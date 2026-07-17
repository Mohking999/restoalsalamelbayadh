<?php
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'إتمام الطلب';
$base = '';
$asset_base = '';

if (empty($_SESSION['cart'])) {
    redirect('cart.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($name === '') $errors[] = 'الرجاء إدخال الاسم الكامل';
    if (!preg_match('/^0[5-7][0-9]{8}$/', $phone)) $errors[] = 'رقم الهاتف غير صالح (مثال: 0555123456)';
    if ($address === '') $errors[] = 'الرجاء إدخال عنوان التوصيل';

    if (empty($errors)) {
        // إعادة التحقق من الأسعار من قاعدة البيانات (لا نثق بأي سعر من الجهة العميلة)
        $ids = array_map('intval', array_keys($_SESSION['cart']));
        $ids_str = implode(',', $ids);
        $items = [];
        $total = 0;

        if ($ids_str) {
            $res = $conn->query("SELECT * FROM menu_items WHERE id IN ($ids_str) AND is_available = 1");
            while ($row = $res->fetch_assoc()) {
                $qty = (int)$_SESSION['cart'][$row['id']];
                if ($qty <= 0) continue;
                $subtotal = $qty * $row['price'];
                $total += $subtotal;
                $items[] = ['name' => $row['name_ar'], 'price' => $row['price'], 'qty' => $qty];
            }
        }

        if (empty($items)) {
            $errors[] = 'سلتك فارغة أو الأطباق لم تعد متوفرة';
        } else {
            $conn->begin_transaction();
            try {
                $order_code = generate_order_code();
                $stmt = $conn->prepare("INSERT INTO orders (order_code, customer_name, phone, address, notes, total, status) VALUES (?,?,?,?,?,?, 'جديد')");
                $stmt->bind_param('sssssd', $order_code, $name, $phone, $address, $notes, $total);
                $stmt->execute();
                $order_id = $conn->insert_id;

                $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, item_name, price, quantity) VALUES (?,?,?,?)");
                foreach ($items as $it) {
                    $stmt2->bind_param('isdi', $order_id, $it['name'], $it['price'], $it['qty']);
                    $stmt2->execute();
                }

                $conn->commit();
                unset($_SESSION['cart']);
                redirect('order_success.php?code=' . urlencode($order_code));
            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = 'حدث خطأ أثناء تسجيل الطلب، حاول مرة أخرى';
            }
        }
    }
}

// حساب مجموع السلة للعرض
$cart_items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $ids_str = implode(',', $ids);
    if ($ids_str) {
        $res = $conn->query("SELECT * FROM menu_items WHERE id IN ($ids_str)");
        while ($row = $res->fetch_assoc()) {
            $qty = $_SESSION['cart'][$row['id']];
            $row['qty'] = $qty;
            $row['subtotal'] = $qty * $row['price'];
            $total += $row['subtotal'];
            $cart_items[] = $row;
        }
    }
}

require 'includes/header.php';
?>

<section class="page-title">
  <div class="container">
    <h1>إتمام الطلب</h1>
    <p>أدخل معلومات التوصيل لتأكيد طلبك.</p>
  </div>
</section>

<div class="container">
  <div class="cart-layout">
    <div>
      <?php if (!empty($errors)): ?>
        <div class="flash error">
          <?php foreach ($errors as $err): ?><div><?= h($err) ?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="form-group">
          <label>الاسم الكامل</label>
          <input type="text" name="name" value="<?= h($_POST['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>رقم الهاتف</label>
          <input type="tel" name="phone" placeholder="0555123456" value="<?= h($_POST['phone'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>عنوان التوصيل</label>
          <textarea name="address" required><?= h($_POST['address'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>ملاحظات إضافية (اختياري)</label>
          <textarea name="notes"><?= h($_POST['notes'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">تأكيد الطلب</button>
      </form>
    </div>

    <div class="summary-box">
      <h3 style="margin-bottom:16px;">ملخص الطلب</h3>
      <?php foreach ($cart_items as $item): ?>
        <div class="summary-row">
          <span><?= h($item['name_ar']) ?> × <?= (int)$item['qty'] ?></span>
          <span><?= format_price($item['subtotal']) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="summary-row total"><span>الإجمالي</span><span class="price"><?= format_price($total) ?></span></div>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>

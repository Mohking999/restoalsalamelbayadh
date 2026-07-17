<?php
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'تم استلام طلبك';
$base = '';
$asset_base = '';

$code = $_GET['code'] ?? '';
$order = null;
$items = [];

if ($code) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_code = ?");
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $stmt2 = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt2->bind_param('i', $order['id']);
        $stmt2->execute();
        $items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

if (!$order) {
    redirect('index.php');
}

require 'includes/header.php';
?>

<div class="container">
  <div class="receipt">
    <div class="receipt-check">✅</div>
    <h2>تم استلام طلبك بنجاح!</h2>
    <div class="code"><?= h($order['order_code']) ?></div>

    <?php foreach ($items as $it): ?>
      <div class="receipt-line">
        <span><?= h($it['item_name']) ?> × <?= (int)$it['quantity'] ?></span>
        <b><?= format_price($it['price'] * $it['quantity']) ?></b>
      </div>
    <?php endforeach; ?>

    <div class="receipt-line" style="border-bottom:none; padding-top:16px;">
      <span>الإجمالي</span>
      <b style="color:var(--gold); font-family:var(--font-mono);"><?= format_price($order['total']) ?></b>
    </div>

    <p style="text-align:center; color:var(--muted); margin-top:22px; font-size:13.5px;">
      سيتصل بك المطعم على الرقم <b style="color:var(--cream)"><?= h($order['phone']) ?></b> لتأكيد الطلب.<br>
      احتفظ برمز الطلب أعلاه للمتابعة.
    </p>
    <a href="menu.php" class="btn btn-outline btn-block" style="margin-top:24px;">طلب جديد</a>
  </div>
</div>

<?php require 'includes/footer.php'; ?>

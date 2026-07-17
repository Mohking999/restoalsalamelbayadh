<?php
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'السلة';
$base = '';
$asset_base = '';

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
    <h1>سلة الطلبات</h1>
    <p>راجع طلبك قبل إتمام عملية الشراء.</p>
  </div>
</section>

<div class="container">
  <?php if (empty($cart_items)): ?>
    <div class="empty-state">
      <div class="emoji">🧺</div>
      <p>سلتك فارغة حاليًا.</p>
      <a href="menu.php" class="btn btn-primary" style="margin-top:18px;">تصفح القائمة</a>
    </div>
  <?php else: ?>
    <div class="cart-layout">
      <div>
        <?php foreach ($cart_items as $item): ?>
          <div class="cart-item" data-id="<?= $item['id'] ?>">
            <div class="info">
              <h4><?= h($item['name_ar']) ?></h4>
              <span><?= format_price($item['price']) ?> × <?= (int)$item['qty'] ?></span>
            </div>
            <div class="qty-control">
              <button class="qty-btn cart-minus" type="button">−</button>
              <span class="qty-val"><?= (int)$item['qty'] ?></span>
              <button class="qty-btn cart-plus" type="button">+</button>
            </div>
            <strong class="price"><?= format_price($item['subtotal']) ?></strong>
            <a href="#" class="remove-link cart-remove">حذف</a>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="summary-box">
        <div class="summary-row"><span>عدد الأطباق</span><span class="summary-count"><?= array_sum(array_column($cart_items, 'qty')) ?></span></div>
        <div class="summary-row total"><span>الإجمالي</span><span class="price summary-total"><?= format_price($total) ?></span></div>
        <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top:20px;">إتمام الطلب →</a>
        <a href="menu.php" class="btn btn-outline btn-block" style="margin-top:12px;">إضافة المزيد</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>

<script>
document.querySelectorAll('.cart-item').forEach(row => {
  const id = row.dataset.id;
  const valEl = row.querySelector('.qty-val');
  const priceEl = row.querySelector('.price');
  const infoSpan = row.querySelector('.info span');

  const plusBtn = row.querySelector('.cart-plus');
  const minusBtn = row.querySelector('.cart-minus');
  const removeBtn = row.querySelector('.cart-remove');

  async function update(newQty, action = 'update') {
    plusBtn.disabled = true;
    minusBtn.disabled = true;
    if (removeBtn) removeBtn.style.pointerEvents = 'none';

    try {
      const res = await fetch('cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `item_id=${id}&qty=${newQty}&action=${action}`
      });
      const data = await res.json();
      
      if (data.ok) {
        // Update header badges
        document.querySelectorAll('.cart-badge').forEach(b => {
          b.textContent = data.cart_count;
          b.classList.remove('bounce');
          void b.offsetWidth;
          b.classList.add('bounce');
        });

        if (data.is_empty) {
          const cartLayout = document.querySelector('.cart-layout');
          cartLayout.style.opacity = '0';
          setTimeout(() => {
            const container = cartLayout.parentNode;
            container.innerHTML = `
              <div class="empty-state" style="opacity:0; transition:opacity 0.4s ease;">
                <div class="emoji">🧺</div>
                <p>سلتك فارغة حاليًا.</p>
                <a href="menu.php" class="btn btn-primary" style="margin-top:18px;">تصفح القائمة</a>
              </div>
            `;
            setTimeout(() => {
              container.querySelector('.empty-state').style.opacity = '1';
            }, 50);
          }, 300);
          return;
        }

        if (newQty <= 0 || action === 'remove') {
          row.classList.add('removing');
          setTimeout(() => row.remove(), 300);
        } else {
          valEl.textContent = newQty;
          priceEl.textContent = data.item_subtotal;
          
          const originalPriceText = infoSpan.textContent.split('×')[0].trim();
          infoSpan.textContent = `${originalPriceText} × ${newQty}`;
        }

        const summaryCount = document.querySelector('.summary-count');
        const summaryTotal = document.querySelector('.summary-total');
        if (summaryCount) summaryCount.textContent = data.item_count;
        if (summaryTotal) summaryTotal.textContent = data.cart_total;

      } else {
        alert(data.error || 'حدث خطأ، حاول مرة أخرى');
      }
    } catch (e) {
      alert('تعذر الاتصال بالخادم');
    } finally {
      plusBtn.disabled = false;
      minusBtn.disabled = false;
      if (removeBtn) removeBtn.style.pointerEvents = 'auto';
    }
  }

  plusBtn.addEventListener('click', () => {
    const currentQty = parseInt(valEl.textContent, 10);
    update(currentQty + 1);
  });
  
  minusBtn.addEventListener('click', () => {
    const currentQty = parseInt(valEl.textContent, 10);
    update(Math.max(currentQty - 1, 0));
  });
  
  removeBtn.addEventListener('click', (e) => {
    e.preventDefault();
    update(0, 'remove');
  });
});
</script>

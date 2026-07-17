<?php
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'القائمة';
$active = 'menu';
$base = '';
$asset_base = '';

$categories = $conn->query("SELECT * FROM categories ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$items_by_cat = [];
$items_res = $conn->query("SELECT * FROM menu_items ORDER BY sort_order");
while ($row = $items_res->fetch_assoc()) {
    $items_by_cat[$row['category_id']][] = $row;
}

require 'includes/header.php';
?>

<?php if (!is_store_open()): ?>
  <div class="store-closed-alert">
    المحل مغلق الآن. الطلبات مغلقة مؤقتًا، يمكنك العودة لاحقًا.
  </div>
<?php endif; ?>

<section class="page-title">
  <div class="container">
    <h1>قائمة الطعام</h1>
    <p>اختر أطباقك المفضلة، حدد الكمية، ثم أكمل طلبك من السلة.</p>
  </div>
</section>

<div class="container">
  <div class="category-bar-wrapper">
    <div class="category-bar">
      <?php foreach ($categories as $i => $cat): ?>
        <a href="#cat-<?= $cat['id'] ?>" class="category-pill <?= $i === 0 ? 'active' : '' ?>" data-cat="<?= $cat['id'] ?>">
          <?= h($cat['icon']) ?> <?= h($cat['name_ar']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="container">
  <?php foreach ($categories as $cat): ?>
    <div class="menu-section" id="cat-<?= $cat['id'] ?>" style="padding-top:6px;">
      <h2><span class="icon"><?= h($cat['icon']) ?></span> <?= h($cat['name_ar']) ?></h2>
      <div class="dish-grid">
        <?php foreach ($items_by_cat[$cat['id']] ?? [] as $item): ?>
          <div class="ticket <?= $item['is_available'] ? '' : 'unavailable' ?>" data-id="<?= $item['id'] ?>">
            <?php if (!empty($item['image_filename'])): ?>
              <div class="ticket-image">
                <img src="uploads/<?= h($item['image_filename']) ?>" alt="<?= h($item['name_ar']) ?>" loading="lazy">
              </div>
            <?php endif; ?>
            <div class="ticket-top">
              <h3><?= h($item['name_ar']) ?></h3>
              <span class="tag <?= h($item['color_tag']) ?>"><?= format_price($item['price']) ?></span>
            </div>
            <p class="desc"><?= h($item['description_ar']) ?></p>
            <div class="ticket-bottom">
              <?php if (!is_store_open()): ?>
                <span class="status-closed">المحل مغلق الآن. الطلبات مغلقة مؤقتًا.</span>
              <?php elseif ($item['is_available']): ?>
                <div class="qty-control">
                  <button class="qty-btn minus" type="button">−</button>
                  <span class="qty-val">0</span>
                  <button class="qty-btn plus" type="button">+</button>
                </div>
                <button class="btn btn-sm btn-primary add-btn" type="button" style="display:none;">أضف للسلة</button>
              <?php else: ?>
                <span style="color:var(--muted); font-size:13px;">غير متوفر حاليًا</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require 'includes/footer.php'; ?>

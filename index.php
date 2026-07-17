<?php
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'الرئيسية';
$active = 'home';
$base = '';
$asset_base = '';

// أطباق مميزة (نأخذ 3 عشوائيًا من المتوفر)
$featured = [];
$res = $conn->query("SELECT * FROM menu_items WHERE is_available = 1 ORDER BY RAND() LIMIT 3");
if ($res) { $featured = $res->fetch_all(MYSQLI_ASSOC); }

$dish_count = $conn->query("SELECT COUNT(*) c FROM menu_items")->fetch_assoc()['c'] ?? 0;
$order_count = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'] ?? 0;

require 'includes/header.php';
?>

<?php if (!is_store_open()): ?>
  <div class="store-closed-alert home-alert">
    المحل مغلق الآن. الطلبات مغلقة مؤقتًا، يمكنك العودة لاحقًا.
  </div>
<?php endif; ?>

<section class="hero">
  <div class="container hero-container">
    <div class="hero-content">
      <div class="hero-eyebrow"><span class="dot"></span> نطبخ الطلب لحظة استلامه — لا تجميد، لا انتظار طويل</div>
      <h1>طعم البيت، على <em>باب</em> دارك</h1>
      <p>مطعم السلام يقدم أطباقًا جزائرية أصيلة، محضّرة يوميًا بمكونات طازجة. اختر من قائمتنا واطلب أونلاين في دقيقتين.</p>
      <div class="hero-actions">
        <a href="menu.php" class="btn btn-primary">🍽️ تصفح القائمة واطلب الآن</a>
        <a href="#featured" class="btn btn-outline">الأطباق المميزة</a>
      </div>
      <div class="hero-stats">
        <div><strong><?= (int)$dish_count ?>+</strong><span>طبق في القائمة</span></div>
        <div><strong>30-45</strong><span>دقيقة وقت التحضير</span></div>
        <div><strong><?= (int)$order_count ?></strong><span>طلب تم تسليمه</span></div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-image-wrapper">
        <div class="hero-glow"></div>
        <img src="مطعم السلام.jpg" alt="طعام تقليدي شهي" class="hero-main-img">
        <div class="floating-badge badge-1">
          <span class="icon">🌶️</span>
          <div>
            <h4>حار وبنين</h4>
            <p>توابل بيتية أصيلة</p>
          </div>
        </div>
        <div class="floating-badge badge-2">
          <span class="icon">⭐</span>
          <div>
            <h4>تقييم عالي</h4>
            <p>100% رضا الزبائن</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="menu-section" id="featured">
  <div class="container">
    <h2><span class="icon">⭐</span> أطباق مقترحة لك اليوم</h2>
    <div class="dish-grid">
      <?php foreach ($featured as $item): ?>
        <div class="ticket">
          <div class="ticket-top">
            <h3><?= h($item['name_ar']) ?></h3>
            <span class="tag <?= h($item['color_tag']) ?>"><?= format_price($item['price']) ?></span>
          </div>
          <p class="desc"><?= h($item['description_ar']) ?></p>
          <div class="ticket-bottom">
            <a href="menu.php" class="btn btn-sm btn-outline">اطلب من القائمة →</a>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($featured)): ?>
        <p style="color:var(--muted)">القائمة قيد التحضير، عاود التحقق قريبًا.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require 'includes/footer.php'; ?>

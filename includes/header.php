<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? h($page_title) . ' - ' : '' ?><?= SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Tajawal:wght@300;400;500;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $asset_base ?? '' ?>assets/css/style.css?v=<?= file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time() ?>">
</head>
<body>

<header class="site-header">
  <div class="container">
    <a href="<?= $base ?? '' ?>index.php" class="brand">
      <svg class="brand-logo" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <!-- Chef Circle Background -->
        <circle cx="100" cy="100" r="95" fill="none" stroke="#E74C3C" stroke-width="3"/>
        
        <!-- Fire Background -->
        <g id="fire">
          <path d="M 70 120 Q 60 100 70 80 Q 75 95 80 85 Q 85 100 80 120 Z" fill="#E74C3C"/>
          <path d="M 90 130 Q 80 100 90 70 Q 100 110 110 80 Q 115 120 110 130 Z" fill="#F39C12"/>
          <path d="M 130 120 Q 140 95 130 75 Q 125 100 120 85 Q 115 100 120 120 Z" fill="#E74C3C"/>
        </g>
        
        <!-- Chef Hat -->
        <path d="M 60 80 L 65 60 L 135 60 L 140 80 Z" fill="white" stroke="black" stroke-width="2"/>
        <rect x="55" y="78" width="90" height="8" fill="white" stroke="black" stroke-width="2"/>
        
        <!-- Chef Head -->
        <circle cx="100" cy="95" r="18" fill="#F4A460" stroke="black" stroke-width="2"/>
        
        <!-- Mustache -->
        <path d="M 85 95 Q 100 100 115 95" stroke="black" stroke-width="3" fill="none" stroke-linecap="round"/>
        
        <!-- Eyes -->
        <circle cx="92" cy="90" r="2" fill="black"/>
        <circle cx="108" cy="90" r="2" fill="black"/>
        
        <!-- Fork Left -->
        <g id="fork-left">
          <line x1="70" y1="110" x2="55" y2="140" stroke="#F4A460" stroke-width="3" stroke-linecap="round"/>
          <line x1="55" y1="140" x2="52" y2="145" stroke="black" stroke-width="2"/>
          <line x1="55" y1="140" x2="58" y2="145" stroke="black" stroke-width="2"/>
          <circle cx="55" cy="140" r="4" fill="#F4A460" stroke="black" stroke-width="1.5"/>
        </g>
        
        <!-- Fork Right -->
        <g id="fork-right">
          <line x1="130" y1="110" x2="145" y2="140" stroke="#F4A460" stroke-width="3" stroke-linecap="round"/>
          <line x1="145" y1="140" x2="142" y2="145" stroke="black" stroke-width="2"/>
          <line x1="145" y1="140" x2="148" y2="145" stroke="black" stroke-width="2"/>
          <circle cx="145" cy="140" r="4" fill="#F4A460" stroke="black" stroke-width="1.5"/>
        </g>
        
        <!-- Text in Arabic -->
        <text x="100" y="165" font-family="Cairo, Arial" font-size="16" font-weight="bold" fill="#E74C3C" text-anchor="middle">مطعم السلام</text>
      </svg>
      <div class="brand-text">
        <strong>مطعم السلام</strong>
        <span>مطبخ بيتي أصيل</span>
      </div>
    </a>
    
    <nav class="main-nav">
      <a href="<?= $base ?? '' ?>index.php" class="<?= ($active ?? '') === 'home' ? 'active' : '' ?>">الرئيسية</a>
      <a href="<?= $base ?? '' ?>menu.php" class="<?= ($active ?? '') === 'menu' ? 'active' : '' ?>">القائمة</a>
      <a href="<?= $base ?? '' ?>photos.php" class="<?= ($active ?? '') === 'photos' ? 'active' : '' ?>">الصور</a>
      <a href="<?= $base ?? '' ?>index.php#contact">تواصل معنا</a>
    </nav>
    
    <div class="header-actions">
      <a href="<?= $base ?? '' ?>cart.php" class="cart-link">
        🧺 <span class="cart-label">السلة</span>
        <span class="cart-badge"><?= function_exists('cart_count') ? cart_count() : 0 ?></span>
      </a>
      <a href="<?= $base ?? '' ?>admin/<?= !empty($_SESSION['admin_id']) ? 'dashboard.php' : 'login.php' ?>" class="btn btn-outline admin-link">
        🔐 لوحة الإدارة
      </a>
      <div class="status-btn <?= is_store_open() ? 'open' : 'closed' ?>" aria-live="polite">
        <?= is_store_open() ? '✅ مفتوح' : '⛔ مغلق' ?>
      </div>
      
      <button class="burger-btn" id="burgerBtn" aria-label="افتح القائمة">
        <span class="burger-bar"></span>
        <span class="burger-bar"></span>
        <span class="burger-bar"></span>
      </button>
    </div>
  </div>
  <div class="store-status-banner <?= is_store_open() ? 'open' : 'closed' ?>">
    <?= is_store_open() ? 'المحل مفتوح الآن. بإمكانك الطلب والاستمتاع بأطباقنا.' : 'المحل مغلق حاليًا. الطلبات مغلقة حتى يتم فتح المحل.' ?>
  </div>
</header>

<!-- Mobile Navigation Drawer -->
<div class="mobile-drawer" id="mobileDrawer">
  <div class="mobile-drawer-header">
    <div class="brand">
      <div class="brand-mark">س</div>
      <div class="brand-text">
        <strong>مطعم السلام</strong>
        <span>مطبخ بيتي أصيل</span>
      </div>
    </div>
    <button class="close-drawer-btn" id="closeDrawerBtn" aria-label="أغلق القائمة">&times;</button>
  </div>
  <nav class="mobile-nav">
    <a href="<?= $base ?? '' ?>index.php" class="<?= ($active ?? '') === 'home' ? 'active' : '' ?>">الرئيسية</a>
    <a href="<?= $base ?? '' ?>menu.php" class="<?= ($active ?? '') === 'menu' ? 'active' : '' ?>">القائمة</a>
    <a href="<?= $base ?? '' ?>photos.php" class="<?= ($active ?? '') === 'photos' ? 'active' : '' ?>">الصور</a>
    <a href="<?= $base ?? '' ?>index.php#contact">تواصل معنا</a>
  </nav>
</div>
<div class="drawer-overlay" id="drawerOverlay"></div>


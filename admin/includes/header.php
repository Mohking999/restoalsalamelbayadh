<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? h($page_title) . ' - ' : '' ?>لوحة إدارة مطعم السلام</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="brand">
      <div class="brand-mark">س</div>
      <div class="brand-text">
        <strong>مطعم السلام</strong>
        <span>لوحة الإدارة</span>
      </div>
    </div>
    <nav class="admin-nav">
      <a href="dashboard.php" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">📋 الطلبات</a>
      <a href="menu.php" class="<?= ($active ?? '') === 'menu' ? 'active' : '' ?>">🍽️ إدارة القائمة</a>
      <a href="categories.php" class="<?= ($active ?? '') === 'categories' ? 'active' : '' ?>">🗂️ إدارة الأصناف</a>
      <a href="store_status.php" class="<?= ($active ?? '') === 'store_status' ? 'active' : '' ?>">🟢 حالة المحل</a>
      <a href="photos.php" class="<?= ($active ?? '') === 'photos' ? 'active' : '' ?>">📸 إدارة الصور</a>
      <a href="../index.php" target="_blank">🌐 معاينة الموقع</a>
      <a href="logout.php">🚪 تسجيل الخروج</a>
    </nav>
  </aside>
  <main class="admin-main">

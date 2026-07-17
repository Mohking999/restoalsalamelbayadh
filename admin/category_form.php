<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$item = ['name_ar' => '', 'icon' => '🍽️', 'sort_order' => 0];
$editing = false;
$errors = [];

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    if ($found) {
        $item = $found;
        $editing = true;
    }
}

$page_title = $editing ? 'تعديل صنف' : 'إضافة صنف جديد';
$active = 'categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name_ar'] ?? '');
    $icon = trim($_POST['icon'] ?? '🍽️');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if ($name === '') {
        $errors[] = 'اسم الصنف مطلوب';
    }

    if (empty($errors)) {
        if ($editing) {
            $stmt = $conn->prepare("UPDATE categories SET name_ar = ?, icon = ?, sort_order = ? WHERE id = ?");
            $stmt->bind_param('ssii', $name, $icon, $sort_order, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name_ar, icon, sort_order) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $name, $icon, $sort_order);
        }
        $stmt->execute();
        redirect('categories.php');
    }
}

require 'includes/header.php';
?>

<h1 style="font-size:26px; margin-bottom:26px;"><?= $editing ? 'تعديل الصنف' : 'إضافة صنف جديد' ?></h1>

<?php if (!empty($errors)): ?>
  <div class="flash error"><?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<form method="post" style="max-width:520px;">
  <div class="form-group">
    <label>اسم الصنف</label>
    <input type="text" name="name_ar" value="<?= h($item['name_ar']) ?>" required>
  </div>
  <div class="form-group">
    <label>الأيقونة</label>
    <input type="text" name="icon" value="<?= h($item['icon']) ?>" required>
    <small style="display:block; margin-top:6px; color:#888;">يمكن استخدام رمز تعبيري مثل 🍽️ أو 🔥 أو 🥗</small>
  </div>
  <div class="form-group">
    <label>ترتيب العرض</label>
    <input type="number" name="sort_order" value="<?= (int)$item['sort_order'] ?>">
    <small style="display:block; margin-top:6px; color:#888;">القيمة الأصغر تظهر أولًا.</small>
  </div>
  <div style="display:flex; gap:12px;">
    <button type="submit" class="btn btn-primary">حفظ</button>
    <a href="categories.php" class="btn btn-outline">إلغاء</a>
  </div>
</form>

<?php require 'includes/footer.php';

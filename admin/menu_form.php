<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$item = ['name_ar'=>'','description_ar'=>'','price'=>'','category_id'=>'','color_tag'=>'gold','is_available'=>1,'sort_order'=>0];
$editing = false;

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    if ($found) { $item = $found; $editing = true; }
}

$page_title = $editing ? 'تعديل طبق' : 'إضافة طبق';
$active = 'menu';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name_ar'] ?? '');
    $desc = trim($_POST['description_ar'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $color_tag = in_array($_POST['color_tag'] ?? '', ['gold','paprika','olive']) ? $_POST['color_tag'] : 'gold';
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $image_filename = $item['image_filename'] ?? null; // احتفظ بالصورة الموجودة

    if ($name === '') $errors[] = 'اسم الطبق مطلوب';
    if ($price <= 0) $errors[] = 'السعر يجب أن يكون أكبر من صفر';
    if (!$category_id) $errors[] = 'اختر صنفًا';

    // معالجة رفع الصورة
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['item_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $errors[] = 'صيغة الصورة غير مدعومة. استخدم JPG أو PNG أو GIF أو WebP.';
        } else if ($file['size'] > 5000000) { // 5MB max
            $errors[] = 'حجم الصورة كبير جدًا. الحد الأقصى 5MB.';
        } else {
            $filename = 'dish_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            
            if (move_uploaded_file($file['tmp_name'], '../uploads/' . $filename)) {
                // حذف الصورة القديمة إن وجدت
                if (!empty($item['image_filename']) && file_exists('../uploads/' . $item['image_filename'])) {
                    unlink('../uploads/' . $item['image_filename']);
                }
                $image_filename = $filename;
            } else {
                $errors[] = 'خطأ في رفع الصورة';
            }
        }
    }

    if (empty($errors)) {
        if ($editing) {
            $stmt = $conn->prepare("UPDATE menu_items SET name_ar=?, description_ar=?, price=?, category_id=?, color_tag=?, is_available=?, image_filename=? WHERE id=?");
            $stmt->bind_param('ssdisisi', $name, $desc, $price, $category_id, $color_tag, $is_available, $image_filename, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO menu_items (name_ar, description_ar, price, category_id, color_tag, is_available, image_filename) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param('ssdisis', $name, $desc, $price, $category_id, $color_tag, $is_available, $image_filename);
        }
        $stmt->execute();
        redirect('menu.php');
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);

require 'includes/header.php';
?>

<h1 style="font-size:26px; margin-bottom:26px;"><?= $editing ? 'تعديل الطبق' : 'إضافة طبق جديد' ?></h1>

<?php if (!empty($errors)): ?>
  <div class="flash error"><?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" style="max-width:520px;">
  <div class="form-group">
    <label>اسم الطبق</label>
    <input type="text" name="name_ar" value="<?= h($item['name_ar']) ?>" required>
  </div>
  <div class="form-group">
    <label>الوصف</label>
    <textarea name="description_ar"><?= h($item['description_ar']) ?></textarea>
  </div>
  <div class="form-group">
    <label>صورة الطبق</label>
    <?php if (!empty($item['image_filename'])): ?>
      <div style="margin-bottom:10px;">
        <img src="../uploads/<?= h($item['image_filename']) ?>" style="max-width:150px; border-radius:6px;">
        <p style="font-size:12px; color:#888; margin:5px 0 0 0;">الصورة الحالية</p>
      </div>
    <?php endif; ?>
    <input type="file" name="item_image" accept="image/*" style="width:100%;">
    <small style="display:block; margin-top:5px; color:#888;">الحد الأقصى: 5MB | الصيغ المدعومة: JPG, PNG, GIF, WebP</small>
  </div>
  <div class="form-group">
    <label>السعر (دج)</label>
    <input type="number" step="0.01" name="price" value="<?= h($item['price']) ?>" required>
  </div>
  <div class="form-group">
    <label>الصنف</label>
    <select name="category_id" required style="width:100%; background:var(--bg); color:var(--cream); border:1px solid var(--line-strong); padding:13px 14px; border-radius:8px;">
      <option value="">اختر صنفًا</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $item['category_id'] ? 'selected' : '' ?>><?= h($cat['icon']) ?> <?= h($cat['name_ar']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-group">
    <label>لون البطاقة</label>
    <select name="color_tag" style="width:100%; background:var(--bg); color:var(--cream); border:1px solid var(--line-strong); padding:13px 14px; border-radius:8px;">
      <option value="gold" <?= $item['color_tag']==='gold'?'selected':'' ?>>ذهبي</option>
      <option value="paprika" <?= $item['color_tag']==='paprika'?'selected':'' ?>>أحمر (فلفل)</option>
      <option value="olive" <?= $item['color_tag']==='olive'?'selected':'' ?>>زيتوني</option>
    </select>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="is_available" <?= $item['is_available'] ? 'checked' : '' ?> style="width:auto; margin-left:8px;"> متوفر حاليًا</label>
  </div>
  <div style="display:flex; gap:12px;">
    <button type="submit" class="btn btn-primary">حفظ</button>
    <a href="menu.php" class="btn btn-outline">إلغاء</a>
  </div>
</form>

<?php require 'includes/footer.php'; ?>

<?php
require_once '../config.php';
require_once '../includes/functions.php';

require_admin();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $photo_id = (int)$_POST['photo_id'];
        
        // Get the filename to delete
        $stmt = $conn->prepare("SELECT filename FROM photos WHERE id = ?");
        $stmt->bind_param('i', $photo_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result && unlink('../uploads/' . $result['filename'])) {
            $conn->query("DELETE FROM photos WHERE id = $photo_id");
            $message = 'تم حذف الصورة بنجاح';
        }
    } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $title = $_POST['title_ar'] ?? '';
        $description = $_POST['description_ar'] ?? '';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        
        if (empty($title)) {
            $error = 'الرجاء إدخال عنوان الصورة';
        } else {
            $file = $_FILES['photo'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $error = 'صيغة الصورة غير مدعومة. استخدم JPG أو PNG أو GIF أو WebP.';
            } else if ($file['size'] > 5000000) { // 5MB max
                $error = 'حجم الصورة كبير جدًا. الحد الأقصى 5MB.';
            } else {
                $filename = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
                
                if (move_uploaded_file($file['tmp_name'], '../uploads/' . $filename)) {
                    $stmt = $conn->prepare("INSERT INTO photos (title_ar, description_ar, filename, sort_order) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('sssi', $title, $description, $filename, $sort_order);
                    
                    if ($stmt->execute()) {
                        $message = 'تم رفع الصورة بنجاح!';
                    } else {
                        unlink('../uploads/' . $filename);
                        $error = 'خطأ في حفظ الصورة في قاعدة البيانات';
                    }
                } else {
                    $error = 'خطأ في رفع الملف';
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'toggle') {
        $photo_id = (int)$_POST['photo_id'];
        $conn->query("UPDATE photos SET is_visible = NOT is_visible WHERE id = $photo_id");
        $message = 'تم تحديث حالة الصورة';
    }
}

// Get all photos
$photos = $conn->query("SELECT * FROM photos ORDER BY sort_order, created_at DESC")->fetch_all(MYSQLI_ASSOC);

$page_title = 'إدارة الصور';
$active = 'photos';
$base = '';
$asset_base = '../';

require '../includes/header.php';
?>

<section class="page-title">
  <div class="container">
    <h1>📸 إدارة الصور</h1>
  </div>
</section>

<div class="container">
  <?php if ($message): ?>
    <div class="alert alert-success"><?= h($message) ?></div>
  <?php endif; ?>
  
  <?php if ($error): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
  <?php endif; ?>

  <!-- Upload Form -->
  <div class="admin-card">
    <h2>رفع صورة جديدة</h2>
    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label>العنوان بالعربية *</label>
        <input type="text" name="title_ar" required>
      </div>
      
      <div class="form-group">
        <label>الوصف</label>
        <textarea name="description_ar" rows="3"></textarea>
      </div>
      
      <div class="form-group">
        <label>ترتيب العرض (أرقام أصغر تظهر أولاً)</label>
        <input type="number" name="sort_order" value="0">
      </div>
      
      <div class="form-group">
        <label>اختر الصورة *</label>
        <input type="file" name="photo" accept="image/*" required>
        <small>الحد الأقصى: 5MB | الصيغ المدعومة: JPG, PNG, GIF, WebP</small>
      </div>
      
      <button type="submit" class="btn btn-primary">رفع الصورة</button>
    </form>
  </div>

  <!-- Photos List -->
  <div class="admin-card" style="margin-top: 30px;">
    <h2>الصور الموجودة</h2>
    
    <?php if (empty($photos)): ?>
      <p style="text-align: center; color: var(--muted); padding: 20px;">لا توجد صور حتى الآن</p>
    <?php else: ?>
      <div class="photos-list">
        <?php foreach ($photos as $photo): ?>
          <div class="photo-item">
            <img src="<?= $base ?>uploads/<?= h($photo['filename']) ?>" alt="<?= h($photo['title_ar']) ?>">
            <div class="photo-info">
              <h4><?= h($photo['title_ar']) ?></h4>
              <p><?= h($photo['description_ar'] ?? '-') ?></p>
              <small>الترتيب: <?= $photo['sort_order'] ?> | الحالة: <?= $photo['is_visible'] ? '✓ مرئية' : '✗ مخفية' ?></small>
            </div>
            <div class="photo-actions">
              <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="photo_id" value="<?= $photo['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline">
                  <?= $photo['is_visible'] ? '🙈 إخفاء' : '👁 إظهار' ?>
                </button>
              </form>
              <form method="POST" style="display: inline;" onsubmit="return confirm('هل تريد حذف هذه الصورة؟');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="photo_id" value="<?= $photo['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">🗑️ حذف</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.alert {
  padding: 15px 20px;
  border-radius: 6px;
  margin-bottom: 20px;
}

.alert-success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.alert-error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.admin-card {
  background: white;
  border-radius: 8px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.admin-card h2 {
  margin-top: 0;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 2px solid #f0f0f0;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #333;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="file"],
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-family: inherit;
  font-size: 14px;
}

.form-group input[type="file"]::file-selector-button {
  padding: 8px 15px;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.form-group small {
  display: block;
  margin-top: 5px;
  color: #888;
  font-size: 12px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.btn-primary {
  background: var(--primary);
  color: white;
}

.btn-primary:hover {
  opacity: 0.9;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

.btn-outline {
  border: 1px solid #ddd;
  background: white;
  color: #333;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.photos-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.photo-item {
  display: grid;
  grid-template-columns: 120px 1fr auto;
  gap: 20px;
  align-items: center;
  padding: 15px;
  background: #f9f9f9;
  border-radius: 6px;
  border: 1px solid #eee;
}

.photo-item img {
  width: 100%;
  height: 100px;
  object-fit: cover;
  border-radius: 4px;
}

.photo-info h4 {
  margin: 0 0 5px 0;
  font-size: 16px;
}

.photo-info p {
  margin: 5px 0;
  font-size: 14px;
  color: #666;
}

.photo-info small {
  display: block;
  margin-top: 8px;
  color: #999;
  font-size: 12px;
}

.photo-actions {
  display: flex;
  gap: 10px;
}

@media (max-width: 768px) {
  .photo-item {
    grid-template-columns: 80px 1fr;
  }
  
  .photo-actions {
    grid-column: 1/-1;
    justify-content: flex-end;
  }
}
</style>

<?php require '../includes/footer.php'; ?>

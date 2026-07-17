<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_admin();

$page_title = 'إدارة الأصناف';
$active = 'categories';

$delete_id = (int)($_GET['delete'] ?? 0);
if ($delete_id) {
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    redirect('categories.php');
}

$categories = $conn->query("SELECT * FROM categories ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);

require 'includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:26px;">
  <h1 style="font-size:26px;">إدارة الأصناف</h1>
  <a href="category_form.php" class="btn btn-primary">+ إضافة صنف جديد</a>
</div>

<table class="admin-table">
  <thead>
    <tr><th>الترتيب</th><th>الصنف</th><th>الأيقونة</th><th>إجراءات</th></tr>
  </thead>
  <tbody>
    <?php foreach ($categories as $cat): ?>
      <tr>
        <td><?= (int)$cat['sort_order'] ?></td>
        <td><?= h($cat['name_ar']) ?></td>
        <td style="font-size:22px;"><?= h($cat['icon']) ?></td>
        <td style="display:flex; gap:10px;">
          <a href="category_form.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline">تعديل</a>
          <a href="categories.php?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-outline" style="color:var(--paprika); border-color:var(--paprika-dim);" onclick="return confirm('حذف هذا الصنف سيحذف الأطباق المرتبطة به أيضًا. تابع؟')">حذف</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($categories)): ?>
      <tr><td colspan="4" style="text-align:center; color:var(--muted); padding:40px;">لا توجد أصناف بعد</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php require 'includes/footer.php';

<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_admin();

$page_title = 'إدارة القائمة';
$active = 'menu';

// تبديل حالة التوفر مباشرة من القائمة
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE menu_items SET is_available = 1 - is_available WHERE id = $id");
    redirect('menu.php');
}

$categories = $conn->query("SELECT * FROM categories ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$items = $conn->query("
    SELECT mi.*, c.name_ar AS cat_name FROM menu_items mi
    JOIN categories c ON c.id = mi.category_id
    ORDER BY c.sort_order, mi.sort_order
")->fetch_all(MYSQLI_ASSOC);

require 'includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:26px;">
  <h1 style="font-size:26px;">إدارة القائمة</h1>
  <a href="menu_form.php" class="btn btn-primary">+ إضافة طبق جديد</a>
</div>

<table class="admin-table">
  <thead>
    <tr><th>الطبق</th><th>الصنف</th><th>السعر</th><th>الحالة</th><th>إجراءات</th></tr>
  </thead>
  <tbody>
    <?php foreach ($items as $item): ?>
      <tr>
        <td><?= h($item['name_ar']) ?></td>
        <td><?= h($item['cat_name']) ?></td>
        <td><?= format_price($item['price']) ?></td>
        <td>
          <a href="?toggle=<?= $item['id'] ?>" class="status-badge <?= $item['is_available'] ? 'done' : 'cancel' ?>" style="text-decoration:none;">
            <?= $item['is_available'] ? 'متوفر' : 'غير متوفر' ?>
          </a>
        </td>
        <td style="display:flex; gap:10px;">
          <a href="menu_form.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline">تعديل</a>
          <a href="delete_item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline" style="color:var(--paprika); border-color:var(--paprika-dim);"
             onclick="return confirm('حذف هذا الطبق نهائيًا؟')">حذف</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
      <tr><td colspan="5" style="text-align:center; color:var(--muted); padding:40px;">لا توجد أطباق بعد</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php require 'includes/footer.php'; ?>

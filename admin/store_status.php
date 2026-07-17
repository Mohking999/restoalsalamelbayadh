<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_admin();

$page_title = 'حالة المحل';
$active = 'dashboard';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_open = isset($_POST['is_open']) ? 1 : 0;
    $stmt = $conn->prepare("INSERT INTO store_status (id, is_open) VALUES (1, ?) ON DUPLICATE KEY UPDATE is_open = VALUES(is_open)");
    $stmt->bind_param('i', $is_open);
    $stmt->execute();
    redirect('store_status.php');
}

$result = $conn->query("SELECT is_open FROM store_status WHERE id = 1");
$status = 1;
if ($result && $row = $result->fetch_assoc()) {
    $status = (int)$row['is_open'];
}

require 'includes/header.php';
?>

<h1 style="font-size:26px; margin-bottom:26px;">حالة المحل</h1>

<form method="post" style="max-width:520px;">
  <div class="form-group" style="display:flex; align-items:center; gap:16px;">
    <label style="font-weight:700; font-size:15px;">المحل حالياً:</label>
    <span style="padding:10px 16px; border-radius:999px; background: <?= $status ? 'rgba(124, 138, 85, 0.18)' : 'rgba(193, 68, 43, 0.14)' ?>; color: <?= $status ? 'var(--gold)' : 'var(--paprika)' ?>; font-weight:700;">
      <?= $status ? '✅ مفتوح' : '⛔ مغلق' ?>
    </span>
  </div>
  <div class="form-group" style="margin-top:24px;">
    <label>
      <input type="checkbox" name="is_open" value="1" <?= $status ? 'checked' : '' ?>>
      افتح المحل الآن
    </label>
  </div>
  <div style="display:flex; gap:12px; margin-top:22px;">
    <button type="submit" class="btn btn-primary">حفظ الحالة</button>
    <a href="dashboard.php" class="btn btn-outline">العودة</a>
  </div>
</form>

<?php require 'includes/footer.php';

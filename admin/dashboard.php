<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_admin();

$page_title = 'الطلبات';
$active = 'dashboard';

// تحديث حالة الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $valid_statuses = ['جديد','قيد التحضير','في الطريق','تم التسليم','ملغى'];
    if (in_array($_POST['status'], $valid_statuses, true)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $_POST['status'], $_POST['order_id']);
        $stmt->execute();
    }
    redirect('dashboard.php');
}

$stats = [
    'today'   => $conn->query("SELECT COUNT(*) c FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'],
    'pending' => $conn->query("SELECT COUNT(*) c FROM orders WHERE status IN ('جديد','قيد التحضير','في الطريق')")->fetch_assoc()['c'],
    'revenue' => $conn->query("SELECT COALESCE(SUM(total),0) s FROM orders WHERE status != 'ملغى' AND DATE(created_at) = CURDATE()")->fetch_assoc()['s'],
    'total'   => $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'],
];

$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);

function status_class($status) {
    switch ($status) {
        case 'جديد': return 'new';
        case 'قيد التحضير': return 'prep';
        case 'في الطريق': return 'way';
        case 'تم التسليم': return 'done';
        case 'ملغى': return 'cancel';
        default: return 'new';
    }
}

require 'includes/header.php';
?>

<h1 style="font-size:26px; margin-bottom:26px;">مرحبًا، <?= h($_SESSION['admin_username']) ?> 👋</h1>

<div style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px; flex-wrap:wrap;">
  <a href="store_status.php" class="btn btn-primary" style="padding: 12px 20px;">⚙️ تغيير حالة المحل</a>
  <div style="color: var(--muted);">يمكنك التحكم في إظهار حالة <strong>مفتوح / مغلق</strong> للموقع من هنا.</div>
</div>

<div class="stat-grid">
  <div class="stat-card"><span>طلبات اليوم</span><strong><?= (int)$stats['today'] ?></strong></div>
  <div class="stat-card"><span>طلبات قيد المعالجة</span><strong><?= (int)$stats['pending'] ?></strong></div>
  <div class="stat-card"><span>مبيعات اليوم</span><strong><?= format_price($stats['revenue']) ?></strong></div>
  <div class="stat-card"><span>إجمالي الطلبات</span><strong><?= (int)$stats['total'] ?></strong></div>
</div>

<table class="admin-table">
  <thead>
    <tr>
      <th>الرمز</th><th>الزبون</th><th>الهاتف</th><th>العنوان</th><th>الإجمالي</th><th>الحالة</th><th>التاريخ</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td style="font-family: var(--font-mono); color:var(--gold);"><?= h($o['order_code']) ?></td>
        <td><?= h($o['customer_name']) ?></td>
        <td><?= h($o['phone']) ?></td>
        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= h($o['address']) ?></td>
        <td><?= format_price($o['total']) ?></td>
        <td>
          <form method="post" style="display:flex; gap:8px; align-items:center;">
            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
            <span class="status-badge <?= status_class($o['status']) ?>"><?= h($o['status']) ?></span>
            <select name="status" onchange="this.form.submit()" style="background:var(--bg); color:var(--cream); border:1px solid var(--line-strong); border-radius:6px; padding:5px 8px; font-size:12px;">
              <?php foreach (['جديد','قيد التحضير','في الطريق','تم التسليم','ملغى'] as $s): ?>
                <option value="<?= h($s) ?>" <?= $s === $o['status'] ? 'selected' : '' ?>><?= h($s) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </td>
        <td style="font-size:12.5px; color:var(--muted);"><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?>
      <tr><td colspan="7" style="text-align:center; color:var(--muted); padding:40px;">لا توجد طلبات بعد</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php require 'includes/footer.php'; ?>

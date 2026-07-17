<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (is_admin_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        redirect('dashboard.php');
    } else {
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تسجيل الدخول - لوحة الإدارة</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-shell">
  <div class="login-box">
    <div class="brand" style="justify-content:center; margin-bottom:26px;">
      <div class="brand-mark">س</div>
      <div class="brand-text">
        <strong>مطعم السلام</strong>
        <span>لوحة الإدارة</span>
      </div>
    </div>
    <?php if ($error): ?><div class="flash error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label>اسم المستخدم</label>
        <input type="text" name="username" required autofocus>
      </div>
      <div class="form-group">
        <label>كلمة المرور</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">دخول</button>
    </form>
    <p style="color:var(--muted); font-size:12px; margin-top:18px; text-align:center;">
      بيانات تجريبية: admin / alsalam2026 — غيّرها فورًا بعد أول دخول
    </p>
  </div>
</div>
</body>
</html>

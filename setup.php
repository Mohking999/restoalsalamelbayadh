<?php


session_start();



$error = '';
$success = '';

// فقط للاستضافة
$is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']) ||
            strpos($_SERVER['SERVER_NAME'] ?? '', '.local') !== false;

if (!$is_local) {
 
} else {
    // محلي
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'alsalam_restaurant');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    $error = "❌ فشل الاتصال بقاعدة البيانات: " . htmlspecialchars($conn->connect_error);
}

// التحقق من كلمة المرور
$authenticated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';
    if ($pass === SETUP_PASSWORD) {
        $authenticated = true;
        $_SESSION['setup_auth'] = true;
    } else {
        $error = '❌ كلمة المرور خاطئة!';
    }
}

if (isset($_SESSION['setup_auth'])) {
    $authenticated = true;
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد قاعدة البيانات</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Cairo', Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #E74C3C; margin-bottom: 20px; }
        .status { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        form { margin-bottom: 20px; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; }
        button { background: #E74C3C; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #c0392b; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: right; }
        th { background: #f8f9fa; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        .warning { color: #ff9800; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>⚙️ إعداد قاعدة البيانات</h1>
    
    <?php if ($error): ?>
        <div class="status error"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="status success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if (!$authenticated): ?>
        <div class="status info">أدخل كلمة المرور للمتابعة (كلمة المرور: <code>setup123</code>)</div>
        <form method="post">
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <button type="submit">دخول</button>
        </form>
    <?php else: ?>
        
        <div class="status info">
            <strong>بيانات الاتصال:</strong><br>
            HOST: <code><?= htmlspecialchars(DB_HOST) ?></code><br>
            DB: <code><?= htmlspecialchars(DB_NAME) ?></code><br>
            USER: <code><?= htmlspecialchars(DB_USER) ?></code>
        </div>
        
        <?php if (!$conn->connect_error): ?>
            <div class="status success">✅ الاتصال بقاعدة البيانات ناجح!</div>
            
            <?php
            // فحص الجداول الموجودة
            $tables = [];
            $result = $conn->query("SHOW TABLES");
            if ($result) {
                while ($row = $result->fetch_array()) {
                    $tables[] = $row[0];
                }
            }
            
            if (empty($tables)) {
                echo '<div class="status warning">⚠️ لا توجد جداول! يجب استيراد البيانات الآن.</div>';
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
                    // استيراد البيانات
                    $sql_file = __DIR__ . '/database.sql';
                    
                    if (!file_exists($sql_file)) {
                        echo '<div class="status error">❌ ملف database.sql غير موجود!</div>';
                    } else {
                        $sql = file_get_contents($sql_file);
                        
                        // تقسيم الاستعلامات
                        $queries = array_filter(
                            array_map('trim', explode(';', $sql)),
                            fn($q) => !empty($q) && !str_starts_with($q, '--') && !str_starts_with($q, '/*')
                        );
                        
                        $imported = 0;
                        $failed = 0;
                        $errors_list = [];
                        
                        foreach ($queries as $query) {
                            if ($conn->query($query)) {
                                $imported++;
                            } else {
                                $failed++;
                                $errors_list[] = htmlspecialchars($conn->error);
                            }
                        }
                        
                        if ($failed === 0) {
                            echo '<div class="status success">✅ تم استيراد البيانات بنجاح! (' . $imported . ' استعلام)</div>';
                            echo '<p style="color:green;">يمكنك الآن <a href="index.php">الذهاب للموقع الرئيسي</a></p>';
                        } else {
                            echo '<div class="status error">❌ حدثت بعض الأخطاء: ' . $failed . ' أخطاء من ' . ($imported + $failed) . '</div>';
                            if ($errors_list) {
                                echo '<ul>';
                                foreach ($errors_list as $err) {
                                    echo '<li>' . $err . '</li>';
                                }
                                echo '</ul>';
                            }
                        }
                    }
                } else {
                    echo '<form method="post">';
                    echo '<button type="submit" name="import" value="1">🚀 استيراد البيانات الآن</button>';
                    echo '</form>';
                }
            } else {
                echo '<div class="status success">✅ الجداول موجودة بالفعل:</div>';
                echo '<table>';
                echo '<tr><th>جدول</th><th>عدد السجلات</th></tr>';
                foreach ($tables as $table) {
                    $count = $conn->query("SELECT COUNT(*) c FROM `$table`")->fetch_assoc()['c'];
                    echo '<tr><td>' . htmlspecialchars($table) . '</td><td>' . (int)$count . '</td></tr>';
                }
                echo '</table>';
                echo '<p style="margin-top:20px; color:green;">✅ قاعدة البيانات جاهزة! يمكنك <a href="index.php">الذهاب للموقع</a></p>';
            }
            ?>
        <?php else: ?>
            <div class="status error">❌ فشل الاتصال بقاعدة البيانات</div>
        <?php endif; ?>
        
        <hr style="margin:30px 0; border:none; border-top:1px solid #ddd;">
        <p class="warning">⚠️ تنبيه: احذف هذا الملف (setup.php) بعد الانتهاء من الاستيراد!</p>
        <p>غيّر كلمة المرور في السطر 9 من الملف قبل رفعه على الاستضافة!</p>
        
    <?php endif; ?>
</div>
</body>
</html>

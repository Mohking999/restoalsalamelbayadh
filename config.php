<?php
/**
 * config.php
 * إعدادات الاتصال بقاعدة البيانات + إعدادات عامة للموقع
 * عدّل بيانات الاستضافة أدناه عند الرفع على ezyro.com أو أي استضافة أخرى
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();

// ---------------------------------------------------------
// كشف البيئة تلقائيًا: محلي (localhost) أو استضافة حقيقية
// ---------------------------------------------------------
$is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']) ||
            strpos($_SERVER['SERVER_NAME'] ?? '', '.local') !== false;

if ($is_local) {
    // إعدادات التطوير المحلي (XAMPP / MAMP / WAMP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'alsalam_restaurant');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', 3306);
} else {
    // إعدادات الاستضافة (cPanel / icosnet)
    define('DB_HOST', '');
    define('DB_NAME', '');
    define('DB_USER', '');
    define('DB_PASS', '');
    define('DB_PORT', );
}

define('SITE_NAME', 'مطعم السلام');
define('CURRENCY', 'دج');

define('STORE_OPEN', true); // قيمة افتراضية، يتم استبدالها بقيمة قاعدة البيانات عندما تكون متاحة

// ---------------------------------------------------------
// الاتصال بقاعدة البيانات
// ---------------------------------------------------------
mysqli_report(MYSQLI_REPORT_OFF); // نتحكم نحن في الأخطاء يدويًا لتفادي كشف بيانات حساسة

// في البيئة المحلية: نعرض الأخطاء.
// على الاستضافة: اعرض الأخطاء فقط عند طلب ?debug=1
$show_errors = $is_local || (isset($_GET['debug']) && $_GET['debug'] === '1');

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    http_response_code(500);
    
    if ($show_errors) {
        // تفاصيل كاملة في البيئة المحلية
        echo "<h1>❌ خطأ في الاتصال بقاعدة البيانات</h1>";
        echo "<p><strong>المضيف:</strong> " . htmlspecialchars(DB_HOST) . "</p>";
        echo "<p><strong>قاعدة البيانات:</strong> " . htmlspecialchars(DB_NAME) . "</p>";
        echo "<p><strong>المستخدم:</strong> " . htmlspecialchars(DB_USER) . "</p>";
        echo "<p><strong>الخطأ:</strong> " . htmlspecialchars($conn->connect_error) . "</p>";
        echo "<p><strong>الحل:</strong></p>";
        echo "<ul>";
        echo "<li>تأكد من أن MySQL يعمل</li>";
        echo "<li>استورد ملف <code>database.sql</code> عبر phpMyAdmin</li>";
        echo "<li>افتح <code>test-db.php</code> للتفاصيل</li>";
        echo "</ul>";
    } else {
        // رسالة آمنة على الاستضافة
        die('Service temporarily unavailable. Please contact support.');
    }
    exit;
}
$conn->set_charset('utf8mb4');

$conn->query("CREATE TABLE IF NOT EXISTS store_status (
    id INT PRIMARY KEY,
    is_open TINYINT(1) NOT NULL DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$conn->query("INSERT INTO store_status (id, is_open) VALUES (1, 1) ON DUPLICATE KEY UPDATE is_open = is_open");

function is_store_open() {
    global $conn;
    $result = $conn->query("SELECT is_open FROM store_status WHERE id = 1");
    if ($result && $row = $result->fetch_assoc()) {
        return (bool)$row['is_open'];
    }
    return defined('STORE_OPEN') ? STORE_OPEN : true;
}

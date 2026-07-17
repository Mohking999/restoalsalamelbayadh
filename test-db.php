<?php
/**
 * ملف اختبار الاتصال بقاعدة البيانات
 * اذهب إلى: http://localhost/alsalam/test-db.php
 */

session_start();

// كشف البيئة
$is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']) ||
            strpos($_SERVER['SERVER_NAME'] ?? '', '.local') !== false;

echo "<h1>⚙️ فحص الاتصال بقاعدة البيانات</h1>";
echo "<p><strong>البيئة:</strong> " . ($is_local ? "محلي (XAMPP)" : "استضافة حقيقية") . "</p>";
echo "<hr>";

if ($is_local) {
    echo "<p><strong>بيانات الاتصال المستخدمة:</strong></p>";
    echo "<pre>HOST: localhost\nDB: alsalam_restaurant\nUSER: root\nPASS: (فارغة)</pre>";
} else {
    echo "<p><strong>بيانات الاتصال المستخدمة:</strong></p>";
    echo "<pre>HOST: localhost\nDB: \nUSER: restoal1_user\nPASS: ,</pre>";
}

echo "<hr>";

// اختبار الاتصال
if ($is_local) {
    $conn = @new mysqli('localhost', 'root', '', 'alsalam_restaurant');
} else {
    $conn = @new mysqli();
}

if ($conn->connect_error) {
    echo "<h3 style='color:red;'>❌ فشل الاتصال بقاعدة البيانات</h3>";
    echo "<p><strong>الخطأ:</strong> " . htmlspecialchars($conn->connect_error) . "</p>";
    echo "<p>🔧 الحلول الممكنة:</p>";
    echo "<ul>";
    echo "<li>تأكد من أن MySQL/MariaDB تعمل</li>";
    echo "<li>تأكد من أن قاعدة البيانات <code>alsalam_restaurant</code> موجودة</li>";
    echo "<li>استورد ملف <code>database.sql</code> باستخدام phpMyAdmin</li>";
    echo "</ul>";
    exit;
}

echo "<h3 style='color:green;'>✅ الاتصال بقاعدة البيانات ناجح!</h3>";

// فحص الجداول
$conn->set_charset('utf8mb4');
$tables = ['categories', 'menu_items', 'orders', 'order_items', 'admin_users'];
echo "<h3>فحص الجداول:</h3>";

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color:green;'>✅ جدول <code>$table</code> موجود</p>";
    } else {
        echo "<p style='color:red;'>❌ جدول <code>$table</code> غير موجود</p>";
    }
}

// فحص عدد السجلات
echo "<h3>عدد السجلات:</h3>";
$counts = [
    'categories' => 'التصنيفات',
    'menu_items' => 'الأطباق',
    'orders' => 'الطلبات',
    'admin_users' => 'المسؤولون'
];

foreach ($counts as $table => $label) {
    $result = $conn->query("SELECT COUNT(*) c FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>📊 $label: <strong>" . $row['c'] . "</strong></p>";
    }
}

// اختبار استعلام بسيط
echo "<h3>اختبار استعلام (أول 3 أطباق):</h3>";
$result = $conn->query("SELECT * FROM menu_items LIMIT 3");
if ($result) {
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | " . $row['name_ar'] . " | السعر: " . $row['price'] . "\n";
    }
    echo "</pre>";
} else {
    echo "<p style='color:red;'>❌ خطأ في الاستعلام: " . htmlspecialchars($conn->error) . "</p>";
}

$conn->close();
echo "<hr>";
echo "<p><a href='index.php'>← العودة للموقع الرئيسي</a></p>";
?>

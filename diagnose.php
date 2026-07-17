<?php
/**
 * ملف تشخيص الخطأ 500
 * اذهب إلى: http://localhost/alsalam/diagnose.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 تشخيص مشكلة الخطأ 500</h1>";
echo "<hr>";

// 1. فحص تحميل config.php
echo "<h3>1️⃣ اختبار تحميل config.php</h3>";
try {
    require_once 'config.php';
    echo "<p style='color:green;'>✅ تم تحميل config.php بنجاح</p>";
    echo "<p>📊 قاعدة البيانات: " . htmlspecialchars(DB_NAME) . "</p>";
} catch (Throwable $e) {
    echo "<p style='color:red;'>❌ خطأ في config.php</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}

// 2. فحص functions.php
echo "<h3>2️⃣ اختبار تحميل functions.php</h3>";
try {
    require_once 'includes/functions.php';
    echo "<p style='color:green;'>✅ تم تحميل functions.php بنجاح</p>";
} catch (Throwable $e) {
    echo "<p style='color:red;'>❌ خطأ في functions.php</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}

// 3. فحص استعلامات قاعدة البيانات
echo "<h3>3️⃣ اختبار استعلامات قاعدة البيانات</h3>";

$queries = [
    "SELECT COUNT(*) c FROM categories" => "التصنيفات",
    "SELECT COUNT(*) c FROM menu_items" => "الأطباق",
    "SELECT COUNT(*) c FROM orders" => "الطلبات",
    "SELECT * FROM categories ORDER BY sort_order LIMIT 1" => "أول تصنيف",
];

foreach ($queries as $query => $label) {
    try {
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p style='color:green;'>✅ $label: استعلام ناجح</p>";
        } else {
            echo "<p style='color:red;'>❌ $label: " . htmlspecialchars($conn->error) . "</p>";
        }
    } catch (Throwable $e) {
        echo "<p style='color:red;'>❌ $label: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// 4. فحص الـ PHP Version وModules
echo "<h3>4️⃣ إعدادات PHP</h3>";
echo "<p>📦 الإصدار: " . htmlspecialchars(phpversion()) . "</p>";
echo "<p>📦 Extensions المثبتة:</p>";
echo "<ul>";
echo "<li>" . (extension_loaded('mysqli') ? "✅" : "❌") . " mysqli</li>";
echo "<li>" . (extension_loaded('json') ? "✅" : "❌") . " json</li>";
echo "<li>" . (extension_loaded('mbstring') ? "✅" : "❌") . " mbstring</li>";
echo "</ul>";

// 5. فحص الملفات والمجلدات
echo "<h3>5️⃣ فحص الملفات والمجلدات</h3>";
$paths = [
    'index.php' => 'الصفحة الرئيسية',
    'menu.php' => 'صفحة القائمة',
    'config.php' => 'ملف التكوين',
    'includes/functions.php' => 'ملف الدوال',
    'includes/header.php' => 'رأس الصفحة',
    'includes/footer.php' => 'تذييل الصفحة',
    'assets/css/style.css' => 'ملف CSS',
    'assets/js/script.js' => 'ملف JavaScript',
];

foreach ($paths as $path => $label) {
    $file = __DIR__ . '/' . $path;
    if (file_exists($file)) {
        echo "<p style='color:green;'>✅ $label موجود</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ $label غير موجود</p>";
    }
}

// 6. فحص الصلاحيات
echo "<h3>6️⃣ صلاحيات الملفات</h3>";
$dirs = ['uploads/', 'admin/', 'assets/', 'includes/'];
foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo "<p>📁 $dir: $perms</p>";
    }
}

// 7. اختبار تحميل header.php (هنا غالباً المشكلة)
echo "<h3>7️⃣ اختبار تحميل header.php</h3>";
try {
    ob_start();
    $_GET = $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SESSION = ['cart' => []];
    $page_title = 'اختبار';
    $base = '';
    $asset_base = '';
    $active = 'test';
    
    // لا نحمل header هنا لتجنب تكرار HTML، لكننا نتحقق من وجود الدوال
    if (function_exists('h') && function_exists('format_price') && function_exists('cart_count')) {
        echo "<p style='color:green;'>✅ جميع الدوال المطلوبة موجودة</p>";
    } else {
        echo "<p style='color:red;'>❌ دوال مفقودة</p>";
    }
    ob_end_clean();
} catch (Throwable $e) {
    echo "<p style='color:red;'>❌ خطأ: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 8. اختبار صفحة index.php
echo "<h3>8️⃣ اختبار تحميل index.php</h3>";
try {
    ob_start();
    include 'index.php';
    $output = ob_get_clean();
    if (strlen($output) > 100) {
        echo "<p style='color:green;'>✅ تم تحميل index.php بنجاح (" . strlen($output) . " بايت)</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ index.php أرجعت محتوى قصير جداً</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red;'>❌ خطأ في index.php:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p>✅ التشخيص انتهى. إذا كان كل شيء أخضر، المشكلة قد تكون في الاستضافة نفسها.</p>";
?>

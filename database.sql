-- Clean SQL schema for مطعم السلام
-- Replace 'restoal1_database' below with your actual database name if needed.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE restoal1_database;

-- ---------------------------------------------------------
-- جدول الأصناف (تصنيفات القائمة)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(100) NOT NULL,
    icon VARCHAR(10) DEFAULT '🍽️',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- بيانات مبدئية للأصناف
INSERT IGNORE INTO categories (id, name_ar, icon, sort_order) VALUES
    (1, 'الأطباق الرئيسية', '🍲', 1),
    (2, 'المشويات', '🔥', 2),
    (3, 'السلطات والمقبلات', '🥗', 3),
    (4, 'الشوربات', '🥣', 4),
    (5, 'الحلويات', '🍰', 5),
    (6, 'المشروبات', '🍵', 6);

-- ---------------------------------------------------------
-- جدول الأطباق
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name_ar VARCHAR(150) NOT NULL,
    description_ar TEXT,
    price DECIMAL(10,2) NOT NULL,
    color_tag VARCHAR(20) DEFAULT 'gold',
    is_available TINYINT(1) DEFAULT 1,
    image_filename VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- جدول الطلبات
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    notes TEXT,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('جديد','قيد التحضير','في الطريق','تم التسليم','ملغى') DEFAULT 'جديد',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- جدول عناصر كل طلب
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- جدول حسابات الإدارة
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- بيانات تجريبية للمسؤول
INSERT IGNORE INTO admin_users (username, password_hash)
VALUES ('admin', '$2y$12$t/dR6Y9czrduzCOLjbL/MeRyk1cFfL14TB3zfa4Ih02OpLmNnUB.y');

SET FOREIGN_KEY_CHECKS = 1;

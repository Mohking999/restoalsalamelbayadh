<?php
/**
 * functions.php - دوال مساعدة عامة
 */

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function format_price($price) {
    return number_format((float)$price, 0) . ' ' . CURRENCY;
}

function generate_order_code() {
    return 'ALS-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

function cart_count() {
    if (empty($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

function is_admin_logged_in() {
    return !empty($_SESSION['admin_id']);
}

function require_admin() {
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

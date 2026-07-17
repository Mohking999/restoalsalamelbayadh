<?php
require_once 'config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$item_id = (int)($_POST['item_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$item_id) {
    echo json_encode(['ok' => false, 'error' => 'عنصر غير صالح']);
    exit;
}

// تأكد أن الطبق موجود ومتوفر
$stmt = $conn->prepare("SELECT id FROM menu_items WHERE id = ? AND is_available = 1");
$stmt->bind_param('i', $item_id);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    echo json_encode(['ok' => false, 'error' => 'الطبق غير متوفر']);
    exit;
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($action === 'remove') {
    unset($_SESSION['cart'][$item_id]);
} else {
    if ($qty <= 0) {
        unset($_SESSION['cart'][$item_id]);
    } else {
        $_SESSION['cart'][$item_id] = min($qty, 20); // حد أقصى معقول للكمية
    }
}

// حساب المجموع الكلي ومجموع العنصر الحالي لرد AJAX ديناميكي
$total = 0;
$item_subtotal = 0;
$item_count = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $ids_str = implode(',', $ids);
    if ($ids_str) {
        $res = $conn->query("SELECT id, price FROM menu_items WHERE id IN ($ids_str)");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $curr_qty = $_SESSION['cart'][$row['id']];
                $sub = $curr_qty * $row['price'];
                $total += $sub;
                $item_count += $curr_qty;
                if ($row['id'] === $item_id) {
                    $item_subtotal = $sub;
                }
            }
        }
    }
}

echo json_encode([
    'ok' => true,
    'cart_count' => cart_count(),
    'item_count' => $item_count,
    'item_subtotal' => format_price($item_subtotal),
    'cart_total' => format_price($total),
    'is_empty' => empty($_SESSION['cart'])
]);


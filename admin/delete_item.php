<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    // احصل على الصورة أولاً
    $stmt = $conn->prepare("SELECT image_filename FROM menu_items WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // احذف الملف من النظام
    if ($result && !empty($result['image_filename']) && file_exists('../uploads/' . $result['image_filename'])) {
        unlink('../uploads/' . $result['image_filename']);
    }
    
    // احذف السجل من قاعدة البيانات
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}
redirect('menu.php');

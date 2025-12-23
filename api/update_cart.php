<?php
header('Content-Type: application/json');
require '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? 0;
$cart_data = json_encode($data['cart_items']);

// Lưu vào session hoặc bảng tạm
$_SESSION['cart_' . $user_id] = $cart_data;

echo json_encode([
    'success' => true,
    'message' => 'Đã lưu giỏ hàng'
]);
?>
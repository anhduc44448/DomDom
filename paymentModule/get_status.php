<?php
require_once '../config.php';

$app_trans_id = $_GET['apptransid'] ?? '';

if (!$app_trans_id) {
    echo json_encode(['error' => 'Missing app_trans_id']);
    exit;
}

$preparation_map = [
    'preparing' => 'Đang chuẩn bị',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
];

$stmt = $conn->prepare("
    SELECT p.*,
           o.status AS order_status
    FROM payments p
    JOIN orders o ON p.order_id = o.id
    WHERE p.transaction_id = ?
");

if ($stmt) {
    $stmt->bind_param("s", $app_trans_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        echo json_encode([
            'payment_status' => $data['payment_status'],
            'order_status' => $data['order_status'],
            'order_status_text' => $preparation_map[$data['order_status']] ?? $data['order_status']
        ]);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Database error']);
}
?>

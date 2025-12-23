<?php
header('Content-Type: application/json');
require '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

// 1. Tạo order
$stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, table_number, total_amount) 
                        VALUES (?, ?, ?, ?)");
$stmt->bind_param("issd", 
    $data['user_id'],
    $data['customer_name'],
    $data['table_number'],
    $data['total_amount']
);
$stmt->execute();
$order_id = $conn->insert_id;

// 2. Lưu items
foreach ($data['items'] as $item) {
    $stmt2 = $conn->prepare("INSERT INTO order_items 
                            (order_id, product_name, quantity, size, unit_price, total_price) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("isidd", 
        $order_id,
        $item['product_name'],
        $item['quantity'],
        $item['size'],
        $item['unit_price'],
        $item['total_price']
    );
    $stmt2->execute();
}

echo json_encode([
    'success' => true, 
    'order_id' => $order_id
]);

$conn->close();
?>
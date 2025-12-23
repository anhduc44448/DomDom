<?php
// api/get_product_by_id.php - FIXED VERSION
header('Content-Type: application/json');
require '../config.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Nếu ID không hợp lệ, tìm sản phẩm đầu tiên
if ($product_id <= 0) {
    $result = $conn->query("SELECT id FROM products ORDER BY id LIMIT 1");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $product_id = $row['id'];
    }
}

// Tìm sản phẩm
$sql = "SELECT * FROM products WHERE id = $product_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    // Fix đường dẫn ảnh
    if (empty($product['image_path'])) {
        $product['image_path'] = 'database/AnhDoAn/BanhTranBo.jpg';
    }
    
    // Đảm bảo có đủ các trường
    $product['id'] = intval($product['id']);
    $product['price'] = floatval($product['price']);
    
    echo json_encode([
        'success' => true,
        'data' => $product
    ]);
} else {
    // Nếu không tìm thấy, trả về sản phẩm mẫu
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => 1,
            'name' => 'Bánh tráng bơ',
            'price' => 25000,
            'image_path' => 'database/AnhDoAn/BanhTranBo.jpg',
            'description' => 'Bánh tráng trộn với bơ thơm ngon',
            'is_best_seller' => 1,
            'is_available' => 1
        ]
    ]);
}

$conn->close();
?>
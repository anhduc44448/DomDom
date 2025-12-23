<?php
// api/get_products.php - FIXED VERSION
header('Content-Type: application/json');
require '../config.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'all';

$sql = "SELECT * FROM products WHERE is_available = 1";

if ($type === 'best_seller') {
    $sql .= " AND is_best_seller = 1";
}

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $products = [];
    while($row = $result->fetch_assoc()) {
        // Đảm bảo có ID và đường dẫn ảnh
        $row['id'] = intval($row['id']);
        $row['price'] = floatval($row['price']);
        
        if (empty($row['image_path'])) {
            $row['image_path'] = 'database/AnhDoAn/BanhTranBo.jpg';
        }
        
        if (empty($row['category_id'])) {
            $row['category_id'] = $row['price'] < 30000 ? 1 : 2;
        }
        
        $products[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $products
    ]);
} else {
    // Trả về sản phẩm mẫu nếu database trống
    echo json_encode([
        'success' => true,
        'data' => [
            [
                'id' => 1,
                'name' => 'Bánh tráng bơ',
                'price' => 25000,
                'image_path' => 'database/AnhDoAn/BanhTranBo.jpg',
                'category_id' => 1,
                'is_best_seller' => 1,
                'is_available' => 1
            ],
            [
                'id' => 2,
                'name' => 'Trà đào cam sả',
                'price' => 32000,
                'image_path' => 'database/AnhDoUong/tra_dao_cam_sa.png',
                'category_id' => 2,
                'is_best_seller' => 1,
                'is_available' => 1
            ],
            [
                'id' => 3,
                'name' => 'Trà sữa trân châu đường đen',
                'price' => 35000,
                'image_path' => 'database/AnhDoUong/tra_sua_tran_chau_duong_den.png',
                'category_id' => 2,
                'is_best_seller' => 1,
                'is_available' => 1
            ]
        ]
    ]);
}

if ($conn) {
    $conn->close();
}
?>
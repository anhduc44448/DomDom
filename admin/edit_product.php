<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Lấy thông tin sản phẩm cần sửa
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Lấy danh mục
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Xử lý cập nhật sản phẩm
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    // Xử lý upload ảnh mới
    $image_path = $product['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../database/';
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $image_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'database/' . $image_name;
        }
    }

    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image_path = ?, is_best_seller = ?, is_available = ? WHERE id = ?");
    $stmt->bind_param("ssdissii", $name, $description, $price, $category_id, $image_path, $is_best_seller, $is_available, $product_id);
    
    if ($stmt->execute()) {
        $success = "Cập nhật sản phẩm thành công!";
        // Cập nhật lại thông tin sản phẩm
        $product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();
    } else {
        $error = "Lỗi khi cập nhật sản phẩm: " . $conn->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Sản phẩm - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h2><i class="fas fa-edit"></i> Sửa Sản phẩm</h2>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <div class="content">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" class="product-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Tên sản phẩm *</label>
                                    <input type="text" id="name" name="name" class="form-control" 
                                           value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="price">Giá (VNĐ) *</label>
                                    <input type="number" id="price" name="price" class="form-control" 
                                           value="<?php echo $product['price']; ?>" min="1000" step="1000" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Mô tả sản phẩm</label>
                                <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="category_id">Danh mục *</label>
                                    <select id="category_id" name="category_id" class="form-control" required>
                                        <option value="">Chọn danh mục</option>
                                        <?php 
                                        $categories->data_seek(0); // Reset pointer
                                        while($category = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                                <?php echo $category['name']; ?> (<?php echo $category['type']; ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="image">Hình ảnh sản phẩm</label>
                                    <?php if ($product['image_path']): ?>
                                        <div class="current-image">
                                            <img src="../<?php echo $product['image_path']; ?>" alt="Current image" class="product-image-preview">
                                            <small>Ảnh hiện tại</small>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                    <small class="form-text">Để trống nếu không thay đổi ảnh</small>
                                </div>
                            </div>

                            <div class="form-check-group">
                                <div class="form-check">
                                    <input type="checkbox" id="is_best_seller" name="is_best_seller" class="form-check-input"
                                        <?php echo $product['is_best_seller'] ? 'checked' : ''; ?>>
                                    <label for="is_best_seller" class="form-check-label">
                                        <i class="fas fa-star"></i> Best Seller
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input type="checkbox" id="is_available" name="is_available" class="form-check-input"
                                        <?php echo $product['is_available'] ? 'checked' : ''; ?>>
                                    <label for="is_available" class="form-check-label">
                                        <i class="fas fa-check"></i> Hiển thị trên menu
                                    </label>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="update_product" class="btn btn-success btn-lg">
                                    <i class="fas fa-save"></i> Cập nhật Sản phẩm
                                </button>
                                <a href="products.php" class="btn btn-secondary">Hủy</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
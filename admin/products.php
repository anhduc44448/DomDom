<?php
session_start();
require '../config.php';

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Xử lý thêm sản phẩm mới
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    // Xử lý upload ảnh
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../database/';
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $image_name;
        
        // Kiểm tra và upload ảnh
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'database/' . $image_name;
        }
    }

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, image_path, is_best_seller, is_available) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdissi", $name, $description, $price, $category_id, $image_path, $is_best_seller, $is_available);
    
    if ($stmt->execute()) {
        $success = "Thêm sản phẩm thành công!";
    } else {
        $error = "Lỗi khi thêm sản phẩm: " . $conn->error;
    }
    $stmt->close();
}

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $success = "Xóa sản phẩm thành công!";
    } else {
        $error = "Lỗi khi xóa sản phẩm: " . $conn->error;
    }
    $stmt->close();
}

// Xử lý cập nhật trạng thái best seller
if (isset($_GET['toggle_best_seller'])) {
    $product_id = intval($_GET['toggle_best_seller']);
    
    // Lấy trạng thái hiện tại
    $current = $conn->query("SELECT is_best_seller FROM products WHERE id = $product_id")->fetch_assoc();
    $new_status = $current['is_best_seller'] ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE products SET is_best_seller = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $product_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: products.php');
    exit();
}

// Xử lý cập nhật trạng thái available
if (isset($_GET['toggle_available'])) {
    $product_id = intval($_GET['toggle_available']);
    
    $current = $conn->query("SELECT is_available FROM products WHERE id = $product_id")->fetch_assoc();
    $new_status = $current['is_available'] ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE products SET is_available = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $product_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: products.php');
    exit();
}

// Lấy danh sách sản phẩm
$products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC
");

// Lấy danh mục cho dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Sản phẩm - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Include sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h2><i class="fas fa-utensils"></i> Quản lý Sản phẩm</h2>
                <div class="header-actions">
                    <span class="product-count">
                        <i class="fas fa-box"></i> 
                        <?php echo $products->num_rows; ?> sản phẩm
                    </span>
                </div>
            </div>

            <!-- Content -->
            <div class="content">
                <!-- Thông báo -->
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

                <!-- Form thêm sản phẩm -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plus-circle"></i> Thêm Sản phẩm Mới</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" class="product-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Tên sản phẩm *</label>
                                    <input type="text" id="name" name="name" class="form-control" placeholder="Ví dụ: Bánh tráng bơ" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="price">Giá (VNĐ) *</label>
                                    <input type="number" id="price" name="price" class="form-control" placeholder="25000" min="1000" step="1000" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Mô tả sản phẩm</label>
                                <textarea id="description" name="description" class="form-control" placeholder="Mô tả chi tiết về sản phẩm..." rows="3"></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="category_id">Danh mục *</label>
                                    <select id="category_id" name="category_id" class="form-control" required>
                                        <option value="">Chọn danh mục</option>
                                        <?php while($category = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $category['id']; ?>">
                                                <?php echo $category['name']; ?> (<?php echo $category['type']; ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="image">Hình ảnh sản phẩm</label>
                                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                    <small class="form-text">Chấp nhận: JPG, PNG, GIF (Tối đa 2MB)</small>
                                </div>
                            </div>

                            <div class="form-check-group">
                                <div class="form-check">
                                    <input type="checkbox" id="is_best_seller" name="is_best_seller" class="form-check-input">
                                    <label for="is_best_seller" class="form-check-label">
                                        <i class="fas fa-star"></i> Best Seller
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input type="checkbox" id="is_available" name="is_available" class="form-check-input" checked>
                                    <label for="is_available" class="form-check-label">
                                        <i class="fas fa-check"></i> Hiển thị trên menu
                                    </label>
                                </div>
                            </div>

                            <button type="submit" name="add_product" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus"></i> Thêm Sản phẩm
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Danh sách sản phẩm -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-list"></i> Danh sách Sản phẩm</h4>
                        <div class="card-actions">
                            <span class="filter-info">
                                Hiển thị <?php echo $products->num_rows; ?> sản phẩm
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($products->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Hình ảnh</th>
                                            <th>Tên sản phẩm</th>
                                            <th>Danh mục</th>
                                            <th>Giá</th>
                                            <th>Trạng thái</th>
                                            <th>Best Seller</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($product = $products->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td>
                                                <?php if ($product['image_path']): ?>
                                                    <img src="../<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                                                <?php else: ?>
                                                    <div class="no-image">📷</div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo $product['name']; ?></strong>
                                                <?php if ($product['description']): ?>
                                                    <br><small class="text-muted"><?php echo substr($product['description'], 0, 50); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $product['category_name']; ?></td>
                                            <td class="price"><?php echo number_format($product['price']); ?>đ</td>
                                            <td>
                                                <a href="?toggle_available=<?php echo $product['id']; ?>" class="status-toggle <?php echo $product['is_available'] ? 'active' : 'inactive'; ?>">
                                                    <?php echo $product['is_available'] ? 'Hiển thị' : 'Ẩn'; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="?toggle_best_seller=<?php echo $product['id']; ?>" class="best-seller-toggle <?php echo $product['is_best_seller'] ? 'active' : ''; ?>">
                                                    <?php echo $product['is_best_seller'] ? '⭐' : '☆'; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?delete=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-data">
                                <i class="fas fa-box-open"></i>
                                <h4>Chưa có sản phẩm nào</h4>
                                <p>Hãy thêm sản phẩm đầu tiên của bạn!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview image before upload
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // You can add image preview here if needed
                    console.log('Image selected:', file.name);
                }
                reader.readAsDataURL(file);
            }
        });

        // Auto format price input
        document.getElementById('price').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value) {
                e.target.value = parseInt(value).toLocaleString('vi-VN');
            }
        });
    </script>
</body>
</html>
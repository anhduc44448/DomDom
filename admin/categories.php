<?php
session_start();
require '../config.php';

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Xử lý thêm danh mục mới
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    
    // Kiểm tra danh mục đã tồn tại chưa
    $check_stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error = "Danh mục '$name' đã tồn tại!";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name, type) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $type);
        
        if ($stmt->execute()) {
            $success = "Thêm danh mục '$name' thành công!";
        } else {
            $error = "Lỗi khi thêm danh mục: " . $conn->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Xử lý xóa danh mục
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    
    // Kiểm tra xem danh mục có sản phẩm không
    $check_products = $conn->query("SELECT COUNT(*) as product_count FROM products WHERE category_id = $category_id");
    $product_count = $check_products->fetch_assoc()['product_count'];
    
    if ($product_count > 0) {
        $error = "Không thể xóa danh mục này vì có $product_count sản phẩm đang thuộc danh mục này!";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        
        if ($stmt->execute()) {
            $success = "Xóa danh mục thành công!";
        } else {
            $error = "Lỗi khi xóa danh mục: " . $conn->error;
        }
        $stmt->close();
    }
}

// Xử lý sửa danh mục
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_category'])) {
    $category_id = intval($_POST['category_id']);
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    
    $stmt = $conn->prepare("UPDATE categories SET name = ?, type = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $type, $category_id);
    
    if ($stmt->execute()) {
        $success = "Cập nhật danh mục thành công!";
    } else {
        $error = "Lỗi khi cập nhật danh mục: " . $conn->error;
    }
    $stmt->close();
}

// Lấy danh sách danh mục với số lượng sản phẩm
$categories = $conn->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.type, c.name
");

// Thống kê
$stats = [
    'total' => $conn->query("SELECT COUNT(*) FROM categories")->fetch_row()[0],
    'food' => $conn->query("SELECT COUNT(*) FROM categories WHERE type = 'food'")->fetch_row()[0],
    'drink' => $conn->query("SELECT COUNT(*) FROM categories WHERE type = 'drink'")->fetch_row()[0]
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Danh mục - Admin</title>
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
                <h2><i class="fas fa-folder"></i> Quản lý Danh mục</h2>
                <div class="header-actions">
                    <span class="category-count">
                        <i class="fas fa-layer-group"></i> 
                        <?php echo $stats['total']; ?> danh mục
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

                <!-- Thống kê nhanh -->
                <div class="stats-grid compact">
                    <div class="stat-card mini">
                        <div class="stat-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total']; ?></h3>
                            <p>Tổng danh mục</p>
                        </div>
                    </div>

                    <div class="stat-card mini">
                        <div class="stat-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['food']; ?></h3>
                            <p>Đồ ăn</p>
                        </div>
                    </div>

                    <div class="stat-card mini">
                        <div class="stat-icon">
                            <i class="fas fa-coffee"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['drink']; ?></h3>
                            <p>Thức uống</p>
                        </div>
                    </div>
                </div>

                <!-- Form thêm danh mục mới -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plus-circle"></i> Thêm Danh mục Mới</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="category-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Tên danh mục *</label>
                                    <input type="text" id="name" name="name" class="form-control" 
                                           placeholder="Ví dụ: Trà sữa, Cà phê, Đồ ăn vặt..." required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="type">Loại danh mục *</label>
                                    <select id="type" name="type" class="form-control" required>
                                        <option value="">Chọn loại</option>
                                        <option value="food">🍽️ Đồ ăn</option>
                                        <option value="drink">🥤 Thức uống</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" name="add_category" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm Danh mục
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Danh sách danh mục -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-list"></i> Danh sách Danh mục</h4>
                        <div class="card-actions">
                            <span class="filter-info">
                                Hiển thị <?php echo $categories->num_rows; ?> danh mục
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($categories->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên danh mục</th>
                                            <th>Loại</th>
                                            <th>Số sản phẩm</th>
                                            <th>Ngày tạo</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($category = $categories->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="category-type category-type-<?php echo $category['type']; ?>">
                                                    <?php if ($category['type'] == 'food'): ?>
                                                        🍽️ Đồ ăn
                                                    <?php else: ?>
                                                        🥤 Thức uống
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="product-count-badge">
                                                    <?php echo $category['product_count']; ?> sản phẩm
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($category['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <!-- Button trigger modal edit -->
                                                    <button type="button" class="btn btn-warning btn-sm" 
                                                            onclick="openEditModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', '<?php echo $category['type']; ?>')"
                                                            title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm" 
                                                       title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa danh mục <?php echo htmlspecialchars($category['name']); ?>?')">
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
                                <i class="fas fa-folder-open"></i>
                                <h4>Chưa có danh mục nào</h4>
                                <p>Hãy thêm danh mục đầu tiên của bạn!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Category -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i class="fas fa-edit"></i> Sửa Danh mục</h4>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" id="editCategoryForm">
                    <input type="hidden" id="edit_category_id" name="category_id">
                    
                    <div class="form-group">
                        <label for="edit_name">Tên danh mục *</label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_type">Loại danh mục *</label>
                        <select id="edit_type" name="type" class="form-control" required>
                            <option value="food">🍽️ Đồ ăn</option>
                            <option value="drink">🥤 Thức uống</option>
                        </select>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="submit" name="update_category" class="btn btn-success">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openEditModal(id, name, type) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_type').value = type;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }

        // Auto focus on name input when adding new category
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('name').focus();
        });
    </script>

    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: #4E342E;
            color: white;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h4 {
            margin: 0;
            font-size: 1.2rem;
        }

        .close {
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #FF7043;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        /* Category Type Badges */
        .category-type {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .category-type-food {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .category-type-drink {
            background: #e3f2fd;
            color: #1565c0;
        }

        /* Product Count Badge */
        .product-count-badge {
            background: #f8f9fa;
            color: #666;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            border: 1px solid #e0e0e0;
        }

        /* Category Form */
        .category-form .form-row {
            grid-template-columns: 2fr 1fr;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .category-form .form-row {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
        }
    </style>
</body>
</html>
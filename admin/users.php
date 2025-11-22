<?php
session_start();
require '../config.php';

// Chỉ admin mới được quản lý user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Xử lý cập nhật role user
if (isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role'];
    
    // Không cho phép tự sửa role của chính mình
    if ($user_id == $_SESSION['user_id']) {
        $error = "Bạn không thể thay đổi quyền của chính mình!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        
        if ($stmt->execute()) {
            $success = "Cập nhật quyền người dùng thành công!";
        } else {
            $error = "Lỗi khi cập nhật quyền: " . $conn->error;
        }
        $stmt->close();
    }
}

// Xử lý xóa user
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Không cho phép tự xóa chính mình
    if ($user_id == $_SESSION['user_id']) {
        $error = "Bạn không thể xóa tài khoản của chính mình!";
    } else {
        // Kiểm tra xem user có đơn hàng không
        $check_orders = $conn->query("SELECT COUNT(*) as order_count FROM orders WHERE user_id = $user_id");
        $order_count = $check_orders->fetch_assoc()['order_count'];
        
        if ($order_count > 0) {
            $error = "Không thể xóa người dùng này vì có $order_count đơn hàng liên quan!";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $success = "Xóa người dùng thành công!";
            } else {
                $error = "Lỗi khi xóa người dùng: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Lấy danh sách users với thống kê
$users = $conn->query("
    SELECT u.*, COUNT(o.id) as order_count 
    FROM users u 
    LEFT JOIN orders o ON u.id = o.user_id 
    GROUP BY u.id 
    ORDER BY u.role, u.created_at DESC
");

// Thống kê
$stats = [
    'total' => $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0],
    'admin' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetch_row()[0],
    'staff' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetch_row()[0],
    'customer' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetch_row()[0]
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Người dùng - Admin</title>
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
                <h2><i class="fas fa-users"></i> Quản lý Người dùng</h2>
                <div class="header-actions">
                    <span class="user-count">
                        <i class="fas fa-user-friends"></i> 
                        <?php echo $stats['total']; ?> người dùng
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
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total']; ?></h3>
                            <p>Tổng người dùng</p>
                        </div>
                    </div>

                    <div class="stat-card mini">
                        <div class="stat-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['admin']; ?></h3>
                            <p>Quản trị viên</p>
                        </div>
                    </div>

                    <div class="stat-card mini">
                        <div class="stat-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['staff']; ?></h3>
                            <p>Nhân viên</p>
                        </div>
                    </div>

                    <div class="stat-card mini">
                        <div class="stat-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['customer']; ?></h3>
                            <p>Khách hàng</p>
                        </div>
                    </div>
                </div>

                <!-- Danh sách người dùng -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-list"></i> Danh sách Người dùng</h4>
                        <div class="card-actions">
                            <span class="filter-info">
                                Hiển thị <?php echo $users->num_rows; ?> người dùng
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($users->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Thông tin</th>
                                            <th>Vai trò</th>
                                            <th>Đơn hàng</th>
                                            <th>Premium</th>
                                            <th>Ngày tạo</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($user = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td>
                                                <div class="user-info">
                                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                        <br><small class="current-user">👈 Tài khoản của bạn</small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <form method="POST" class="role-form">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <select name="role" class="role-select role-<?php echo $user['role']; ?>" 
                                                            <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : 'onchange="this.form.submit()"'; ?>>
                                                        <option value="customer" <?php echo $user['role'] == 'customer' ? 'selected' : ''; ?>>Khách hàng</option>
                                                        <option value="staff" <?php echo $user['role'] == 'staff' ? 'selected' : ''; ?>>Nhân viên</option>
                                                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Quản trị</option>
                                                    </select>
                                                    <button type="submit" name="update_role" class="d-none">Cập nhật</button>
                                                </form>
                                            </td>
                                            <td>
                                                <span class="order-count-badge">
                                                    <?php echo $user['order_count']; ?> đơn
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['is_premium']): ?>
                                                    <span class="premium-badge">
                                                        <i class="fas fa-crown"></i> Premium
                                                    </span>
                                                <?php else: ?>
                                                    <span class="standard-badge">
                                                        Standard
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" 
                                                           title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa người dùng <?php echo htmlspecialchars($user['username']); ?>?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="btn btn-secondary btn-sm disabled" title="Không thể xóa chính mình">
                                                            <i class="fas fa-trash"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-data">
                                <i class="fas fa-users-slash"></i>
                                <h4>Không có người dùng nào</h4>
                                <p>Chưa có người dùng trong hệ thống!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Users Management Styles */
        .user-count {
            background: #f3e5f5;
            color: #7b1fa2;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }

        .user-info {
            line-height: 1.4;
        }

        .current-user {
            color: #FF7043;
            font-weight: 500;
            font-size: 0.7rem;
        }

        /* Role Select */
        .role-form {
            min-width: 120px;
        }

        .role-select {
            width: 100%;
            padding: 6px 10px;
            border: 2px solid;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .role-select:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .role-select.role-admin {
            border-color: #ffd54f;
            background: #fff8e1;
            color: #ff8f00;
        }

        .role-select.role-staff {
            border-color: #90caf9;
            background: #e3f2fd;
            color: #1565c0;
        }

        .role-select.role-customer {
            border-color: #c8e6c9;
            background: #e8f5e8;
            color: #2e7d32;
        }

        /* Badges */
        .order-count-badge {
            background: #f8f9fa;
            color: #666;
            padding: 6px 10px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid #e0e0e0;
            display: inline-block;
        }

        .premium-badge {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #856404;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }

        .standard-badge {
            background: #f8f9fa;
            color: #666;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 500;
            border: 1px solid #e0e0e0;
            display: inline-block;
        }

        .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .role-form {
                min-width: 100px;
            }
            
            .user-info {
                font-size: 0.9rem;
            }
        }
    </style>
</body>
</html>
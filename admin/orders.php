<?php
session_start();
require '../config.php';

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header('Location: ../login.php');
    exit();
}

// Xử lý cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $success = "Cập nhật trạng thái đơn hàng #$order_id thành công!";
    } else {
        $error = "Lỗi khi cập nhật đơn hàng: " . $conn->error;
    }
    $stmt->close();
}

// Xử lý xóa đơn hàng
if (isset($_GET['delete'])) {
    $order_id = intval($_GET['delete']);
    
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    try {
        // Xóa các items trong đơn hàng trước
        $stmt1 = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt1->bind_param("i", $order_id);
        $stmt1->execute();
        $stmt1->close();
        
        // Xóa đơn hàng
        $stmt2 = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt2->bind_param("i", $order_id);
        $stmt2->execute();
        $stmt2->close();
        
        $conn->commit();
        $success = "Xóa đơn hàng #$order_id thành công!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Lỗi khi xóa đơn hàng: " . $e->getMessage();
    }
}

// Lọc và tìm kiếm
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Xây dựng query với filter
$where_conditions = [];
$params = [];
$types = '';

if ($filter_status && $filter_status !== 'all') {
    $where_conditions[] = "o.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if ($search) {
    $where_conditions[] = "(o.customer_name LIKE ? OR o.customer_phone LIKE ? OR o.id = ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = $search;
    $types .= 'sss';
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// Lấy danh sách đơn hàng
$sql = "
    SELECT o.*, u.username, 
           COUNT(oi.id) as item_count,
           SUM(oi.quantity) as total_quantity
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    $where_sql
    GROUP BY o.id 
    ORDER BY o.order_date DESC
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result();

// Thống kê đơn hàng
$stats = [
    'total' => $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0],
    'pending' => $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetch_row()[0],
    'confirmed' => $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'confirmed'")->fetch_row()[0],
    'delivering' => $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'delivering'")->fetch_row()[0],
    'completed' => $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetch_row()[0],
    'cancelled' => $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetch_row()[0]
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn hàng - Admin</title>
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
                <h2><i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng</h2>
                <div class="header-actions">
                    <span class="order-count">
                        <i class="fas fa-clipboard-list"></i> 
                        <?php echo $stats['total']; ?> đơn hàng
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
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['pending']; ?></h3>
                            <p>Chờ xử lý</p>
                        </div>
                    </div>

                    <div class="stat-card mini">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['confirmed']; ?></h3>
                            <p>Đã xác nhận</p>
                        </div>
                    </div>

                    <div class="stat-card mini">
                        <div class="stat-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['delivering']; ?></h3>
                            <p>Đang giao</p>
                        </div>
                    </div>

                    <div class="stat-card mini">
                        <div class="stat-icon">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['completed']; ?></h3>
                            <p>Hoàn thành</p>
                        </div>
                    </div>

                    <div class="stat-card mini">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['cancelled']; ?></h3>
                            <p>Đã hủy</p>
                        </div>
                    </div>
                </div>

                <!-- Bộ lọc và tìm kiếm -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-filter"></i> Bộ lọc & Tìm kiếm</h4>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="filter-form">
                            <div class="filter-row">
                                <div class="filter-group">
                                    <label for="status">Lọc theo trạng thái:</label>
                                    <select id="status" name="status" class="form-control">
                                        <option value="all">Tất cả trạng thái</option>
                                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                        <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                        <option value="delivering" <?php echo $filter_status === 'delivering' ? 'selected' : ''; ?>>Đang giao</option>
                                        <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                        <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                    </select>
                                </div>
                                
                                <div class="filter-group">
                                    <label for="search">Tìm kiếm:</label>
                                    <input type="text" id="search" name="search" class="form-control" 
                                           placeholder="Tên KH, SĐT hoặc mã đơn..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                
                                <div class="filter-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Lọc
                                    </button>
                                    <a href="orders.php" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Xóa lọc
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danh sách đơn hàng -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-list"></i> Danh sách Đơn hàng</h4>
                        <div class="card-actions">
                            <span class="filter-info">
                                Hiển thị <?php echo $orders->num_rows; ?> đơn hàng
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($orders->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Khách hàng</th>
                                            <th>Số điện thoại</th>
                                            <th>Sản phẩm</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày đặt</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($order = $orders->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo $order['id']; ?></strong>
                                                <?php if ($order['username']): ?>
                                                    <br><small class="text-muted">@<?php echo $order['username']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                                <br><small class="text-muted"><?php echo substr($order['customer_address'], 0, 30); ?>...</small>
                                            </td>
                                            <td><?php echo $order['customer_phone']; ?></td>
                                            <td>
                                                <span class="badge"><?php echo $order['item_count']; ?> món</span>
                                                <br><small><?php echo $order['total_quantity']; ?> sản phẩm</small>
                                            </td>
                                            <td class="price"><?php echo number_format($order['total_amount']); ?>đ</td>
                                            <td>
                                                <form method="POST" class="status-form">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="status" class="status-select status-<?php echo $order['status']; ?>" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                                        <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                                        <option value="delivering" <?php echo $order['status'] == 'delivering' ? 'selected' : ''; ?>>Đang giao</option>
                                                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                                    </select>
                                                    <button type="submit" name="update_status" class="d-none">Cập nhật</button>
                                                </form>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($order['order_date'])); ?>
                                                <br><small><?php echo date('H:i', strtotime($order['order_date'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm" title="Chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                                        <a href="?delete=<?php echo $order['id']; ?>" class="btn btn-danger btn-sm" title="Xóa" 
                                                           onclick="return confirm('Bạn có chắc muốn xóa đơn hàng #<?php echo $order['id']; ?>?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
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
                                <i class="fas fa-clipboard-list"></i>
                                <h4>Không tìm thấy đơn hàng nào</h4>
                                <p>Hãy thử thay đổi bộ lọc hoặc từ khóa tìm kiếm!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto submit form khi chọn trạng thái
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>
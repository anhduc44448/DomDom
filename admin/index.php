<?php
// admin/index.php - Trang tổng quan
session_start();
require '../config.php';

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Lấy thông tin thống kê
$stats = [];
$stats['total_products'] = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$stats['total_orders'] = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$stats['total_users'] = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$stats['pending_orders'] = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetch_row()[0];

// Lấy doanh thu hôm nay
$today = date('Y-m-d');
$revenue_today = $conn->query("SELECT SUM(total_amount) FROM orders WHERE DATE(order_date) = '$today' AND status = 'completed'")->fetch_row()[0];
$stats['revenue_today'] = $revenue_today ? $revenue_today : 0;

// Lấy đơn hàng mới nhất (5 đơn)
$recent_orders = $conn->query("
    SELECT o.*, u.username 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC 
    LIMIT 5
");

// Lấy sản phẩm bán chạy
$best_sellers = $conn->query("
    SELECT p.name, COUNT(oi.id) as sold_count
    FROM products p 
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE p.is_best_seller = 1
    GROUP BY p.id 
    ORDER BY sold_count DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin Đom đóm quán</title>
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
                <h2><i class="fas fa-tachometer-alt"></i> Bảng Điều Khiển</h2>
                <div class="header-info">
                    <span class="date">📅 <?php echo date('d/m/Y'); ?></span>
                    <span class="time">🕒 <?php echo date('H:i:s'); ?></span>
                </div>
            </div>

            <!-- Content -->
            <div class="content">
                <!-- Thống kê tổng quan -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_products']; ?></h3>
                            <p>Tổng Sản Phẩm</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_orders']; ?></h3>
                            <p>Tổng Đơn Hàng</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_users']; ?></h3>
                            <p>Người Dùng</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['pending_orders']; ?></h3>
                            <p>Đơn Chờ Xử Lý</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['revenue_today']); ?>đ</h3>
                            <p>Doanh Thu Hôm Nay</p>
                        </div>
                    </div>
                </div>

                <!-- Hai cột: Đơn hàng mới + Sản phẩm bán chạy -->
                <div class="dashboard-grid">
                    <!-- Đơn hàng mới nhất -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h4><i class="fas fa-list"></i> Đơn Hàng Mới Nhất</h4>
                            <a href="orders.php" class="view-all">Xem tất cả</a>
                        </div>
                        <div class="card-body">
                            <?php if ($recent_orders->num_rows > 0): ?>
                                <div class="order-list">
                                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                                        <div class="order-item">
                                            <div class="order-info">
                                                <strong>#<?php echo $order['id']; ?></strong>
                                                <span><?php echo $order['customer_name']; ?></span>
                                            </div>
                                            <div class="order-meta">
                                                <span class="amount"><?php echo number_format($order['total_amount']); ?>đ</span>
                                                <span class="status status-<?php echo $order['status']; ?>">
                                                    <?php
                                                    $status_text = [
                                                        'preparing' => 'Đang chuẩn bị',
                                                        'completed' => 'Hoàn thành',
                                                        'cancelled' => 'Đã hủy'
                                                    ];
                                                    echo $status_text[$order['status']];
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-data">Chưa có đơn hàng nào</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sản phẩm bán chạy -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h4><i class="fas fa-star"></i> Sản Phẩm Nổi Bật</h4>
                        </div>
                        <div class="card-body">
                            <?php if ($best_sellers->num_rows > 0): ?>
                                <div class="product-list">
                                    <?php while($product = $best_sellers->fetch_assoc()): ?>
                                        <div class="product-item">
                                            <span class="product-name"><?php echo $product['name']; ?></span>
                                            <span class="sold-count"><?php echo $product['sold_count']; ?> lượt</span>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-data">Chưa có sản phẩm bán chạy</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h4>Thao Tác Nhanh</h4>
                    <div class="action-buttons">
                        <a href="products.php?action=add" class="action-btn">
                            <i class="fas fa-plus"></i>
                            <span>Thêm Sản Phẩm</span>
                        </a>
                        <a href="orders.php" class="action-btn">
                            <i class="fas fa-eye"></i>
                            <span>Xem Đơn Hàng</span>
                        </a>
                        <a href="categories.php" class="action-btn">
                            <i class="fas fa-folder"></i>
                            <span>Quản Lý Danh Mục</span>
                        </a>
                        <a href="../index.html" target="_blank" class="action-btn">
                            <i class="fas fa-external-link-alt"></i>
                            <span>Xem Website</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Cập nhật thời gian thực
        function updateTime() {
            const now = new Date();
            const timeElement = document.querySelector('.time');
            timeElement.textContent = '🕒 ' + now.toLocaleTimeString('vi-VN');
        }
        setInterval(updateTime, 1000);
    </script>
</body>
</html>

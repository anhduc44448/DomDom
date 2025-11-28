<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header('Location: ../login.php');
    exit();
}

// Lấy thông tin đơn hàng
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = $conn->query("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = $order_id
")->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Lấy chi tiết các sản phẩm trong đơn hàng
$order_items = $conn->query("
    SELECT oi.*, p.image_path 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = $order_id
");

// Xử lý cập nhật trạng thái
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $success = "Cập nhật trạng thái thành công!";
        // Cập nhật lại thông tin đơn hàng
        $order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết Đơn hàng #<?php echo $order_id; ?> - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h2>
                    <i class="fas fa-file-invoice"></i> 
                    Chi tiết Đơn hàng #<?php echo $order_id; ?>
                </h2>
                <div class="header-actions">
                    <a href="orders.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> In đơn
                    </button>
                </div>
            </div>

            <div class="content">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <div class="order-detail-grid">
                    <!-- Thông tin đơn hàng -->
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-info-circle"></i> Thông tin Đơn hàng</h4>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Mã đơn hàng:</label>
                                    <span>#<?php echo $order_id; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Ngày đặt:</label>
                                    <span><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Trạng thái:</label>
                                    <span class="status status-<?php echo $order['status']; ?>">
                                        <?php 
                                        $status_text = [
                                            'preparing' => '🔄 Đang chuẩn bị',
                                            'completed' => '✅ Hoàn thành', 
                                            'cancelled' => '❌ Đã hủy'
                                        ];
                                        echo $status_text[$order['status']] ?? $order['status'];
                                        ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Tổng tiền:</label>
                                    <span class="price"><?php echo number_format($order['total_amount']); ?>đ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin đặt hàng -->
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-user"></i> Thông tin Đặt hàng</h4>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Tên khách hàng:</label>
                                    <span><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></span>
                                </div>
                                <div class="info-item">
                                    <label>Số bàn:</label>
                                    <span class="table-number"><?php echo $order['table_number']; ?></span>
                                </div>
                                <?php if ($order['username']): ?>
                                <div class="info-item">
                                    <label>Tài khoản:</label>
                                    <span>@<?php echo $order['username']; ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($order['customer_note']): ?>
                                <div class="info-item">
                                    <label>Ghi chú:</label>
                                    <span class="customer-note">"<?php echo htmlspecialchars($order['customer_note']); ?>"</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Cập nhật trạng thái -->
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-sync"></i> Cập nhật Trạng thái</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="status">Trạng thái mới:</label>
                                    <select id="status" name="status" class="form-control" required>
                                        <option value="preparing" <?php echo $order['status'] == 'preparing' ? 'selected' : ''; ?>>🔄 Đang chuẩn bị</option>
                                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>✅ Hoàn thành</option>
                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>❌ Đã hủy</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_status" class="btn btn-success">
                                    <i class="fas fa-save"></i> Cập nhật Trạng thái
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Chi tiết sản phẩm -->
                    <div class="card full-width">
                        <div class="card-header">
                            <h4><i class="fas fa-list-alt"></i> Chi tiết Sản phẩm</h4>
                        </div>
                        <div class="card-body">
                            <?php if ($order_items->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Sản phẩm</th>
                                                <th>Kích cỡ</th>
                                                <th>Số lượng</th>
                                                <th>Đơn giá</th>
                                                <th>Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $counter = 1;
                                            while($item = $order_items->fetch_assoc()): 
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td>
                                                    <div class="product-info">
                                                        <?php if ($item['image_path']): ?>
                                                            <img src="../<?php echo $item['image_path']; ?>" alt="<?php echo $item['product_name']; ?>" class="product-image">
                                                        <?php endif; ?>
                                                        <div class="product-details">
                                                            <strong><?php echo $item['product_name']; ?></strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $size_names = ['S' => 'Nhỏ', 'M' => 'Vừa', 'L' => 'Lớn'];
                                                    echo $size_names[$item['size']] ?? $item['size'];
                                                    ?>
                                                </td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td class="price"><?php echo number_format($item['unit_price']); ?>đ</td>
                                                <td class="price"><?php echo number_format($item['total_price']); ?>đ</td>
                                            </tr>
                                            <?php endwhile; ?>
                                            <tr class="total-row">
                                                <td colspan="5" class="text-end"><strong>Tổng cộng:</strong></td>
                                                <td class="price total-amount">
                                                    <strong><?php echo number_format($order['total_amount']); ?>đ</strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <p>Không tìm thấy sản phẩm trong đơn hàng này</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .order-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .info-grid {
            display: grid;
            gap: 15px;
        }

        .info-item {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 10px;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-item label {
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .table-number {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .customer-note {
            background: #fff3cd;
            color: #856404;
            padding: 8px 12px;
            border-radius: 8px;
            border-left: 3px solid #ffc107;
            font-style: italic;
        }

        .status {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-preparing {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }

        .product-details {
            flex: 1;
        }

        .total-row {
            background: #f8f9fa;
            font-weight: 600;
        }

        .text-end {
            text-align: right;
        }

        .total-amount {
            font-size: 1.1rem;
            color: #FF7043;
        }

        @media (max-width: 768px) {
            .order-detail-grid {
                grid-template-columns: 1fr;
            }
            
            .info-item {
                grid-template-columns: 1fr;
                gap: 5px;
                text-align: center;
            }

            .product-info {
                flex-direction: column;
                text-align: center;
            }
        }

        @media print {
            .sidebar, .header-actions {
                display: none;
            }
            
            .main-content {
                margin-left: 0;
            }

            .btn {
                display: none;
            }
        }
    </style>
</body>
</html>
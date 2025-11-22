<?php
// admin/sidebar.php - Thanh điều hướng
// KHÔNG có session_start() ở đây vì đã được gọi trong file chính

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Xác định trang hiện tại
$current_page = basename($_SERVER['PHP_SELF']);

// Xác định role và tên user
$user_role = $_SESSION['role'] ?? 'customer';
$username = $_SESSION['username'] ?? 'Khách';
?>

<!-- HTML cho sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>🏪 Đom đóm quán</h3>
        <div class="user-info">
            <div class="user-avatar">
                <?php 
                $avatar_icon = ($user_role === 'admin') ? '👑' : 
                              (($user_role === 'staff') ? '👨‍💼' : '👤');
                echo $avatar_icon;
                ?>
            </div>
            <div class="user-details">
                <strong><?php echo htmlspecialchars($username); ?></strong>
                <span class="user-role">
                    <?php 
                    $role_text = [
                        'admin' => 'Quản trị viên',
                        'staff' => 'Nhân viên', 
                        'customer' => 'Khách hàng'
                    ];
                    echo $role_text[$user_role] ?? 'Khách hàng';
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <ul class="nav-links">
        <!-- Dashboard -->
        <li class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <a href="index.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Bảng điều khiển</span>
            </a>
        </li>
        
        <!-- Quản lý Sản phẩm (Chỉ Admin) -->
        <?php if ($user_role === 'admin'): ?>
        <li class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
            <a href="products.php">
                <i class="fas fa-utensils"></i>
                <span>Quản lý Sản phẩm</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Quản lý Đơn hàng (Admin + Staff) -->
        <?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
        <li class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
            <a href="orders.php">
                <i class="fas fa-shopping-cart"></i>
                <span>Quản lý Đơn hàng</span>
                <?php
                // Hiển thị số đơn hàng chờ xử lý
                require '../config.php';
                $pending_count = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetch_row()[0];
                if ($pending_count > 0): 
                ?>
                <span class="badge pending-count"><?php echo $pending_count; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Quản lý Danh mục (Chỉ Admin) -->
        <?php if ($user_role === 'admin'): ?>
        <li class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
            <a href="categories.php">
                <i class="fas fa-folder"></i>
                <span>Quản lý Danh mục</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Quản lý Người dùng (Chỉ Admin) -->
        <?php if ($user_role === 'admin'): ?>
        <li class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <a href="users.php">
                <i class="fas fa-users"></i>
                <span>Quản lý Người dùng</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Phân cách -->
        <li class="divider"></li>
        
        <!-- Liên kết ra ngoài -->
        <li>
            <a href="../index.html" target="_blank">
                <i class="fas fa-external-link-alt"></i>
                <span>Xem Website</span>
            </a>
        </li>
        
        <li>
            <a href="../menu.html" target="_blank">
                <i class="fas fa-list-alt"></i>
                <span>Xem Menu</span>
            </a>
        </li>
        
        <!-- Đăng xuất -->
        <li class="logout-item">
            <a href="logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Đăng xuất</span>
            </a>
        </li>
    </ul>
    
    <!-- Footer sidebar -->
    <div class="sidebar-footer">
        <div class="system-info">
            <small>Phiên bản 1.0</small>
            <small><?php echo date('Y'); ?> © Đom đóm quán</small>
        </div>
    </div>
</div>

<style>
    /* Sidebar Header */
    .sidebar-header {
        padding: 20px;
        background: rgba(0,0,0,0.2);
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar-header h3 {
        color: #FF7043;
        margin-bottom: 15px;
        font-size: 1.3rem;
        text-align: center;
    }
    
    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .user-avatar {
        font-size: 1.8rem;
        width: 50px;
        height: 50px;
        background: rgba(255,112,67,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .user-details {
        flex: 1;
    }
    
    .user-details strong {
        display: block;
        color: white;
        font-size: 0.9rem;
        margin-bottom: 2px;
    }
    
    .user-role {
        color: #FFCC80;
        font-size: 0.7rem;
        opacity: 0.9;
    }
    
    /* Nav Links với icons */
    .nav-links a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        color: white;
        text-decoration: none;
        transition: all 0.3s;
        border-left: 4px solid transparent;
    }
    
    .nav-links a i {
        width: 20px;
        text-align: center;
        font-size: 1rem;
    }
    
    .nav-links a span {
        flex: 1;
    }
    
    .nav-links a:hover {
        background: rgba(255,112,67,0.3);
        border-left-color: #FF7043;
        padding-left: 25px;
    }
    
    .nav-links li.active a {
        background: rgba(255,112,67,0.5);
        border-left-color: #FF7043;
        font-weight: 600;
    }
    
    /* Badge cho đơn hàng chờ xử lý */
    .badge.pending-count {
        background: #ff6b6b;
        color: white;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        min-width: 18px;
        text-align: center;
    }
    
    /* Divider */
    .divider {
        height: 1px;
        background: rgba(255,255,255,0.2);
        margin: 10px 0;
    }
    
    /* Logout item */
    .logout-item a {
        color: #ff9999 !important;
    }
    
    .logout-item a:hover {
        background: rgba(255,153,153,0.2) !important;
        border-left-color: #ff9999 !important;
    }
    
    /* Sidebar Footer */
    .sidebar-footer {
        margin-top: auto;
        padding: 15px 20px;
        border-top: 1px solid rgba(255,255,255,0.1);
    }
    
    .system-info {
        text-align: center;
    }
    
    .system-info small {
        display: block;
        color: rgba(255,255,255,0.6);
        font-size: 0.7rem;
        margin-bottom: 2px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .sidebar-header {
            padding: 15px;
        }
        
        .user-info {
            flex-direction: column;
            text-align: center;
            gap: 8px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            font-size: 1.5rem;
        }
        
        .nav-links a {
            padding: 12px 15px;
            font-size: 0.9rem;
        }
        
        .nav-links a i {
            font-size: 0.9rem;
        }
    }
</style>

<script>
    // Auto update pending orders count mỗi 30 giây
    function updatePendingCount() {
        fetch('get_pending_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.pending-count');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error updating pending count:', error));
    }
    
    // Cập nhật mỗi 30 giây
    setInterval(updatePendingCount, 30000);
</script>
<?php
// admin/logout.php - Đăng xuất
session_start();

// Xóa tất cả session
session_unset();
session_destroy();

// Xóa localStorage trên client (thông qua JavaScript)
echo "<script>
    localStorage.removeItem('username');
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('user_id');
    window.location.href = '../login.php';
</script>";
exit();
?>
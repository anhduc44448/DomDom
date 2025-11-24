<?php
// config.php - Kết nối database và thiết lập cơ bản
$host = "localhost";
$user = "root";
$pass = "12345678";
$dbname = "domdom_db";

// Kết nối database
$conn = new mysqli($host, $user, $pass, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập múi giờ và encoding
date_default_timezone_set('Asia/Ho_Chi_Minh');
$conn->set_charset("utf8mb4");
?>
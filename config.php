<?php
// config.php - FIXED VERSION
$host = "localhost:3366";
$user = "root";
$pass = "";
$dbname = "domdom1";

// Kết nối database
$conn = new mysqli($host, $user, $pass, $dbname);

// Kiểm tra kết nối - nhưng không die() để frontend vẫn hoạt động
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    // Không die() - để có thể dùng sample data
}

// Chỉ thiết lập charset nếu kết nối thành công
if (!$conn->connect_error) {
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $conn->set_charset("utf8mb4");
}
?>
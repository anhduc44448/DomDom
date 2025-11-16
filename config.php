<?php
$host = "localhost:3366";
$user = "root";
$pass = "";
$dbname = "cuahang";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
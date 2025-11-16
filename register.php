<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    // Kiểm tra xem username hoặc email đã tồn tại chưa
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('Tên đăng nhập hoặc email đã tồn tại!');</script>";
    } else {
        // Thêm tài khoản mới
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, is_premium) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $username, $password, $email);

        if ($stmt->execute()) {
            echo "<script>alert('Đăng ký thành công!'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra khi đăng ký!');</script>";
        }
        $stmt->close();
    }
    
    $check_stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản - Đom đóm quán</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #4E342E;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: rgba(0,0,0,0.7);
            padding: 30px;
            border-radius: 10px;
            width: 320px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        h2 {
            color: #FF7043;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
            border: none;
            box-sizing: border-box;
        }
        input[type=submit] {
            background-color: #FF7043;
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
            margin-top: 15px;
        }
        input[type=submit]:hover {
            background-color: #4E342E;
        }
        .error { 
            color: #ff6666; 
            margin: 10px 0;
        }
        a { 
            color: #FFCC80; 
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        p {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Đăng ký</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Tên đăng nhập" required minlength="3" maxlength="50">
            <input type="password" name="password" placeholder="Mật khẩu" required minlength="6">
            <input type="email" name="email" placeholder="Email" required>
            <input type="submit" value="Tạo tài khoản">
        </form>

        <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
    </div>
</body>
</html>
<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);

    // Kiểm tra username hoặc email đã tồn tại
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error = "Tên đăng nhập hoặc email đã tồn tại!";
    } else {
        // Thêm tài khoản mới
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'customer')");
        $stmt->bind_param("sss", $username, $password, $email);

        if ($stmt->execute()) {
            echo "<script>
                alert('Đăng ký thành công! Vui lòng đăng nhập.');
                window.location.href = 'login.php';
            </script>";
        } else {
            $error = "Có lỗi xảy ra khi đăng ký!";
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
    <title>Đăng ký - Đom đóm quán</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #4E342E, #5D4037);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .register-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }
        
        .register-header {
            background: linear-gradient(135deg, #FF7043, #FF5722);
            padding: 30px;
            text-align: center;
        }
        
        .register-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .register-header p {
            opacity: 0.9;
        }
        
        .register-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #FF7043;
            box-shadow: 0 0 0 3px rgba(255,112,67,0.1);
        }
        
        .btn-register {
            width: 100%;
            background: #FF7043;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        
        .btn-register:hover {
            background: #E64A19;
        }
        
        .register-links {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-links a {
            color: #FF7043;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-links a:hover {
            text-decoration: underline;
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .register-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Đăng Ký Tài Khoản</h1>
            <p>Tham gia cùng Đom đóm quán</p>
        </div>
        
        <div class="register-body">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Tên đăng nhập *</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Tên đăng nhập (tối thiểu 3 ký tự)" required minlength="3">
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu *</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Mật khẩu (tối thiểu 6 ký tự)" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="email@example.com" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="fullname">Họ và tên</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Nguyễn Văn A">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="0901234567">
                    </div>
                </div>
                
                <button type="submit" class="btn-register">Đăng Ký</button>
            </form>
            
            <div class="register-links">
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
            </div>
        </div>
    </div>
</body>
</html>
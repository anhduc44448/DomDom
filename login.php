<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Kiểm tra thông tin đăng nhập
    $stmt = $conn->prepare("SELECT id, username, password, email, role FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Lưu thông tin vào session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        echo "<script>
            localStorage.setItem('username', '" . $user['username'] . "');
            localStorage.setItem('isLoggedIn', 'true');
            localStorage.setItem('user_id', '" . $user['id'] . "');
            localStorage.setItem('user_role', '" . $user['role'] . "');
            
            // Kiểm tra nếu là admin thì chuyển hướng đến admin
            if ('" . $user['role'] . "' === 'admin') {
                alert('Đăng nhập thành công với quyền Admin!');
                window.location.href = 'admin/index.php';
            } else {
                alert('Đăng nhập thành công!');
                window.location.href = 'index.html';
            }
        </script>";
    } else {
        echo "<script>alert('Sai tên đăng nhập hoặc mật khẩu!');</script>";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - Đom đóm quán</title>
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
            height: 100vh;
            margin: 0;
        }
        
        .login-container {
            display: flex;
            width: 900px;
            height: 500px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }
        
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #FF7043, #FF5722);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
            text-align: center;
        }
        
        .login-left h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .login-left p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .brand-logo {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .login-right {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-box h2 {
            color: #4E342E;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2rem;
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
        
        .btn-login {
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
        
        .btn-login:hover {
            background: #E64A19;
        }
        
        .login-links {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-links a {
            color: #FF7043;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-links a:hover {
            text-decoration: underline;
        }
        
        .demo-accounts {
            margin-top: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #FF7043;
        }
        
        .demo-accounts h4 {
            color: #4E342E;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .demo-account {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.8rem;
            color: #666;
        }
        
        .demo-account:last-child {
            margin-bottom: 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                width: 95%;
                height: auto;
            }
            
            .login-left {
                padding: 30px 20px;
            }
            
            .login-right {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="login-left">
            <div class="brand-logo">🏪</div>
            <h1>Đom đóm quán</h1>
            <p>Khơi nguồn cảm hứng từ từng tách cà phê</p>
            <p style="margin-top: 20px; font-size: 0.9rem; opacity: 0.8;">
                Không gian lý tưởng cho CEO và doanh nhân
            </p>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-box">
                <h2>Đăng Nhập</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Tên đăng nhập</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Nhập tên đăng nhập" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                    </div>
                    
                    <button type="submit" class="btn-login">Đăng Nhập</button>
                </form>
                
                <div class="login-links">
                    <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                </div>
                
                <!-- Demo Accounts -->
                <div class="demo-accounts">
                    <h4>👑 Tài khoản demo:</h4>
                    <div class="demo-account">
                        <span>Admin:</span>
                        <span><strong>admin</strong> / <strong>admin123</strong></span>
                    </div>
                    <div class="demo-account">
                        <span>Nhân viên:</span>
                        <span><strong>staff1</strong> / <strong>staff123</strong></span>
                    </div>
                    <div class="demo-account">
                        <span>Khách hàng:</span>
                        <span><strong>customer1</strong> / <strong>password123</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-fill demo accounts for testing
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const demo = urlParams.get('demo');
            
            if (demo === 'admin') {
                document.getElementById('username').value = 'admin';
                document.getElementById('password').value = 'admin123';
            } else if (demo === 'staff') {
                document.getElementById('username').value = 'staff1';
                document.getElementById('password').value = 'staff123';
            } else if (demo === 'customer') {
                document.getElementById('username').value = 'customer1';
                document.getElementById('password').value = 'password123';
            }
        });
    </script>
</body>
</html>
<?php
/**
 * 登录页面
 */

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // 简单的密码验证（实际应用中应该使用数据库或更安全的方式）
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 数据看板</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
        }
        .login-header h3 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        .login-header p {
            color: rgba(255,255,255,0.8);
            margin: 10px 0 0;
        }
        .login-body {
            padding: 30px;
        }
        .login-footer {
            background: #f8f9fa;
            padding: 15px;
            text-align: center;
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h3><i class="bi bi-shield-lock"></i> 后台登录</h3>
            <p>数据看板管理系统</p>
        </div>
        <div class="login-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-person"></i> 用户名
                    </label>
                    <input type="text" class="form-control" name="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-lock"></i> 密码
                    </label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> 登录
                    </button>
                </div>
            </form>
        </div>
        <div class="login-footer">
            默认账号：admin / 密码：admin123
        </div>
    </div>
</body>
</html>

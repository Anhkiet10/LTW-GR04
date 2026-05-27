<?php
session_start();

// Nếu đã đăng nhập thì redirect
if (isset($_SESSION['user_id'])) {
    header('Location: /WEB_GR4/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } else {
        // TODO: Thay bằng logic xác thực DB thực tế
        // Ví dụ:
        // require_once __DIR__ . '/../config/db.php';
        // $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        // $stmt->execute([$username]);
        // $user = $stmt->fetch();
        // if ($user && password_verify($password, $user['password'])) { ... }

        // --- DEMO ---
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['user_id']   = 1;
            $_SESSION['username']  = $username;
            $_SESSION['role']      = 'admin';
            header('Location: /WEB_GR4/admin/home.php');
            exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập · Admin</title>
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/style.css">
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="login-page">

    <!-- Background decoration -->
    <div class="login-bg-circle login-bg-circle--1"></div>
    <div class="login-bg-circle login-bg-circle--2"></div>

    <div class="login-wrapper">

        <!-- CARD -->
        <div class="login-card">

            <!-- Header -->
            <div class="login-header">
                <div class="login-logo-icon">
                    <i class="fas fa-store"></i>
                </div>
                <h1 class="login-title">Đăng nhập</h1>
                <p class="login-subtitle">Chào mừng bạn đến với W4SHOP</p>
            </div>

            <!-- Error message -->
            <?php if ($error): ?>
            <div class="login-alert">
                <i class="fas fa-circle-exclamation"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form class="login-form" method="POST" action="">

                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Tên đăng nhập
                    </label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        placeholder="Nhập tên đăng nhập"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Mật khẩu
                    </label>
                    <div class="input-password-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Nhập mật khẩu"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword()" title="Hiện/ẩn mật khẩu">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="form-checkbox">
                        <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
                    </label>
                    <a href="/WEB_GR4/forgot-password.php" class="form-link">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-right-to-bracket"></i> Đăng nhập
                </button>

            </form>

            <!-- Footer link -->
            <p class="login-footer-text">
                Chưa có tài khoản? <a href="/WEB_GR4/register.php" class="form-link">Đăng ký ngay</a>
            </p>

        </div>

    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

</body>
</html>
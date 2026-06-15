<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - W4SHOP</title>
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/style.css">
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body class="login-page">

    <div class="login-bg-circle login-bg-circle--1"></div>
    <div class="login-bg-circle login-bg-circle--2"></div>

    <div class="login-wrapper">

        <div class="login-card">

            <div class="login-header">
                <div class="login-logo-icon">
                    <i class="fas fa-store"></i>
                </div>
                <h1 class="login-title">Đăng nhập</h1>
                <p class="login-subtitle">Chào mừng bạn đến với W4SHOP</p>
            </div>

            <?php if (!empty($errors['email']) || !empty($errors['password'])): ?>
            <div class="login-alert">
                <i class="fas fa-circle-exclamation"></i>
                <?= htmlspecialchars($errors['email'] ?? $errors['password']) ?>
            </div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="/WEB_GR4/login">

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="Nhập email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        autocomplete="email"
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
                    <a href="/WEB_GR4/forgot-password" class="form-link">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-right-to-bracket"></i> Đăng nhập
                </button>

            </form>

            <p class="login-footer-text">
                Chưa có tài khoản? <a href="/WEB_GR4/register" class="form-link">Đăng ký ngay</a>
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
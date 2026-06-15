<?php

// Helper: giữ giá trị cũ khi có lỗi
function old(string $key, string $default = ''): string {
    return htmlspecialchars($_POST[$key] ?? $default);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký · WEB_GR4</title>
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/style.css">
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/login.css">
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body class="login-page">

    <!-- Background decoration (tái dùng từ login.css) -->
    <div class="login-bg-circle login-bg-circle--1"></div>
    <div class="login-bg-circle login-bg-circle--2"></div>

    <div class="login-wrapper register-wrapper">

        <div class="login-card">

            <!-- Header -->
            <div class="login-header">
                <div class="login-logo-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1 class="login-title">Tạo tài khoản</h1>
            </div>

            <?php if (isset($success) && $success): ?>
            <!-- SUCCESS STATE -->
            <div class="register-success">
                <div class="register-success__icon">
                    <i class="fas fa-circle-check"></i>
                </div>
                <h2>Đăng ký thành công!</h2>
                <p>Tài khoản của bạn đã được tạo. Hãy đăng nhập để tiếp tục.</p>
                <a href="/WEB_GR4/login" class="btn-login" style="display:block; text-align:center; text-decoration:none; margin-top:8px;">
                    <i class="fas fa-right-to-bracket"></i> Đăng nhập ngay
                </a>
            </div>

            <?php else: ?>
            <!-- FORM -->
            <form class="login-form" method="POST" action="/WEB_GR4/register" novalidate id="registerForm">

                <!-- Họ và tên -->
                <div class="form-group <?= isset($errors['fullname']) ? 'has-error' : '' ?>">
                    <label for="fullname" class="form-label">
                        <i class="fas fa-id-card"></i> Họ và tên
                    </label>
                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        class="form-input"
                        placeholder="Nhập họ tên"
                        value="<?= old('fullname') ?>"
                        autocomplete="name"
                    >
                    <?php if (isset($errors['fullname'])): ?>
                        <span class="field-error"><i class="fas fa-triangle-exclamation"></i> <?= $errors['fullname'] ?></span>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="Nhập email"
                        value="<?= old('email') ?>"
                        autocomplete="email"
                    >
                    <?php if (isset($errors['email'])): ?>
                        <span class="field-error"><i class="fas fa-triangle-exclamation"></i> <?= $errors['email'] ?></span>
                    <?php endif; ?>
                </div>

                <!-- Mật khẩu -->
                <div class="form-group <?= isset($errors['password']) ? 'has-error' : '' ?>">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Mật khẩu
                    </label>
                    <div class="input-password-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Ít nhất 6 ký tự"
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('password','icon1')" title="Hiện/ẩn mật khẩu">
                            <i class="fas fa-eye" id="icon1"></i>
                        </button>
                    </div>
                    <!-- Strength bar -->
                    <div class="password-strength" id="strengthBar">
                        <div class="strength-track">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span class="strength-label" id="strengthLabel"></span>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <span class="field-error"><i class="fas fa-triangle-exclamation"></i> <?= $errors['password'] ?></span>
                    <?php endif; ?>
                </div>

                <!-- Xác nhận mật khẩu -->
                <div class="form-group <?= isset($errors['password2']) ? 'has-error' : '' ?>">
                    <label for="password2" class="form-label">
                        <i class="fas fa-lock"></i> Xác nhận mật khẩu
                    </label>
                    <div class="input-password-wrap">
                        <input
                            type="password"
                            id="password2"
                            name="password2"
                            class="form-input"
                            placeholder="Nhập lại mật khẩu"
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('password2','icon2')" title="Hiện/ẩn mật khẩu">
                            <i class="fas fa-eye" id="icon2"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password2'])): ?>
                        <span class="field-error"><i class="fas fa-triangle-exclamation"></i> <?= $errors['password2'] ?></span>
                    <?php endif; ?>
                </div>

                <!-- Đồng ý điều khoản -->
                <div class="form-group agree-group <?= isset($errors['agree']) ? 'has-error' : '' ?>">
                    <?php if (isset($errors['agree'])): ?>
                        <span class="field-error"><i class="fas fa-triangle-exclamation"></i> <?= $errors['agree'] ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-user-plus"></i> Tạo tài khoản
                </button>

            </form>
            <?php endif; ?>

            <!-- Footer -->
            <?php if (!isset($success) || !$success): ?>
            <p class="login-footer-text">
                Đã có tài khoản? <a href="/WEB_GR4/login" class="form-link">Đăng nhập</a>
            </p>
            <?php endif; ?>

        </div>
    </div>

    <script>
        // Toggle hiện/ẩn mật khẩu
        function togglePassword(fieldId, iconId) {
            const input = document.getElementById(fieldId);
            const icon  = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Password strength meter
        const pwInput      = document.getElementById('password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthLabel = document.getElementById('strengthLabel');

        const levels = [
            { label: '',          color: 'transparent', width: '0%'   },
            { label: 'Yếu',       color: '#e05c7b',     width: '25%'  },
            { label: 'Trung bình',color: '#e0a54a',     width: '55%'  },
            { label: 'Khá',       color: '#7bb8d4',     width: '75%'  },
            { label: 'Mạnh',      color: '#7bbda4',     width: '100%' },
        ];

        if (pwInput) {
            pwInput.addEventListener('input', function () {
                const val = this.value;
                let score = 0;
                if (val.length >= 6)                      score++;
                if (val.length >= 10)                     score++;
                if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
                if (/[0-9]/.test(val))                    score++;
                if (/[^a-zA-Z0-9]/.test(val))            score++;
                const level = Math.min(score, 4);
                strengthFill.style.width      = val.length ? levels[Math.max(level,1)].width : '0%';
                strengthFill.style.background = levels[Math.max(level,1)].color;
                strengthLabel.textContent     = val.length ? levels[Math.max(level,1)].label : '';
                strengthLabel.style.color     = levels[Math.max(level,1)].color;
            });
        }

        // Realtime match check
        const pw2Input = document.getElementById('password2');
        if (pw2Input) {
            pw2Input.addEventListener('input', function () {
                const parent = this.closest('.form-group');
                const match  = this.value === pwInput.value;
                parent.classList.toggle('has-error', this.value.length > 0 && !match);
                parent.classList.toggle('is-valid',  this.value.length > 0 && match);
            });
        }
    </script>

</body>
</html>
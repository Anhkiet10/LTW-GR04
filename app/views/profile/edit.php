<?php
// app/views/profile/edit.php
include dirname(__DIR__) . '/layouts/header.php';
?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/profileEdit.css">

<div class="profile-page">
    <div class="profile-container">
        <div class="profile-grid">

            <!-- Sidebar -->
            <div class="profile-card profile-sidebar">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                </div>
                <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>
                <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
                <div class="profile-role-wrap">
                    <span class="profile-role profile-role--<?= $user['role'] ?>">
                        <?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng' ?>
                    </span>
                </div>

            </div>

            <!-- Form -->
            <div class="profile-content">

                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="profile-section">
                    <div class="profile-section-header">
                        <h2 class="profile-section-title">Chỉnh sửa thông tin</h2>
                    </div>

                    <div class="profile-section-body">
                        <form method="POST" action="/WEB_GR4/profile/update">

                            <div class="form-group">
                                <label for="full_name">Họ và tên <span class="required">*</span></label>
                                <input type="text" id="full_name" name="full_name"
                                       value="<?= htmlspecialchars($user['full_name']) ?>"
                                       required maxlength="100">
                            </div>

                            <div class="form-group">
                                <label for="email">Email <span class="required">*</span></label>
                                <input type="email" id="email" name="email"
                                       value="<?= htmlspecialchars($user['email']) ?>"
                                       required maxlength="150">
                            </div>

                            <div class="form-group">
                                <label for="phone">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone"
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                       maxlength="20" placeholder="Để trống nếu không muốn thay đổi">
                            </div>

                            <hr class="form-divider">
                            <p class="form-note">Để trống nếu không muốn đổi mật khẩu</p>

                            <div class="form-group">
                                <label for="password">Mật khẩu mới</label>
                                <input type="password" id="password" name="password"
                                       minlength="6" autocomplete="new-password"
                                       placeholder="Tối thiểu 6 ký tự">
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                                <input type="password" id="confirm_password" name="confirm_password"
                                       autocomplete="new-password"
                                       placeholder="Nhập lại mật khẩu mới">
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="profile-btn profile-btn--primary">Lưu thay đổi</button>
                                <a href="/WEB_GR4/profile" class="profile-btn profile-btn--secondary">Hủy</a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script src="/WEB_GR4/public/assets/js/user/profile_edit.js"></script>
<?php
// app/views/profile/add-address.php
include dirname(__DIR__) . '/layouts/header.php';
?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/profileIndex.css">

<div class="profile-page">
    <div class="profile-container">

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="profile-section" style="max-width:600px;margin:0 auto">
            <div class="profile-section-header">
                <h2 class="profile-section-title">Thêm địa chỉ mới</h2>
            </div>

            <div class="profile-section-body">
                <form method="POST" action="/WEB_GR4/profile/store-address">

                    <div class="form-group">
                        <label for="label">Nhãn địa chỉ</label>
                        <input type="text" id="label" name="label"
                               placeholder="Ví dụ: Nhà, Công ty, Trường học"
                               maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="full_address">Địa chỉ chi tiết <span class="required">*</span></label>
                        <input type="text" id="full_address" name="full_address"
                               placeholder="Số nhà, đường, phường/xã, quận/huyện"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="city">Tỉnh / Thành phố <span class="required">*</span></label>
                        <input type="text" id="city" name="city"
                               placeholder="Ví dụ: Hồ Chí Minh, Hà Nội"
                               required maxlength="100">
                    </div>

                    <div class="form-group form-group--checkbox">
                        <label>
                            <input type="checkbox" name="is_default" value="1">
                            Đặt làm địa chỉ mặc định
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="profile-btn profile-btn--primary">Thêm địa chỉ</button>
                        <a href="/WEB_GR4/profile" class="profile-btn profile-btn--secondary">Hủy</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<script src="/WEB_GR4/public/assets/js/user/address_form.js"></script>
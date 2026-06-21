<?php
// app/views/profile/index.php
include dirname(__DIR__) . '/layouts/header.php';
?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/profileIndex.css">

<div class="profile-page">
    <div class="profile-container">
        <div class="profile-grid">

            <div class="profile-card profile-sidebar">

                <div class="profile-avatar">
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                </div>

                <div class="profile-name">
                    <?= htmlspecialchars($user['full_name']) ?>
                </div>

                <div class="profile-email">
                    <?= htmlspecialchars($user['email']) ?>
                </div>

                <div class="profile-role-wrap">
                    <span class="profile-role profile-role--<?= $user['role'] ?>">
                        <?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng' ?>
                    </span>
                </div>

                <div class="profile-menu">
                    <a href="/WEB_GR4/orders">Lịch sử mua hàng</a>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="/admin">Trang quản trị</a>
                    <?php endif; ?>
                    <a class="logout" href="/WEB_GR4/logout">Đăng xuất</a>
                </div>

            </div>

            <div class="profile-content">

                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="profile-section">
                    <div class="profile-section-header">
                        <h2 class="profile-section-title">Thông tin cá nhân</h2>
                        <a href="/WEB_GR4/profile/edit" class="profile-btn">Chỉnh sửa</a>
                    </div>

                    <div class="profile-section-body">

                        <div class="profile-info-row">
                            <div class="profile-label">Họ và tên</div>
                            <div class="profile-value"><?= htmlspecialchars($user['full_name']) ?></div>
                        </div>

                        <div class="profile-info-row">
                            <div class="profile-label">Email</div>
                            <div class="profile-value"><?= htmlspecialchars($user['email']) ?></div>
                        </div>

                        <div class="profile-info-row">
                            <div class="profile-label">Số điện thoại</div>
                            <div class="profile-value">
                                <?= $user['phone'] ? htmlspecialchars($user['phone']) : '<em style="color:#aaa">Chưa cập nhật</em>' ?>
                            </div>
                        </div>

                        <div class="profile-info-row">
                            <div class="profile-label">Vai trò</div>
                            <div class="profile-value">
                                <?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng' ?>
                            </div>
                        </div>

                        <div class="profile-info-row">
                            <div class="profile-label">Ngày tham gia</div>
                            <div class="profile-value">
                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            </div>
                        </div>

                    </div>
                </div>

                <?php if ($user['role'] === 'customer'): ?>
                <div class="profile-section">
                    <div class="profile-section-header">
                        <h2 class="profile-section-title">Sổ địa chỉ nhận hàng</h2>
                        <a href="/WEB_GR4/profile/addAddress" class="profile-btn">+ Thêm địa chỉ</a>
                    </div>

                    <div class="profile-section-body">

                        <?php if (empty($addresses)): ?>
                            <p class="profile-empty">Bạn chưa có địa chỉ nào. <a href="/WEB_GR4/profile/addAddress">Thêm ngay</a></p>
                        <?php else: ?>
                            <?php foreach ($addresses as $addr): ?>
                            <div class="address-card <?= $addr['is_default'] ? 'default' : '' ?>">

                                <div class="address-info">
                                    <div class="address-name">
                                        <?= htmlspecialchars($addr['label'] ?: 'Địa chỉ') ?>
                                        <?php if ($addr['is_default']): ?>
                                            <span class="address-default">Mặc định</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="address-text">
                                        <?= htmlspecialchars($addr['full_address']) ?>,
                                        <?= htmlspecialchars($addr['city']) ?>
                                    </div>
                                </div>

                                <div class="address-actions">
                                    <a href="/WEB_GR4/profile/editAddress/<?= $addr['address_id'] ?>"
                                       class="addr-btn addr-btn--edit">Sửa</a>

                                    <?php if (!$addr['is_default']): ?>
                                    <form method="POST" action="/WEB_GR4/profile/set-default-address" style="display:inline">
                                        <input type="hidden" name="address_id" value="<?= $addr['address_id'] ?>">
                                        <button type="submit" class="addr-btn addr-btn--default">Đặt mặc định</button>
                                    </form>
                                    <?php endif; ?>

                                    <form method="POST" action="/WEB_GR4/profile/delete-address" style="display:inline" class="form-delete-address">
                                        <input type="hidden" name="address_id" value="<?= $addr['address_id'] ?>">
                                        <button type="submit" class="addr-btn addr-btn--delete">Xóa</button>
                                    </form>
                                    
                                </div>

                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<div id="custom-confirm-modal" class="custom-modal" style="display: none;">
    <div class="custom-modal-content">
        <h3 style="margin-top: 0;">Xác nhận xóa</h3>
        <p>Bạn có chắc chắn muốn xóa địa chỉ này không?</p>
        <div class="custom-modal-actions">
            <button type="button" id="custom-confirm-cancel" class="addr-btn addr-btn--edit">Hủy</button>
            <button type="button" id="custom-confirm-ok" class="addr-btn addr-btn--delete">Xóa</button>
        </div>
    </div>
</div>

<script src="/WEB_GR4/public/assets/js/user/profile_index.js"></script>
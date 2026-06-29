<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/GuestCheckout.css">

<div class="gc-container">

    <div class="gc-steps">
        <div class="gc-step active" id="step-indicator-1">
            <span class="gc-step-num">1</span>
            <span class="gc-step-label">Thông tin</span>
        </div>
        <div class="gc-step-line"></div>
        <div class="gc-step" id="step-indicator-2">
            <span class="gc-step-num">2</span>
            <span class="gc-step-label">Thanh toán</span>
        </div>
    </div>

    <!-- ======================== BƯỚC 1: Thông tin + Tóm tắt đơn ======================== -->
    <div class="gc-step-panel" id="panel-step-1">

        <div class="gc-layout">

            <!-- Cột trái: Form thông tin -->
            <div class="gc-form-col">
                <h2 class="gc-section-title">
                    <i class="fa-solid fa-user"></i> Thông tin nhận hàng
                </h2>

                <?php if (!empty($errors)): ?>
                    <div class="gc-alert gc-alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <ul>
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="guestInfoForm" novalidate>
                    <!-- Token CSRF -->
                    <input type="hidden" name="csrf_token"
                           value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                    <!-- Dữ liệu sản phẩm được mã hóa JSON để JS gửi lên server -->
                    <input type="hidden" id="cartDataInput"
                           value="<?php echo htmlspecialchars($cartJson ?? ''); ?>">

                    <div class="gc-field">
                        <label for="gc_name">Họ và tên <span class="gc-required">*</span></label>
                        <input type="text" id="gc_name" name="guest_name"
                               placeholder="Nguyễn Văn A"
                               value="<?php echo htmlspecialchars($old['guest_name'] ?? ''); ?>"
                               required>
                        <span class="gc-field-error" id="err-name"></span>
                    </div>

                    <div class="gc-field">
                        <label for="gc_phone">Số điện thoại <span class="gc-required">*</span></label>
                        <input type="tel" id="gc_phone" name="guest_phone"
                               placeholder="0901234567"
                               value="<?php echo htmlspecialchars($old['guest_phone'] ?? ''); ?>"
                               required>
                        <span class="gc-field-error" id="err-phone"></span>
                    </div>

                    <div class="gc-field">
                        <label for="gc_email">Email <small>(để nhận xác nhận đơn)</small></label>
                        <input type="email" id="gc_email" name="guest_email"
                               placeholder="example@email.com"
                               value="<?php echo htmlspecialchars($old['guest_email'] ?? ''); ?>">
                        <span class="gc-field-error" id="err-email"></span>
                    </div>

                    <div class="gc-field">
                        <label for="gc_city">Tỉnh / Thành phố <span class="gc-required">*</span></label>
                        <input type="text" id="gc_city" name="guest_city"
                               placeholder="Hồ Chí Minh"
                               value="<?php echo htmlspecialchars($old['guest_city'] ?? ''); ?>"
                               required>
                        <span class="gc-field-error" id="err-city"></span>
                    </div>

                    <div class="gc-field">
                        <label for="gc_address">Địa chỉ cụ thể <span class="gc-required">*</span></label>
                        <input type="text" id="gc_address" name="guest_address"
                               placeholder="Số nhà, tên đường, phường/xã, quận/huyện"
                               value="<?php echo htmlspecialchars($old['guest_address'] ?? ''); ?>"
                               required>
                        <span class="gc-field-error" id="err-address"></span>
                    </div>

                    <div class="gc-field">
                        <label for="gc_note">Ghi chú đơn hàng</label>
                        <textarea id="gc_note" name="note" rows="3"
                                  placeholder="Giao giờ hành chính, gọi trước khi giao, ..."></textarea>
                    </div>

                    <div class="gc-login-hint">
                        <i class="fa-solid fa-circle-info"></i>
                        Bạn đã có tài khoản?
                        <a href="/WEB_GR4/login?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">
                            Đăng nhập
                        </a>
                        để quản lý đơn hàng dễ hơn.
                    </div>

                    <button type="submit" class="gc-btn-next" id="btnNextStep">
                        Tiếp tục <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>

            <!-- Cột phải: Tóm tắt đơn -->
            <div class="gc-summary-col">
                <h2 class="gc-section-title">
                    <i class="fa-solid fa-bag-shopping"></i> Đơn hàng của bạn
                </h2>

                <div class="gc-item-list">
                    <?php foreach ($items as $item): ?>
                        <?php $sub = $item['price_snapshot'] * $item['quantity']; ?>
                        <div class="gc-item">
                            <div class="gc-item-img">
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="/WEB_GR4/public<?php echo htmlspecialchars($item['image_url']); ?>"
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <?php else: ?>
                                    <div class="gc-no-img">
                                        <i class="fa-solid fa-box-open"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="gc-item-qty"><?php echo $item['quantity']; ?></span>
                            </div>
                            <div class="gc-item-info">
                                <p class="gc-item-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                <?php if (!empty($item['variant_label']) || (!empty($item['variant_key']) && $item['variant_key'] !== 'default')): ?>
                                    <p class="gc-item-variant">
                                        <?php echo htmlspecialchars(!empty($item['variant_label']) ? $item['variant_label'] : $item['variant_key']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="gc-item-price">
                                <?php echo number_format($sub, 0, ',', '.'); ?>đ
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="gc-divider"></div>

                <div class="gc-summary-row">
                    <span>Tạm tính</span>
                    <span><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="gc-summary-row">
                    <span>Phí vận chuyển</span>
                    <span class="gc-free">Miễn phí</span>
                </div>

                <div class="gc-divider"></div>

                <div class="gc-summary-row gc-total">
                    <span>Tổng cộng</span>
                    <strong><?php echo number_format($total, 0, ',', '.'); ?>đ</strong>
                </div>
            </div>

        </div><!-- /.gc-layout -->
    </div><!-- /#panel-step-1 -->


    <!-- ======================== BƯỚC 2: Chọn thanh toán ======================== -->
    <div class="gc-step-panel hidden" id="panel-step-2"
         data-total="<?php echo (int)$total; ?>">

        <div class="gc-layout">

            <!-- Cột trái: Phương thức thanh toán -->
            <div class="gc-form-col">
                <h2 class="gc-section-title">
                    <i class="fa-solid fa-wallet"></i> Phương thức thanh toán
                </h2>

                <div class="gc-method-tabs">
                    <button class="gc-method-tab active" data-method="cod">
                        <i class="fa-solid fa-truck"></i> Thanh toán khi nhận hàng (COD)
                    </button>
                    <button class="gc-method-tab" data-method="bank_transfer">
                        <i class="fa-solid fa-qrcode"></i> Chuyển khoản / QR
                    </button>
                </div>

                <!-- Panel COD -->
                <div class="gc-method-panel" id="gc-panel-cod">
                    <div class="gc-cod-info">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>
                            <h3>Thanh toán khi nhận hàng</h3>
                            <p>Bạn sẽ thanh toán
                                <strong><?php echo number_format($total, 0, ',', '.'); ?>đ</strong>
                                khi nhân viên giao hàng đến nơi.
                            </p>
                        </div>
                    </div>

                    <button class="gc-btn-confirm" id="btnConfirmCOD">
                        <i class="fa-solid fa-check"></i> Xác nhận đặt hàng COD
                    </button>
                </div>

                <!-- Panel Chuyển khoản -->
                <div class="gc-method-panel hidden" id="gc-panel-bank_transfer">
                    <p class="gc-method-desc">
                        Đặt hàng xong, dùng QR để chuyển khoản. Đơn sẽ được xác nhận sau khi
                        chúng tôi nhận được thanh toán.
                    </p>

                    <div class="gc-qr-wrapper">
                        <img src="https://img.vietqr.io/image/MB-0973469734-print.png?amount=<?php echo (int)$total; ?>&addInfo=DATHANGGUESTORDER"
                             alt="QR thanh toán" class="gc-qr-image" id="gcQrImage">
                        <div class="gc-qr-info">
                            <div class="gc-qr-row">
                                <span class="gc-qr-label">Ngân hàng</span>
                                <span class="gc-qr-value">MB Bank</span>
                            </div>
                            <div class="gc-qr-row">
                                <span class="gc-qr-label">Số tài khoản</span>
                                <span class="gc-qr-value">0973469734</span>
                            </div>
                            <div class="gc-qr-row">
                                <span class="gc-qr-label">Số tiền</span>
                                <span class="gc-qr-value gc-highlight">
                                    <?php echo number_format($total, 0, ',', '.'); ?>đ
                                </span>
                            </div>
                            <div class="gc-qr-row">
                                <span class="gc-qr-label">Nội dung CK</span>
                                <span class="gc-qr-value gc-highlight" id="gcQrNote">
                                    Sẽ hiện sau khi đặt hàng
                                </span>
                            </div>
                        </div>
                    </div>

                    <button class="gc-btn-confirm" id="btnConfirmQR">
                        <i class="fa-solid fa-qrcode"></i> Xác nhận đã Chuyển khoản
                    </button>
                </div>

                <button class="gc-btn-back" id="btnBackStep">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </button>
            </div>

            <!-- Cột phải: Xác nhận thông tin giao hàng (readonly) -->
            <div class="gc-summary-col">
                <h2 class="gc-section-title">
                    <i class="fa-solid fa-location-dot"></i> Địa chỉ giao hàng
                </h2>

                <div class="gc-address-confirm" id="gcAddressConfirm">
                    <!-- Điền bằng JS từ bước 1 -->
                </div>

                <div class="gc-divider"></div>

                <div class="gc-summary-row gc-total">
                    <span>Tổng cộng</span>
                    <strong><?php echo number_format($total, 0, ',', '.'); ?>đ</strong>
                </div>

                <a href="/WEB_GR4/products" class="gc-btn-cancel">
                    <i class="fa-solid fa-xmark"></i> Hủy đơn hàng
                </a>
            </div>

        </div>
    </div><!-- /#panel-step-2 -->


    <!-- ======================== BƯỚC 3: Thành công ======================== -->
    <div class="gc-step-panel hidden" id="panel-success">
        <div class="gc-success">
            <div class="gc-success-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h2>Đặt hàng thành công!</h2>
            <p id="gcSuccessMsg">Cảm ơn bạn đã mua hàng.</p>

            <div class="gc-success-detail" id="gcSuccessDetail">
                <!-- Điền bằng JS sau khi server trả về order_id -->
            </div>

            <div class="gc-success-actions">
                <a href="/WEB_GR4/products" class="gc-btn-next">
                    <i class="fa-solid fa-store"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>

</div><!-- /.gc-container -->

<script>
    // Truyền biến PHP sang JS
    const GC_TOTAL = <?php echo (int)$total; ?>;
    const GC_ITEMS = <?php echo $cartJson ?? '[]'; ?>;
</script>
<script src="/WEB_GR4/public/assets/js/user/guest_checkout1.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
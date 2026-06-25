<?php
// app/views/admin/UserForm.php
// Nhận: $user (array|null), $errors (array), $mode ('create'|'edit')

$isEdit  = $mode === 'edit';
$title   = $isEdit ? 'Chỉnh sửa người dùng' : 'Thêm người dùng mới';
$action  = $isEdit ? '/WEB_GR4/admin/users/update'   : '/WEB_GR4/admin/users/store';

// Giá trị hiện tại (ưu tiên dữ liệu POST bị lỗi, rồi mới đến DB)
$val = fn(string $k, string $default = '') => htmlspecialchars($user[$k] ?? $default);
?>

<?php include __DIR__ . '/../layouts/admin_sidebar.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/UserForm.css">
<div class="admin-content">

  <!-- ── Header ────────────────────────────────────────────── -->
  <div class="page-header">
    <div>
      <a href="/WEB_GR4/admin/users" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại</a>
      <h1 class="page-title"><?= $title ?></h1>
    </div>
  </div>

  <!-- ── Form ───────────────────────────────────────────────── -->
  <div class="form-card">
    <form method="POST" action="<?= $action ?>">
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $val('user_id') ?>">
      <?php endif; ?>

      <div class="form-grid">

        <!-- Họ tên -->
        <div class="form-group <?= !empty($errors['full_name']) ? 'has-error' : '' ?>">
          <label for="full_name">Họ tên <span class="required">*</span></label>
          <input
            type="text"
            id="full_name"
            name="full_name"
            value="<?= $val('full_name') ?>"
            class="form-control"
            placeholder="Nguyễn Văn A"
            autofocus
          >
          <?php if (!empty($errors['full_name'])): ?>
            <span class="error-msg"><?= $errors['full_name'] ?></span>
          <?php endif; ?>
        </div>

        <!-- Email -->
        <div class="form-group <?= !empty($errors['email']) ? 'has-error' : '' ?>">
          <label for="email">Email <span class="required">*</span></label>
          <input
            type="email"
            id="email"
            name="email"
            value="<?= $val('email') ?>"
            class="form-control"
            placeholder="example@email.com"
          >
          <?php if (!empty($errors['email'])): ?>
            <span class="error-msg"><?= $errors['email'] ?></span>
          <?php endif; ?>
        </div>

        <!-- Số điện thoại -->
        <div class="form-group <?= !empty($errors['phone']) ? 'has-error' : '' ?>">
          <label for="phone">Số điện thoại</label>
          <input
            type="text"
            id="phone"
            name="phone"
            value="<?= $val('phone') ?>"
            class="form-control"
            placeholder="0901234567"
          >
          <?php if (!empty($errors['phone'])): ?>
            <span class="error-msg"><?= $errors['phone'] ?></span>
          <?php endif; ?>
        </div>

        <!-- Vai trò -->
        <div class="form-group">
          <label for="role">Vai trò <span class="required">*</span></label>
          <select id="role" name="role" class="form-control">
            <option value="customer" <?= ($user['role'] ?? 'customer') === 'customer' ? 'selected' : '' ?>>Khách hàng</option>
            <option value="admin"    <?= ($user['role'] ?? '')         === 'admin'    ? 'selected' : '' ?>>Admin</option>
          </select>
        </div>

        <!-- Mật khẩu -->
        <div class="form-group <?= !empty($errors['password']) ? 'has-error' : '' ?>">
          <label for="password">
            Mật khẩu
            <?php if (!$isEdit): ?>
              <span class="required">*</span>
            <?php else: ?>
              <small class="text-muted">(Để trống nếu không đổi)</small>
            <?php endif; ?>
          </label>
          <div class="input-icon-wrap">
            <input
              type="password"
              id="password"
              name="password"
              class="form-control"
              placeholder="<?= $isEdit ? 'Nhập mật khẩu mới nếu muốn đổi' : 'Tối thiểu 6 ký tự' ?>"
              autocomplete="new-password"
            >
            <button type="button" class="toggle-password" aria-label="Hiện/ẩn mật khẩu">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
          </div>
          <?php if (!empty($errors['password'])): ?>
            <span class="error-msg"><?= $errors['password'] ?></span>
          <?php endif; ?>
        </div>

      </div><!-- /.form-grid -->

      <div class="form-actions">
        <a href="/WEB_GR4/admin/users" class="btn btn-ghost">Huỷ</a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i>
          <?= $isEdit ? 'Lưu thay đổi' : 'Tạo người dùng' ?>
        </button>
      </div>
    </form>
  </div>

</div>

<script>
// Toggle show / hide password
const pwdInput = document.getElementById('password');
const eyeIcon  = document.getElementById('eyeIcon');

document.querySelector('.toggle-password').addEventListener('click', () => {
  const show = pwdInput.type === 'password';
  pwdInput.type      = show ? 'text' : 'password';
  eyeIcon.className  = show ? 'fas fa-eye-slash' : 'fas fa-eye';
});
</script>
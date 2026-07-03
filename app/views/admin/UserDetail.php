<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php include __DIR__ . '/../layouts/admin_sidebar.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/UserDetail.css">
<div class="admin-content">
  <div class="page-header">
    <div>
      <h1 class="page-title">Chi tiết người dùng</h1>
      <p class="page-sub">
        <a href="/WEB_GR4/admin/users" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
      </p>
    </div>
    <div style="display:flex;gap:8px">
      <a href="/WEB_GR4/admin/users/edit?id=<?= $user['user_id'] ?>" class="btn btn-primary">
        <i class="fas fa-edit"></i> Chỉnh sửa
      </a>
      <?php if ($user['user_id'] !== (int)$_SESSION['user_id']): ?>
        <button class="btn btn-danger delete-btn"
          data-id="<?= $user['user_id'] ?>"
          data-name="<?= htmlspecialchars($user['full_name']) ?>">
          <i class="fas fa-trash"></i> Xóa
        </button>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible">
      <?= htmlspecialchars($flash['message']) ?>
      <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
  <?php endif; ?>

  <div class="detail-grid">

    <div class="table-card detail-card">
      <div class="detail-card-header">
        <i class="fas fa-user"></i> Thông tin tài khoản
      </div>

      <div class="detail-avatar">
        <div class="avatar-circle">
          <?= mb_strtoupper(mb_substr($user['full_name'], 0, 1)) ?>
        </div>
        <div>
          <div class="detail-name"><?= htmlspecialchars($user['full_name']) ?></div>
          <span class="badge badge-<?= $user['role'] === 'admin' ? 'warning' : 'info' ?>">
            <?= $user['role'] === 'admin' ? 'Admin' : 'Khách hàng' ?>
          </span>
        </div>
      </div>

      <table class="info-table">
        <tr>
          <td class="info-label"><i class="fas fa-hashtag"></i> ID</td>
          <td><?= $user['user_id'] ?></td>
        </tr>
        <tr>
          <td class="info-label"><i class="fas fa-envelope"></i> Email</td>
          <td><?= htmlspecialchars($user['email']) ?></td>
        </tr>
        <tr>
          <td class="info-label"><i class="fas fa-phone"></i> Điện thoại</td>
          <td><?= htmlspecialchars($user['phone'] ?? '—') ?></td>
        </tr>
        <tr>
          <td class="info-label"><i class="fas fa-calendar-alt"></i> Ngày tạo</td>
          <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
        </tr>
      </table>
    </div>

    <div class="detail-card">

      <div class="table-card stat-card">
        <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-value"><?= $orderCount ?></div>
        <div class="stat-label">Đơn hàng</div>
        <?php if ($orderCount > 0): ?>
          <a href="/WEB_GR4/admin/orders?user_id=<?= $user['user_id'] ?>" class="btn btn-ghost btn-sm" style="margin-top:10px">
            Xem đơn hàng <i class="fas fa-arrow-right"></i>
          </a>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<div id="deleteModal" class="modal" style="display:none">
  <div class="modal-box">
    <h3>Xác nhận xóa</h3>
    <p>Bạn có chắc muốn xóa người dùng <strong id="deleteUserName"></strong>?<br>
       Hành động này không thể hoàn tác.</p>
    <div class="modal-actions">
      <button id="cancelDelete" class="btn btn-ghost">Huỷ</button>
      <form id="deleteForm" method="POST" action="/WEB_GR4/admin/users/delete" style="display:inline">
        <input type="hidden" name="id" id="deleteUserId">
        <button type="submit" class="btn btn-danger">Xóa</button>
      </form>
    </div>
  </div>
</div>
<script src="/WEB_GR4/public/assets/js/admin/user_detail.js"></script>
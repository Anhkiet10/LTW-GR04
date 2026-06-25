<?php
// app/views/admin/UserDetail.php
// Nhận: $user, $orderCount

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php include __DIR__ . '/../layouts/admin_sidebar.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/UserDetail.css">
<div class="admin-content">

  <!-- ── Header ─────────────────────────────────────────── -->
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

  <!-- ── Flash ───────────────────────────────────────────── -->
  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible">
      <?= htmlspecialchars($flash['message']) ?>
      <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
  <?php endif; ?>

  <div class="detail-grid">

    <!-- ── Thông tin cơ bản ────────────────────────────── -->
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

    <!-- ── Thống kê ─────────────────────────────────────── -->
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

<!-- ── Modal xác nhận xoá ────────────────────────────────── -->
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

<style>
.detail-grid {
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 20px;
  align-items: start;
}
@media (max-width: 768px) {
  .detail-grid { grid-template-columns: 1fr; }
}

.detail-card-header {
  font-weight: 600;
  font-size: 15px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-color, #e5e7eb);
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-primary, #111827);
}

.detail-avatar {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  border-bottom: 1px solid var(--border-color, #e5e7eb);
}

.avatar-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--primary, #6366f1);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  font-weight: 700;
  flex-shrink: 0;
}

.detail-name {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 6px;
  color: var(--text-primary, #111827);
}

.info-table {
  width: 100%;
  border-collapse: collapse;
}
.info-table tr td {
  padding: 14px 20px;
  border-bottom: 1px solid var(--border-color, #f3f4f6);
  font-size: 14px;
  color: var(--text-primary, #374151);
}
.info-table tr:last-child td { border-bottom: none; }
.info-label {
  color: var(--text-secondary, #6b7280) !important;
  width: 160px;
  font-weight: 500;
  display: flex;
  gap: 8px;
  align-items: center;
}

.stat-card {
  text-align: center;
  padding: 28px 20px !important;
}
.stat-icon { font-size: 28px; color: var(--primary, #6366f1); margin-bottom: 8px; }
.stat-value { font-size: 36px; font-weight: 700; color: var(--text-primary, #111827); line-height: 1; }
.stat-label { font-size: 13px; color: var(--text-secondary, #6b7280); margin-top: 4px; }

.back-link { color: var(--text-secondary, #6b7280); font-size: 14px; text-decoration: none; }
.back-link:hover { color: var(--primary, #6366f1); }

.btn-sm { padding: 6px 12px; font-size: 13px; }
</style>

<script>
// ── Xoá user ───────────────────────────────────────────────
const modal = document.getElementById('deleteModal');

document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('deleteUserId').value        = btn.dataset.id;
    document.getElementById('deleteUserName').textContent = btn.dataset.name;
    modal.style.display = 'flex';
  });
});

document.getElementById('cancelDelete').addEventListener('click', () => {
  modal.style.display = 'none';
});

modal.addEventListener('click', e => {
  if (e.target === modal) modal.style.display = 'none';
});
</script>
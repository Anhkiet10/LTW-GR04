<?php
// app/views/admin/User.php
// Nhận: $users, $total, $page, $totalPages, $filters

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php include __DIR__ . '/../layouts/admin_sidebar.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/User.css">
<div class="admin-content">

  <!-- ── Header ─────────────────────────────────────────── -->
  <div class="page-header">
    <div>
      <h1 class="page-title">Quản lý người dùng</h1>
      <p class="page-sub">Tổng cộng <strong><?= $total ?></strong> người dùng</p>
    </div>
    <a href="/WEB_GR4/admin/users/create" class="btn btn-primary">
      <i class="fas fa-plus"></i> Thêm người dùng
    </a>
  </div>

  <!-- ── Flash ───────────────────────────────────────────── -->
  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible">
      <?= htmlspecialchars($flash['message']) ?>
      <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
  <?php endif; ?>

  <!-- ── Bộ lọc ──────────────────────────────────────────── -->
  <div class="filter-card">
    <form method="GET" action="/WEB_GR4/admin/users" class="filter-form">
      <input
        type="text"
        name="search"
        placeholder="Tìm tên, email, số điện thoại…"
        value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
        class="form-control"
      >

      <select name="role" class="form-control">
        <option value="">Tất cả vai trò</option>
        <option value="customer" <?= ($filters['role'] ?? '') === 'customer' ? 'selected' : '' ?>>Khách hàng</option>
        <option value="admin"    <?= ($filters['role'] ?? '') === 'admin'    ? 'selected' : '' ?>>Admin</option>
      </select>

      <button type="submit" class="btn btn-secondary">
        <i class="fas fa-search"></i> Tìm kiếm
      </button>
      <a href="/WEB_GR4/admin/users" class="btn btn-ghost">load lại trang</a>
    </form>
  </div>

  <!-- ── Bảng danh sách ──────────────────────────────────── -->
  <div class="table-card">
    <?php if (empty($users)): ?>
      <div class="empty-state">
        <i class="fas fa-users fa-3x"></i>
        <p>Không tìm thấy người dùng nào.</p>
      </div>
    <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Điện thoại</th>
            <th>Vai trò</th>
            <th>Ngày tạo</th>
            <th class="text-center">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $i => $user): ?>
            <tr>
              <td><?= ($page - 1) * 10 + $i + 1 ?></td>
              <td>
                <a href="/WEB_GR4/admin/users/detail?id=<?= $user['user_id'] ?>" class="user-name-link">
                  <?= htmlspecialchars($user['full_name']) ?>
                </a>
              </td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><?= htmlspecialchars($user['phone'] ?? '—') ?></td>
              <td>
                <span class="badge badge-<?= $user['role'] === 'admin' ? 'warning' : 'info' ?>">
                  <?= $user['role'] === 'admin' ? 'Admin' : 'Khách hàng' ?>
                </span>
              </td>
              <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
              <td class="action-cell">
                <a href="/WEB_GR4/admin/users/edit?id=<?= $user['user_id'] ?>" class="btn-icon btn-edit" title="Sửa">
                  <i class="fas fa-edit"></i>
                </a>
                <button
                  class="btn-icon btn-delete delete-btn"
                  data-id="<?= $user['user_id'] ?>"
                  data-name="<?= htmlspecialchars($user['full_name']) ?>"
                  title="Xóa"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- ── Phân trang ──────────────────────────────────── -->
      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php
          $qs = http_build_query(array_filter([
            'search' => $filters['search'] ?? '',
            'role'   => $filters['role']   ?? '',
          ]));
          ?>

          <?php if ($page > 1): ?>
            <a href="/WEB_GR4/admin/users?page=<?= $page - 1 ?>&<?= $qs ?>" class="page-btn">‹ Trước</a>
          <?php endif; ?>

          <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
            <a
              href="/WEB_GR4/admin/users?page=<?= $p ?>&<?= $qs ?>"
              class="page-btn <?= $p === $page ? 'active' : '' ?>"
            ><?= $p ?></a>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <a href="/WEB_GR4/admin/users?page=<?= $page + 1 ?>&<?= $qs ?>" class="page-btn">Sau ›</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
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

<script src="/WEB_GR4/public/assets/js/admin/User.js"></script>

<!-- <script>
// ── Xoá user ───────────────────────────────────────────────
const modal = document.getElementById('deleteModal');

document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('deleteUserId').value   = btn.dataset.id;
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
</script> -->
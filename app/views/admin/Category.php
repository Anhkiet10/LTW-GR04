<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php include __DIR__ . '/../layouts/admin_sidebar.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/User.css">

<div class="admin-content">

  <!-- ── Header ─────────────────────────────────────────────── -->
  <div class="page-header">
    <div>
      <h1 class="page-title">Quản lý danh mục</h1>
      <p class="page-sub">Tổng cộng <strong><?= $total ?></strong> danh mục</p>
    </div>
    <a href="/WEB_GR4/admin/categories/create" class="btn btn-primary">
      <i class="fas fa-plus"></i> Thêm danh mục
    </a>
  </div>

  <!-- ── Flash ───────────────────────────────────────────────── -->
  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible">
      <?= htmlspecialchars($flash['message']) ?>
      <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
  <?php endif; ?>

  <!-- ── Bộ lọc ──────────────────────────────────────────────── -->
  <div class="filter-card">
    <form method="GET" action="/WEB_GR4/admin/categories" class="filter-form">
      <input
        type="text"
        name="search"
        placeholder="Tìm tên danh mục, mô tả…"
        value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
        class="form-control"
      >

      <select name="type" class="form-control">
        <option value="">Tất cả loại</option>
        <option value="root"  <?= ($filters['type'] ?? '') === 'root'  ? 'selected' : '' ?>>Danh mục gốc</option>
        <option value="child" <?= ($filters['type'] ?? '') === 'child' ? 'selected' : '' ?>>Danh mục con</option>
      </select>

      <button type="submit" class="btn btn-secondary">
        <i class="fas fa-search"></i> Tìm kiếm
      </button>
      <a href="/WEB_GR4/admin/categories" class="btn btn-ghost">Load lại trang</a>
    </form>
  </div>

  <!-- ── Bảng danh sách ──────────────────────────────────────── -->
  <div class="table-card">
    <?php if (empty($categories)): ?>
      <div class="empty-state">
        <i class="fas fa-folder-open fa-3x"></i>
        <p>Không tìm thấy danh mục nào.</p>
      </div>
    <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Tên danh mục</th>
            <th>Danh mục cha</th>
            <th>Mô tả</th>
            <th class="text-center">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $i => $cat): ?>
            <tr>
              <td><?= ($page - 1) * 10 + $i + 1 ?></td>
              <td>
                <?php if ($cat['parent_id']): ?>
                  <span style="margin-right:6px; color:#aaa;">└</span>
                <?php endif; ?>
                <strong><?= htmlspecialchars($cat['category_name']) ?></strong>
              </td>
              <td>
                <?php if ($cat['parent_name']): ?>
                  <span class="badge badge-info"><?= htmlspecialchars($cat['parent_name']) ?></span>
                <?php else: ?>
                  <span class="badge badge-warning">Gốc</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($cat['description'] ?? '—') ?></td>
              <td class="action-cell">
                <a
                  href="/WEB_GR4/admin/categories/edit?id=<?= $cat['category_id'] ?>"
                  class="btn-icon btn-edit"
                  title="Sửa"
                >
                  <i class="fas fa-edit"></i>
                </a>
                <button
                  class="btn-icon btn-delete delete-btn"
                  data-id="<?= $cat['category_id'] ?>"
                  data-name="<?= htmlspecialchars($cat['category_name']) ?>"
                  title="Xóa"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- ── Phân trang ────────────────────────────────────── -->
      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php
          $qs = http_build_query(array_filter([
            'search' => $filters['search'] ?? '',
            'type'   => $filters['type']   ?? '',
          ]));
          ?>

          <?php if ($page > 1): ?>
            <a href="/WEB_GR4/admin/categories?page=<?= $page - 1 ?>&<?= $qs ?>" class="page-btn">‹ Trước</a>
          <?php endif; ?>

          <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
            <a
              href="/WEB_GR4/admin/categories?page=<?= $p ?>&<?= $qs ?>"
              class="page-btn <?= $p === $page ? 'active' : '' ?>"
            ><?= $p ?></a>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <a href="/WEB_GR4/admin/categories?page=<?= $page + 1 ?>&<?= $qs ?>" class="page-btn">Sau ›</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- ── Modal xác nhận xoá ──────────────────────────────────── -->
<div id="deleteModal" class="modal" style="display:none">
  <div class="modal-box">
    <h3>Xác nhận xóa</h3>
    <p>Bạn có chắc muốn xóa danh mục <strong id="deleteCatName"></strong>?<br>
       Không thể xóa nếu danh mục còn danh mục con hoặc sản phẩm.</p>
    <div class="modal-actions">
      <button id="cancelDelete" class="btn btn-ghost">Huỷ</button>
      <form id="deleteForm" method="POST" action="/WEB_GR4/admin/categories/delete" style="display:inline">
        <input type="hidden" name="id" id="deleteCatId">
        <button type="submit" class="btn btn-danger">Xóa</button>
      </form>
    </div>
  </div>
</div>
<script src="/WEB_GR4/public/assets/js/admin/category.js"></script>
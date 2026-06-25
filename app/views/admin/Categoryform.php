<?php
// app/views/admin/CategoryForm.php
// Nhận: $category (array|null), $errors (array), $mode ('create'|'edit'), $parentList (array)

$isEdit = $mode === 'edit';
$title  = $isEdit ? 'Chỉnh sửa danh mục' : 'Thêm danh mục mới';
$action = $isEdit
    ? '/WEB_GR4/admin/categories/update'
    : '/WEB_GR4/admin/categories/store';

$val = fn(string $k, string $default = '') => htmlspecialchars($category[$k] ?? $default);
?>

<?php include __DIR__ . '/../layouts/admin_sidebar.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/UserForm.css">

<div class="admin-content">

  <!-- ── Header ─────────────────────────────────────────────── -->
  <div class="page-header">
    <div>
      <a href="/WEB_GR4/admin/categories" class="back-link">
        <i class="fas fa-arrow-left"></i> Quay lại
      </a>
      <h1 class="page-title"><?= $title ?></h1>
    </div>
  </div>

  <!-- ── Form ────────────────────────────────────────────────── -->
  <div class="form-card">
    <form method="POST" action="<?= $action ?>">
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $val('category_id') ?>">
      <?php endif; ?>

      <div class="form-grid">

        <!-- Tên danh mục -->
        <div class="form-group <?= !empty($errors['category_name']) ? 'has-error' : '' ?>">
          <label for="category_name">Tên danh mục <span class="required">*</span></label>
          <input
            type="text"
            id="category_name"
            name="category_name"
            value="<?= $val('category_name') ?>"
            class="form-control"
            placeholder="VD: Áo thun, Điện thoại…"
            autofocus
          >
          <?php if (!empty($errors['category_name'])): ?>
            <span class="error-msg"><?= $errors['category_name'] ?></span>
          <?php endif; ?>
        </div>

        <!-- Danh mục cha -->
        <div class="form-group <?= !empty($errors['parent_id']) ? 'has-error' : '' ?>">
          <label for="parent_id">Danh mục cha</label>
          <select id="parent_id" name="parent_id" class="form-control">
            <option value="">— Là danh mục gốc —</option>
            <?php foreach ($parentList as $parent): ?>
              <option
                value="<?= $parent['category_id'] ?>"
                <?= (string)($category['parent_id'] ?? '') === (string)$parent['category_id'] ? 'selected' : '' ?>
              >
                <?= htmlspecialchars($parent['category_name']) ?>
                <?= $parent['parent_id'] ? ' (con)' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($errors['parent_id'])): ?>
            <span class="error-msg"><?= $errors['parent_id'] ?></span>
          <?php endif; ?>
          <small class="text-muted">Để trống nếu đây là danh mục gốc.</small>
        </div>

        <!-- Mô tả -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label for="description">Mô tả</label>
          <textarea
            id="description"
            name="description"
            class="form-control"
            rows="4"
            placeholder="Mô tả ngắn về danh mục (không bắt buộc)…"
          ><?= $val('description') ?></textarea>
        </div>

      </div><!-- /.form-grid -->

      <div class="form-actions">
        <a href="/WEB_GR4/admin/categories" class="btn btn-ghost">Huỷ</a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i>
          <?= $isEdit ? 'Lưu thay đổi' : 'Tạo danh mục' ?>
        </button>
      </div>

    </form>
  </div>

</div>
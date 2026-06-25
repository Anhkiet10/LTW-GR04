<?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/product_admin.css">

<main class="admin-content">

    <!-- ── Page header ── -->
    <div class="page-header">
        <div class="page-header__left">
            <h1><i class="fas fa-box-open"></i> Quản lý sản phẩm</h1>
            <p>Thêm, chỉnh sửa và quản lý toàn bộ sản phẩm trong cửa hàng</p>
        </div>
        <button type="button" class="btn btn--sm btn--ghost" onclick="openAttributeManageModal()" title="Thêm thể loại và giá trị thuộc tính mới">
                                    <i class="fas fa-cog"></i> Chỉnh lại thuộc tính
                                </button>
        <button class="btn btn--primary" id="btnAddProduct">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </button>
    </div>

    <!-- ── Stats bar ── -->
    <div class="stats-bar">
        <div class="stat-card">
            <span class="stat-card__num"><?= (int)$total ?></span>
            <span class="stat-card__label">Tổng sản phẩm</span>
        </div>
        <div class="stat-card stat-card--green">
            <span class="stat-card__num"><?= (int)$active ?></span>
            <span class="stat-card__label">Đang bán</span>
        </div>
        <div class="stat-card stat-card--orange">
            <span class="stat-card__num"><?= (int)$hidden ?></span>
            <span class="stat-card__label">Đã ẩn</span>
        </div>
        <div class="stat-card stat-card--red">
            <span class="stat-card__num"><?= (int)$noStock ?></span>
            <span class="stat-card__label">Hết hàng</span>
        </div>
    </div>

    <!-- ── Filter bar ── -->
    <form class="filter-bar" id="filterForm" method="GET" action="/WEB_GR4/admin/products">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" name="search" placeholder="Tìm tên sản phẩm, SKU..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>
            <select id="filterCategory" name="category">
                <option value="">Tất cả danh mục</option>
                <?php
                // Tách danh mục cha và con
                $parents = array_filter($categories, fn($c) => !$c['parent_id']);
                foreach ($parents as $parent):
                ?>
                    <option value="<?= $parent['category_id'] ?>" <?= (string)($filters['category'] ?? '') === (string)$parent['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($parent['category_name']) ?></option>
                    <?php foreach ($categories as $child): ?>
                        <?php if ($child['parent_id'] == $parent['category_id']): ?>
                            <option value="<?= $child['category_id'] ?>" <?= (string)($filters['category'] ?? '') === (string)$child['category_id'] ? 'selected' : '' ?>>&nbsp;&nbsp;&nbsp;&nbsp;↳ <?= htmlspecialchars($child['category_name']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </select>
        <select id="filterStatus" name="status">
            <option value="">Tất cả trạng thái</option>
            <option value="1" <?= (string)($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Đang bán</option>
            <option value="0" <?= (string)($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Đã ẩn</option>
        </select>
    </form>

    <!-- ── Product table ── -->
    <div class="table-wrap">
        <table class="product-table" id="productTable">
            <thead>
                <tr>
                    <th style="width:56px">Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Biến thể</th>
                    <th>Giá</th>
                    <th>Tồn kho</th>
                    <th>Trạng thái</th>
                    <th style="width:100px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="8" class="empty-row">Chưa có sản phẩm nào</td></tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <?php
                    $stock = (int)($p['total_stock'] ?? 0);
                    $stockClass = $stock === 0 ? 'stock-badge--empty' : ($stock < 10 ? 'stock-badge--low' : 'stock-badge--ok');
                ?>
                <tr data-id="<?= $p['product_id'] ?>"
                    data-name="<?= strtolower(htmlspecialchars($p['product_name'])) ?>"
                    data-cat="<?= $p['category_id'] ?>"
                    data-parent-cat="<?= $p['parent_cat_id'] ?? '' ?>"
                    data-status="<?= $p['is_active'] ?>">

                    <td>
                        <?php if (!empty($p['image_url'])): ?>
                            <img src="/WEB_GR4/public<?= htmlspecialchars($p['image_url']) ?>" alt="" class="product-thumb">
                        <?php else: ?>
                            <div class="product-thumb product-thumb--empty"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div class="product-name"><?= htmlspecialchars($p['product_name']) ?></div>
                        <div class="product-slug"><?= htmlspecialchars($p['slug']) ?></div>
                    </td>

                    <td>
                        <div class="cat-cell">
                            <?php if (!empty($p['parent_cat_name'])): ?>
                                <span class="cat-parent"><?= htmlspecialchars($p['parent_cat_name']) ?></span>
                                <span class="cat-name cat-name--child"><?= htmlspecialchars($p['category_name'] ?? '—') ?></span>
                            <?php else: ?>
                                <span class="cat-name"><?= htmlspecialchars($p['category_name'] ?? '—') ?></span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <td class="text-center"><?= (int)$p['variant_count'] ?> biến thể</td>

                    <td>
                        <?php if (!empty($p['min_price'])): ?>
                            <span class="price"><?= number_format($p['min_price'], 0, ',', '.') ?>₫</span>
                            <?php if ($p['min_price'] != $p['max_price']): ?>
                                <span class="price-sep"></span>
                                <span class="price"><?= number_format($p['max_price'], 0, ',', '.') ?>₫</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-center">
                        <span class="stock-badge <?= $stockClass ?>"><?= $stock ?></span>
                    </td>

                    <td>
                        <?php if ($p['is_active']): ?>
                            <span class="status-badge status-badge--active">Đang bán</span>
                        <?php else: ?>
                            <span class="status-badge status-badge--hidden">Đã ẩn</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div class="action-btns">
                            <button class="action-btn action-btn--edit" title="Sửa"
                                    onclick="openEdit(<?= $p['product_id'] ?>)">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="action-btn action-btn--delete" title="Xóa"
                                    onclick="confirmDelete(<?= $p['product_id'] ?>, '<?= addslashes($p['product_name']) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php
            $qs = http_build_query(array_filter([
                'search'   => $filters['search']   ?? '',
                'category' => $filters['category'] ?? '',
                'status'   => $filters['status']   ?? '',
            ], fn($v) => $v !== ''));
            $qs = $qs !== '' ? '&' . $qs : '';
            ?>

            <?php if ($page > 1): ?>
                <a href="/WEB_GR4/admin/products?page=<?= $page - 1 ?><?= $qs ?>" class="page-btn">‹ Trước</a>
            <?php endif; ?>

            <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                <a
                    href="/WEB_GR4/admin/products?page=<?= $p ?><?= $qs ?>"
                    class="page-btn <?= $p === $page ? 'active' : '' ?>"
                ><?= $p ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="/WEB_GR4/admin/products?page=<?= $page + 1 ?><?= $qs ?>" class="page-btn">Sau ›</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</main>

<!-- ═══════════════════════════════════════════════════════
     MODAL: Thêm / Sửa sản phẩm
════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="productModal">
    <div class="modal" >

        <div class="modal__header">
            <h2 id="modalTitle">Thêm sản phẩm mới</h2>
            <button type="button" class="modal__close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>

        <form id="productForm" enctype="multipart/form-data" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
            <input type="hidden" name="product_id" id="fProductId" value="">

            <div class="modal__body" style="flex:1; overflow-y:auto;">

                <!-- Thông tin cơ bản -->
                <div class="form-section">
                    <h3 class="form-section__title"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                    <div class="form-grid">

                        <div class="form-group form-group--full">
                            <label>Tên sản phẩm <span class="required">*</span></label>
                            <input type="text" name="product_name" id="fName" placeholder="VD: iPhone 15 Pro Max" required>
                        </div>

                        <div class="form-group">
                            <label>Danh mục</label>
                            <select name="category_id" id="fCategory">

                                <option value="">— Chọn danh mục —</option>
                                <?php
                                // Tách danh mục cha và con
                                $parents = array_filter($categories, fn($c) => !$c['parent_id']);
                                foreach ($parents as $parent):
                                ?>
                                    <option value="<?= $parent['category_id'] ?>"><?= htmlspecialchars($parent['category_name']) ?></option>
                                    <?php foreach ($categories as $child): ?>
                                        <?php if ($child['parent_id'] == $parent['category_id']): ?>
                                            <option value="<?= $child['category_id'] ?>">&nbsp;&nbsp;&nbsp;&nbsp;↳ <?= htmlspecialchars($child['category_name']) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Trạng thái</label>
                            <label class="toggle" style="margin-top:6px;">
                                <input type="checkbox" name="is_active" id="fIsActive" checked>
                                <span class="toggle__track"></span>
                                <span class="toggle__label" id="toggleLabel" style="padding-left:8px;">Đang bán</span>
                            </label>
                        </div>

                        <div class="form-group form-group--full">
                            <label>Mô tả</label>
                            <textarea name="description" id="fDescription" rows="3" placeholder="Mô tả ngắn về sản phẩm..."></textarea>
                        </div>

                    </div>
                </div>

                <!-- Ảnh đại diện -->
                <div class="form-section">
                    <h3 class="form-section__title"><i class="fas fa-image"></i> Ảnh đại diện</h3>
                    <div class="image-upload-wrap">
                        <div class="image-preview" id="imagePreview">
                            <img src="" alt="preview" id="modalImgTarget" style="display:none; max-width:100%; height:100%; object-fit:cover;">
                            <span id="uploadHint" style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                                <i class="fas fa-cloud-upload-alt" style="font-size:22px;"></i>
                                Nhấn để chọn ảnh
                            </span>
                        </div>
                        <input type="file" name="image" id="fImage" accept="image/jpeg,image/png,image/webp" hidden>
                    </div>
                </div>

                <!-- Biến thể -->
                <div class="form-section">
                    <div class="form-section__header">
                        <h3 class="form-section__title"><i class="fas fa-layer-group"></i> Biến thể sản phẩm</h3>
                        <div class="variant-actions">
                            <button type="button" class="btn btn--sm btn--outline" onclick="addVariantRow()">
                                <i class="fas fa-plus"></i> Thêm thuộc tính
                            </button>
                            <div class="attr-col-add">
                                <select id="attrTypePicker" class="attr-type-picker">
                                    <option value="">— Chọn thể loại —</option>
                                    <?php foreach ($attributeTypes ?? [] as $attr): ?>
                                        <option value="<?= (int)$attr['attribute_id'] ?>">
                                            <?= htmlspecialchars($attr['attribute_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn--sm btn--outline" onclick="addAttributeColumn()">
                                    <i class="fas fa-plus"></i> Thêm thể loại thuộc tính
                                </button>
                                <button type="button" class="btn btn--sm btn--ghost" onclick="openAttributeManageModal()" title="Thêm thể loại và giá trị thuộc tính mới">
                                    <i class="fas fa-cog"></i> Chỉnh lại thuộc tính
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="variant-table-wrap">
                        <table class="variant-table">
                            <thead>
                                <tr id="variantTableHead">
                                    <th>SKU</th>
                                    <th data-col="price">Giá (₫)</th>
                                    <th>Tồn kho</th>
                                    <th>Bán</th>
                                    <th style="width:52px;text-align:center;" data-col="img">Ảnh</th>
                                    <th style="width:36px"></th>
                                </tr>
                            </thead>
                            <tbody id="variantRows"></tbody>
                        </table>
                    </div>
                    <p class="hint">Thêm thể loại thuộc tính (Màu sắc, Dung lượng…), rồi thêm biến thể với SKU và giá trị tương ứng.</p>
                </div>

            </div><!-- /.modal__body -->

            <div class="modal__footer">
                <button type="button" class="btn btn--ghost" onclick="closeModal()">Huỷ</button>
                <button type="submit" class="btn btn--primary" id="btnSubmit">
                    <i class="fas fa-save"></i> Lưu sản phẩm
                </button>
            </div>

        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODAL: Quản lý thuộc tính
════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="attributeManageModal">
    <div class="modal modal--attr-manage">
        <div class="modal__header">
            <h2><i class="fas fa-sliders-h"></i> Quản lý thuộc tính</h2>
            <button type="button" class="modal__close" onclick="closeAttributeManageModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal__body">
            <div class="attr-manage-section">
                <h4 class="attr-manage-section__title">Thêm thể loại thuộc tính mới</h4>
                <div class="attr-manage-form">
                    <input type="text" id="newAttributeTypeName" class="v-input" placeholder="VD: Chất liệu, Kích cỡ tay...">
                    <button type="button" class="btn btn--sm btn--primary" id="btnCreateAttributeType">
                        <i class="fas fa-plus"></i> Thêm thể loại
                    </button>
                </div>
            </div>

            <div class="attr-manage-section">
                <h4 class="attr-manage-section__title">Danh sách thể loại &amp; giá trị</h4>
                <div id="attributeManageList" class="attr-manage-list">
                    <p class="hint">Đang tải...</p>
                </div>
            </div>
        </div>
        <div class="modal__footer">
            <button type="button" class="btn btn--primary" onclick="closeAttributeManageModal()">Xong</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODAL: Xác nhận xóa
════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal" style="max-width:440px;">
        <div class="modal__header">
            <h3><i class="fas fa-exclamation-triangle" style="color:#dc2626;"></i> Xác nhận xóa</h3>
            <button type="button" class="modal__close" onclick="closeDeleteModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal__body">
            <p>Bạn có chắc chắn muốn xóa sản phẩm <strong id="deleteProductName"></strong>?</p>
            <p style="font-size:0.84rem; color:#6b7280; margin-top:6px;">Hành động này không thể hoàn tác và sẽ xóa toàn bộ biến thể, ảnh liên quan.</p>
        </div>
        <div class="modal__footer">
            <button type="button" class="btn btn--ghost" onclick="closeDeleteModal()">Hủy</button>
            <button type="button" class="btn btn--danger" id="btnConfirmDelete">
                <i class="fas fa-trash"></i> Xác nhận xóa
            </button>
        </div>
    </div>
</div>

<!-- Toast notification -->
<div class="toast" id="toast" style="display:none;"></div>
<div class="modal-overlay" id="confirmModal" style="z-index:1200;">
  <div class="modal" style="max-width:400px;">
    <div class="modal__header">
      <h3 style="color:#dc2626;margin:0;font-size:15px;">
        <i class="fas fa-exclamation-triangle"></i> Xác nhận
      </h3>
    </div>
    <div class="modal__body">
      <p id="confirmModalMsg" style="font-size:14px;color:#374151;line-height:1.6;margin:0;"></p>
    </div>
    <div class="modal__footer">
      <button type="button" class="btn btn--ghost" id="confirmModalCancel">Hủy</button>
      <button type="button" class="btn btn--danger" id="confirmModalOk"></button>
    </div>
  </div>
</div>
<script>
window.PRODUCT_ATTRIBUTES = <?= json_encode($attributeTypes ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<script src="/WEB_GR4/public/assets/js/admin/product_admin.js"></script>
<?php include __DIR__ . '/../layouts/admin_sidebar.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/backup.css">

<div class="admin-content">
    <div class="admin-header">
        <h1 class="admin-title"><i class="fas fa-database"></i> Sao lưu dữ liệu</h1>
        <p class="admin-subtitle">Xuất toàn bộ hoặc một phần dữ liệu ra file <code>.sql</code></p>
    </div>

    <!-- Thông báo kết quả -->
    <div id="backup-alert" class="backup-alert" style="display:none;"></div>

    <div class="backup-grid">

        <!-- Cột trái: Chọn bảng -->
        <div class="backup-card">
            <div class="backup-card-header">
                <i class="fas fa-table"></i> Chọn bảng cần sao lưu
            </div>
            <div class="backup-card-body">
                <div class="backup-select-actions">
                    <button type="button" class="btn-link" onclick="selectAll()">Chọn tất cả</button>
                    <span class="divider">|</span>
                    <button type="button" class="btn-link" onclick="deselectAll()">Bỏ chọn</button>
                </div>

                <ul class="table-list">
                    <?php foreach ($dbInfo as $item): ?>
                    <li class="table-list-item">
                        <label class="table-checkbox-label">
                            <input
                                type="checkbox"
                                class="table-checkbox"
                                name="tables[]"
                                value="<?php echo htmlspecialchars($item['table']); ?>"
                                checked
                            >
                            <span class="table-name">
                                <i class="fas fa-table table-icon"></i>
                                <?php echo htmlspecialchars($item['table']); ?>
                            </span>
                            <span class="table-row-count">
                                <?php echo number_format($item['rows']); ?> dòng
                            </span>
                        </label>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Cột phải: Tùy chọn & nút tải -->
        <div class="backup-card">
            <div class="backup-card-header">
                <i class="fas fa-cog"></i> Tùy chọn sao lưu
            </div>
            <div class="backup-card-body">
                <div class="backup-info-box">
                    <div class="backup-info-row">
                        <span><i class="fas fa-server"></i> Database</span>
                        <strong>w4shopdb</strong>
                    </div>
                    <div class="backup-info-row">
                        <span><i class="fas fa-layer-group"></i> Tổng số bảng</span>
                        <strong><?php echo count($dbInfo); ?> bảng</strong>
                    </div>
                    <div class="backup-info-row">
                        <span><i class="fas fa-calendar-alt"></i> Thời gian</span>
                        <strong><?php echo date('d/m/Y H:i'); ?></strong>
                    </div>
                </div>

                <div class="backup-note">
                    <i class="fas fa-info-circle"></i>
                    File <code>.sql</code> có thể được import lại qua phpMyAdmin hoặc MySQL CLI.
                </div>

                <button
                    id="btn-backup"
                    class="btn-backup"
                    onclick="startBackup()"
                >
                    <i class="fas fa-download"></i>
                    <span id="btn-backup-text">Tải xuống bản sao lưu</span>
                </button>

                <div id="backup-progress" class="backup-progress" style="display:none;">
                    <div class="backup-spinner"></div>
                    <span>Đang tạo file sao lưu, vui lòng chờ...</span>
                </div>
            </div>
        </div>

    </div><!-- /.backup-grid -->
</div><!-- /.admin-content -->

</div>

<script src="/WEB_GR4/public/assets/js/admin/backup.js"></script>
<?php
$cssV = @filemtime(APP_ROOT . '/public/css/import-wizard.css') ?: time();
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/import-wizard.css?v=<?= (int) $cssV ?>">

<div id="import-app" class="import-wizard-wrap"
     data-base-url="<?= htmlspecialchars(BASE_URL) ?>"
     data-max-rows="<?= (int) $maxRows ?>"
     data-groq-ok="<?= $groqOk ? '1' : '0' ?>">

    <a href="<?= BASE_URL ?>/product/index" class="import-back-link">
        <i class="bi bi-arrow-left"></i> Danh sách sản phẩm
    </a>

    <div class="import-wizard-top">
        <h1 class="h1 mb-0">Import sản phẩm từ Excel</h1>
        <p class="subtext mb-0 mt-2">Tải file danh sách hàng — hệ thống map cột và nhập hàng loạt</p>
    </div>

    <nav class="import-stepper" aria-label="Tiến trình import">
        <div class="import-stepper-item is-active" id="stepper-1">
            <span class="import-stepper-num" aria-hidden="true">1</span>
            <span>Tải file lên</span>
        </div>
        <span class="import-stepper-line" aria-hidden="true"></span>
        <div class="import-stepper-item" id="stepper-2">
            <span class="import-stepper-num" aria-hidden="true">2</span>
            <span>Xác nhận &amp; Import</span>
        </div>
    </nav>

    <div class="import-wizard-card">

        <!-- Bước 1 -->
        <div id="step-1">
            <div class="import-upload-area">
                <div id="dropzone" class="import-dropzone">
                    <input type="file" id="excel-file" class="d-none" accept=".xlsx,.xls,.csv">
                    <span class="import-dropzone-icon"><i class="bi bi-file-earmark-spreadsheet"></i></span>
                    <p class="import-dropzone-title mb-0">Kéo thả file vào đây</p>
                    <p class="subtext mb-3">hoặc click để chọn file</p>
                    <span class="btn btn-primary" id="btn-pick-file" role="button">
                        <i class="bi bi-upload me-1"></i> Chọn file Excel
                    </span>
                    <p class="subtext small mt-3 mb-0">
                        .xlsx, .xls, .csv · Tối đa <?= (int) $maxRows ?> dòng ·
                        <a href="<?= BASE_URL ?>/samples/mau_import_san_pham.xlsx" download onclick="event.stopPropagation()">Mẫu thời trang</a>
                        ·
                        <a href="<?= BASE_URL ?>/samples/mau_import_san_pham_moi.xlsx" download onclick="event.stopPropagation()">Mẫu SP lạ (mới)</a>
                    </p>
                </div>

                <div id="file-preview" class="import-file-preview d-none mt-0">
                    <div class="import-file-preview-icon"><i class="bi bi-file-earmark-excel"></i></div>
                    <div class="import-file-preview-meta">
                        <div class="import-file-preview-label">Đã chọn file</div>
                        <div class="import-file-preview-name" id="file-preview-name">—</div>
                        <div class="subtext small" id="file-preview-meta">—</div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-change-file">Đổi file</button>
                </div>
            </div>

            <div class="import-wizard-body">
                <div class="row g-3">
                    <?php if (!$isManager): ?>
                    <div class="col-sm-6">
                        <label class="import-field-label" for="branch-id">Chi nhánh gán tồn kho</label>
                        <select id="branch-id" class="form-select stripe-input w-100">
                            <?php foreach ($branches as $b): ?>
                            <option value="<?= (int) $b['branch_id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <input type="hidden" id="branch-id" value="<?= (int) $sessionBranchId ?>">
                    <div class="col-sm-6">
                        <span class="import-field-label">Chi nhánh</span>
                        <p class="mb-0 fw-500"><?= htmlspecialchars($branches[0]['name'] ?? 'Chi nhánh của bạn') ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm-6">
                        <label class="import-field-label" for="default-category">Danh mục mặc định</label>
                        <select id="default-category" class="form-select stripe-input w-100">
                            <option value="0">— Nếu file không có cột nhóm hàng —</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= (int) $c['category_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="import-notice">
                    <i class="bi bi-stars"></i>
                    <div>
                        <?php if ($groqOk): ?>
                        <strong class="d-block mb-1" style="color:var(--color-primary)">Phân tích bằng Groq AI</strong>
                        Tự động map cột Excel. Chỉnh lại ở bước 2 nếu cần.
                        <?php else: ?>
                        <strong class="d-block mb-1" style="color:var(--color-primary)">Map cột tự động</strong>
                        Nhận diện theo tên cột (Tên, Mã SP, Giá bán…).
                        <span class="d-block mt-1 small">Chưa có <code>GROQ_API_KEY</code> — <a href="https://console.groq.com/keys" target="_blank" rel="noopener">lấy key Groq</a></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="import-wizard-footer">
                    <span class="import-wizard-footer-status" id="step1-status">Chưa chọn file</span>
                    <button type="button" class="btn btn-primary" id="btn-analyze" disabled>
                        <i class="bi bi-stars me-1"></i> Phân tích &amp; map cột
                    </button>
                </div>
            </div>
        </div>

        <!-- Bước 2 -->
        <div id="step-2" class="d-none">
            <div class="stripe-alert stripe-alert-success import-step-banner py-3 px-4 mb-0 rounded-0">
                <i class="bi bi-check-circle me-2"></i>
                <span id="step2-file-text">File đã sẵn sàng</span>
            </div>

            <div class="import-wizard-body">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                    <p class="subtext mb-0 flex-grow-1" id="ai-status"></p>
                    <button type="button" class="btn btn-outline-primary btn-sm flex-shrink-0" id="btn-ai-map">
                        <i class="bi bi-arrow-repeat me-1"></i> Map lại
                    </button>
                </div>

                <p class="import-field-label mb-2">Ánh xạ cột Excel</p>
                <div class="table-responsive border rounded mb-4" style="border-color:var(--color-border)!important">
                    <table class="table stripe-table mb-0" id="mapping-table">
                        <thead>
                            <tr>
                                <th>Cột Excel</th>
                                <th>Trường hệ thống</th>
                                <th>Mẫu</th>
                            </tr>
                        </thead>
                        <tbody id="mapping-tbody"></tbody>
                    </table>
                </div>

                <p class="import-field-label mb-2">Xem trước</p>
                <p class="subtext small mb-2" id="preview-summary"></p>
                <div class="table-responsive border rounded mb-0" style="border-color:var(--color-border)!important">
                    <table class="table stripe-table table-sm mb-0" id="preview-table">
                        <thead>
                            <tr>
                                <th>Tên</th>
                                <th>SKU</th>
                                <th>Danh mục</th>
                                <th>Giá bán</th>
                                <th>Tồn</th>
                            </tr>
                        </thead>
                        <tbody id="preview-tbody"></tbody>
                    </table>
                </div>

                <div id="import-result" class="mt-4 d-none"></div>

                <div class="import-wizard-footer">
                    <button type="button" class="btn btn-ghost" id="btn-back-upload">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại
                    </button>
                    <div class="import-footer-actions">
                        <span class="import-wizard-footer-status" id="step2-status">Cần map Tên + SKU</span>
                        <button type="button" class="btn btn-primary" id="btn-commit" disabled>
                            <i class="bi bi-check-lg me-1"></i> Xác nhận &amp; Import
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<script src="<?= BASE_URL ?>/js/product-import.js?v=2"></script>

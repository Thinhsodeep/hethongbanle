(function () {
  const app = document.getElementById('import-app');
  if (!app) return;

  const BASE_URL = app.dataset.baseUrl || '';
  const MAX_ROWS = parseInt(app.dataset.maxRows || '500', 10);
  const GROQ_OK = app.dataset.groqOk === '1';

  const SYSTEM_FIELDS = [
    { value: '_skip', label: '— Bỏ qua —' },
    { value: 'name', label: 'Tên sản phẩm (name)' },
    { value: 'sku', label: 'Mã SKU (sku)' },
    { value: 'category_name', label: 'Danh mục (category_name)' },
    { value: 'sell_price', label: 'Giá bán (sell_price)' },
    { value: 'import_price', label: 'Giá nhập (import_price)' },
    { value: 'barcode', label: 'Mã vạch (barcode)' },
    { value: 'unit', label: 'Đơn vị (unit)' },
    { value: 'description', label: 'Mô tả (description)' },
    { value: 'color', label: 'Màu (color)' },
    { value: 'size', label: 'Size (size)' },
    { value: 'attribute', label: 'Thuộc tính (attribute)' },
    { value: 'quantity', label: 'Tồn kho (quantity)' },
  ];

  let headers = [];
  let rows = [];
  let columnMap = {};
  let defaults = {};
  let selectedFile = null;

  const fileInput = document.getElementById('excel-file');
  const dropzone = document.getElementById('dropzone');
  const filePreview = document.getElementById('file-preview');
  const step1 = document.getElementById('step-1');
  const step2 = document.getElementById('step-2');
  const mappingTbody = document.getElementById('mapping-tbody');
  const aiStatus = document.getElementById('ai-status');
  const previewTbody = document.getElementById('preview-tbody');
  const previewSummary = document.getElementById('preview-summary');
  const importResult = document.getElementById('import-result');
  const step1Status = document.getElementById('step1-status');
  const step2Status = document.getElementById('step2-status');
  const btnAnalyze = document.getElementById('btn-analyze');

  document.getElementById('btn-pick-file')?.addEventListener('click', (e) => {
    e.stopPropagation();
    fileInput?.click();
  });
  dropzone?.addEventListener('click', () => fileInput?.click());
  dropzone?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      fileInput?.click();
    }
  });
  fileInput?.addEventListener('change', (e) => onFilePicked(e.target.files?.[0]));
  document.getElementById('btn-change-file')?.addEventListener('click', () => fileInput?.click());
  document.getElementById('btn-analyze')?.addEventListener('click', runAnalyze);
  document.getElementById('btn-ai-map')?.addEventListener('click', () => callAiMap().then(refreshPreview));
  document.getElementById('btn-back-upload')?.addEventListener('click', () => goStep(1));
  document.getElementById('btn-commit')?.addEventListener('click', commitImport);

  setupDragDrop();

  function setupDragDrop() {
    if (!dropzone) return;
    ['dragenter', 'dragover'].forEach((ev) => {
      dropzone.addEventListener(ev, (e) => {
        e.preventDefault();
        dropzone.classList.add('is-dragover');
      });
    });
    ['dragleave', 'drop'].forEach((ev) => {
      dropzone.addEventListener(ev, (e) => {
        e.preventDefault();
        dropzone.classList.remove('is-dragover');
        if (ev === 'drop' && e.dataTransfer?.files?.[0]) {
          onFilePicked(e.dataTransfer.files[0]);
        }
      });
    });
  }

  function onFilePicked(file) {
    if (!file) return;
    const ext = (file.name.split('.').pop() || '').toLowerCase();
    if (!['xlsx', 'xls', 'csv'].includes(ext)) {
      alert('Chỉ hỗ trợ .xlsx, .xls, .csv');
      return;
    }
    selectedFile = file;
    showFilePreview(file);
    btnAnalyze.disabled = false;
    step1Status.textContent = 'Sẵn sàng phân tích';
    step1Status.classList.add('is-ready');
  }

  function showFilePreview(file) {
    const nameEl = document.getElementById('file-preview-name');
    const metaEl = document.getElementById('file-preview-meta');
    if (nameEl) nameEl.textContent = file.name;
    if (metaEl) {
      const kb = (file.size / 1024).toFixed(1);
      metaEl.textContent = `${(file.name.split('.').pop() || '').toUpperCase()} · ${kb} KB`;
    }
    if (dropzone) dropzone.style.display = 'none';
    filePreview?.classList.remove('d-none');
  }

  function resetFileUi() {
    selectedFile = null;
    headers = [];
    rows = [];
    filePreview?.classList.add('d-none');
    if (dropzone) dropzone.style.display = '';
    btnAnalyze.disabled = true;
    step1Status.textContent = 'Chưa chọn file';
    step1Status.classList.remove('is-ready');
    if (fileInput) fileInput.value = '';
  }

  async function runAnalyze() {
    if (!selectedFile) return;

    btnAnalyze.disabled = true;
    const origHtml = btnAnalyze.innerHTML;
    btnAnalyze.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang đọc file...';

    try {
      const buffer = await readFileAsArrayBuffer(selectedFile);
      parseWorkbook(buffer);
      buildDefaultMapping();
      renderMappingTable();

      const step2Text = document.getElementById('step2-file-text');
      if (step2Text) {
        step2Text.textContent = `${selectedFile.name} · ${rows.length} dòng sản phẩm`;
      }

      goStep(2);
      await callAiMap();
      refreshPreview();
      updateStep2Status();
    } catch (err) {
      console.error(err);
      alert('Không đọc được file: ' + (err.message || err));
    } finally {
      btnAnalyze.disabled = false;
      btnAnalyze.innerHTML = origHtml;
    }
  }

  function readFileAsArrayBuffer(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = (ev) => resolve(ev.target.result);
      reader.onerror = () => reject(new Error('Đọc file thất bại'));
      reader.readAsArrayBuffer(file);
    });
  }

  function parseWorkbook(buffer) {
    const data = new Uint8Array(buffer);
    const wb = XLSX.read(data, { type: 'array' });
    const sheet = wb.Sheets[wb.SheetNames[0]];
    const aoa = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '' });

    if (!aoa.length) throw new Error('File trống');

    const rawHeaders = aoa[0].map((h) => String(h ?? '').trim());
    headers = rawHeaders.map((h, i) => (h !== '' ? h : `Cột_${i + 1}`));
    if (!headers.some((h) => h && !h.startsWith('Cột_'))) {
      throw new Error('Không tìm thấy dòng tiêu đề');
    }

    rows = [];
    for (let i = 1; i < aoa.length; i++) {
      const line = aoa[i];
      if (!line || line.every((c) => String(c ?? '').trim() === '')) continue;
      const obj = {};
      headers.forEach((h, idx) => {
        obj[h] = String(line[idx] ?? '').trim();
      });
      if (Object.values(obj).some((v) => v !== '')) rows.push(obj);
    }

    if (rows.length > MAX_ROWS) {
      alert(`File có ${rows.length} dòng, chỉ lấy ${MAX_ROWS} dòng đầu.`);
      rows = rows.slice(0, MAX_ROWS);
    }
    if (rows.length === 0) throw new Error('Không có dòng dữ liệu');
  }

  function buildDefaultMapping() {
    columnMap = {};
    headers.forEach((h) => {
      columnMap[h] = guessField(h);
    });
  }

  function guessField(header) {
    const h = header.toLowerCase();
    if (/tên|ten|name|sản phẩm|san pham|hàng hóa/.test(h)) return 'name';
    if (/mã sp|ma sp|sku/.test(h)) return 'sku';
    if (/sku|mã hàng|ma hang/.test(h)) return 'sku';
    if (/\b(mã|ma)\b/.test(h) && !/vạch|vach|barcode|nhóm|nhom|loại|loai/.test(h)) return 'sku';
    if (/vạch|vach|barcode|ean/.test(h)) return 'barcode';
    if (/nhóm|nhom|loại|loai|danh mục|category/.test(h)) return 'category_name';
    if (/giá bán|gia ban|sell/.test(h) && !/nhập|nhap|vốn|von/.test(h)) return 'sell_price';
    if (/giá nhập|gia nhap|vốn|von|cost/.test(h)) return 'import_price';
    if (/\bprice\b/.test(h) && !/nhập|nhap|vốn|von/.test(h)) return 'sell_price';
    if (/tồn|ton|sl\b|số lượng|so luong|qty|quantity|kho/.test(h)) return 'quantity';
    if (/đơn vị|don vi|unit/.test(h)) return 'unit';
    if (/màu|mau|color/.test(h)) return 'color';
    if (/size|cỡ|co\b/.test(h)) return 'size';
    return '_skip';
  }

  function renderMappingTable() {
    if (!mappingTbody) return;
    mappingTbody.innerHTML = '';
    headers.forEach((h) => {
      const tr = document.createElement('tr');
      const sample = rows[0]?.[h] ?? '';
      const opts = SYSTEM_FIELDS.map(
        (f) =>
          `<option value="${f.value}" ${columnMap[h] === f.value ? 'selected' : ''}>${f.label}</option>`
      ).join('');
      tr.innerHTML = `
        <td><strong>${escapeHtml(h)}</strong></td>
        <td><select class="form-select stripe-input map-select" data-header="${escapeAttr(h)}">${opts}</select></td>
        <td class="subtext">${escapeHtml(sample)}</td>
      `;
      mappingTbody.appendChild(tr);
    });

    mappingTbody.querySelectorAll('.map-select').forEach((sel) => {
      sel.addEventListener('change', () => {
        columnMap[sel.dataset.header] = sel.value;
        refreshPreview();
        updateStep2Status();
      });
    });
  }

  async function callAiMap() {
    if (!aiStatus) return;
    aiStatus.textContent = GROQ_OK
      ? 'Groq AI đang phân tích cột...'
      : 'Đang map cột tự động (heuristic)...';

    const sampleRows = rows.slice(0, 8);
    try {
      const res = await fetch(`${BASE_URL}/productImport/mapColumns`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ headers, sample_rows: sampleRows }),
      });
      const data = await res.json();
      if (!data.ok) {
        aiStatus.textContent = data.message || 'Lỗi map cột';
        return;
      }
      columnMap = data.column_map || columnMap;
      defaults = data.defaults || {};
      renderMappingTable();
      const via = data.mapped_via_ai ? 'Groq AI' : 'Heuristic';
      aiStatus.textContent = `${via} · ${data.confidence || 'medium'}. ${data.notes || ''}`;
    } catch (err) {
      aiStatus.textContent = 'Lỗi: ' + err.message;
    }
  }

  function collectColumnMap() {
    mappingTbody?.querySelectorAll('.map-select').forEach((sel) => {
      columnMap[sel.dataset.header] = sel.value;
    });
    return columnMap;
  }

  function mapRowClient(row) {
    const out = {};
    Object.entries(columnMap).forEach(([header, field]) => {
      if (field === '_skip') return;
      const v = row[header];
      if (v !== undefined && String(v).trim() !== '') out[field] = String(v).trim();
    });
    Object.entries(defaults).forEach(([k, v]) => {
      if (!out[k] && v) out[k] = v;
    });
    return out;
  }

  function hasRequiredMapping() {
    collectColumnMap();
    const mapped = Object.values(columnMap);
    return mapped.includes('name') && mapped.includes('sku');
  }

  function refreshPreview() {
    if (!previewTbody || !previewSummary) return;
    collectColumnMap();
    previewTbody.innerHTML = '';
    rows.slice(0, 20).forEach((row) => {
      const m = mapRowClient(row);
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${escapeHtml(m.name || '—')}</td>
        <td>${escapeHtml(m.sku || '—')}</td>
        <td>${escapeHtml(m.category_name || '(mặc định)')}</td>
        <td>${escapeHtml(m.sell_price || '0')}</td>
        <td>${escapeHtml(m.quantity || '0')}</td>
      `;
      previewTbody.appendChild(tr);
    });
    previewSummary.textContent = `Tổng ${rows.length} dòng sẽ được import (hiển thị 20 dòng đầu).`;
  }

  function updateStep2Status() {
    if (!step2Status) return;
    if (hasRequiredMapping()) {
      step2Status.textContent = `Sẵn sàng import ${rows.length} sản phẩm`;
      step2Status.classList.add('is-ready');
      document.getElementById('btn-commit')?.removeAttribute('disabled');
    } else {
      step2Status.textContent = 'Cần map cột Tên (name) và Mã SKU (sku)';
      step2Status.classList.remove('is-ready');
      document.getElementById('btn-commit')?.setAttribute('disabled', 'disabled');
    }
  }

  async function commitImport() {
    if (!hasRequiredMapping()) {
      alert('Cần map ít nhất cột Tên (name) và SKU (sku).');
      return;
    }

    const branchId = parseInt(document.getElementById('branch-id')?.value || '0', 10);
    const defaultCategoryId = parseInt(
      document.getElementById('default-category')?.value || '0',
      10
    );

    const btn = document.getElementById('btn-commit');
    btn.disabled = true;
    const origHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang import...';

    try {
      const res = await fetch(`${BASE_URL}/productImport/commit`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          rows,
          column_map: columnMap,
          branch_id: branchId,
          options: { default_category_id: defaultCategoryId, defaults },
        }),
      });
      const data = await res.json();
      importResult?.classList.remove('d-none');

      if (!data.ok) {
        importResult.innerHTML = `<div class="stripe-alert stripe-alert-danger">${escapeHtml(data.message || 'Lỗi')}</div>`;
        return;
      }

      let html = `<div class="stripe-alert stripe-alert-success">
        Import xong: <strong>${data.imported}</strong> thành công,
        <strong>${data.skipped}</strong> bỏ qua.
        <a href="${BASE_URL}/product/index" class="ms-2">Xem danh sách SP</a>
      </div>`;
      if (data.errors?.length) {
        html += '<ul class="mt-2 small mb-0">';
        data.errors.slice(0, 30).forEach((e) => {
          html += `<li>Dòng ${e.row}: ${escapeHtml(e.message)}</li>`;
        });
        if (data.errors.length > 30) {
          html += `<li>... và ${data.errors.length - 30} lỗi khác</li>`;
        }
        html += '</ul>';
      }
      importResult.innerHTML = html;
      step2Status.textContent = `Đã import ${data.imported} sản phẩm`;
    } catch (err) {
      importResult?.classList.remove('d-none');
      importResult.innerHTML = `<div class="stripe-alert stripe-alert-danger">${escapeHtml(err.message)}</div>`;
    } finally {
      btn.disabled = false;
      btn.innerHTML = origHtml;
      updateStep2Status();
    }
  }

  function goStep(n) {
    step1?.classList.toggle('d-none', n !== 1);
    step2?.classList.toggle('d-none', n !== 2);

    const s1 = document.getElementById('stepper-1');
    const s2 = document.getElementById('stepper-2');
    s1?.classList.toggle('is-active', n === 1);
    s1?.classList.toggle('is-done', n > 1);
    s2?.classList.toggle('is-active', n === 2);
    s2?.classList.toggle('is-done', false);

    if (n === 1) importResult?.classList.add('d-none');
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function escapeAttr(s) {
    return escapeHtml(s).replace(/'/g, '&#39;');
  }
})();

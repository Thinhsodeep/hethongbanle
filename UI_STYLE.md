# UI Style Guide — Stripe Design (Bootstrap 5 Implementation)

# Agent đọc file này mỗi khi viết HTML/PHP view. Áp dụng 100% token bên dưới.

## Nguồn thiết kế

Stripe UI từ designdotmd.directory — đã được dịch sang Bootstrap 5 + custom CSS.
KHÔNG dùng Tailwind. KHÔNG dùng class Bootstrap mặc định nếu đã có override bên dưới.

---

## 1. COLOR TOKENS

```css
/* Dán vào public/css/style.css — dùng var() trong mọi custom CSS */
:root {
  --color-primary: #0a2540; /* navy đậm — sidebar, secondary btn */
  --color-accent: #635bff; /* tím Stripe — btn primary, link, badge accent */
  --color-secondary: #425466; /* slate — subtext, label mờ */
  --color-neutral: #f6f9fc; /* nền trang — body background */
  --color-surface: #ffffff; /* nền card, form, table */
  --color-border: #e3e8ef; /* viền input, card, divider */
  --color-text: #0a2540; /* text chính */
  --color-subtext: #425466; /* text phụ */
  --color-success: #27ae60;
  --color-warning: #f39c12;
  --color-danger: #e74c3c;
  --color-info: #635bff;

  --radius-sm: 6px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --radius-pill: 999px;

  --shadow-card:
    0 1px 3px rgba(10, 37, 64, 0.08), 0 1px 2px rgba(10, 37, 64, 0.05);
  --shadow-md: 0 4px 12px rgba(10, 37, 64, 0.1);
}
```

### Override Bootstrap color classes

```css
/* public/css/style.css */
body {
  background: var(--color-neutral);
  color: var(--color-text);
  font-family: "Inter", sans-serif;
}

/* Primary = tím Stripe */
.btn-primary {
  background: var(--color-accent);
  border-color: var(--color-accent);
  color: #fff;
  border-radius: var(--radius-md);
  font-weight: 500;
}
.btn-primary:hover {
  background: #4f46e5;
  border-color: #4f46e5;
}

/* Secondary = navy */
.btn-secondary {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: #fff;
  border-radius: var(--radius-md);
  font-weight: 500;
}
.btn-secondary:hover {
  background: #0d2f4f;
}

/* Outline */
.btn-outline-primary {
  color: var(--color-accent);
  border-color: var(--color-accent);
  border-radius: var(--radius-md);
  font-weight: 500;
}
.btn-outline-primary:hover {
  background: var(--color-accent);
  color: #fff;
}

/* Ghost */
.btn-ghost {
  background: transparent;
  border: none;
  color: var(--color-secondary);
  border-radius: var(--radius-md);
  font-weight: 500;
}
.btn-ghost:hover {
  background: var(--color-neutral);
}
```

---

## 2. TYPOGRAPHY

```html
<!-- Thêm vào layouts/header.php, trong <head> -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link
  href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
  rel="stylesheet"
/>
```

```css
/* Scale */
.text-display {
  font-size: 3rem;
  font-weight: 300;
  color: var(--color-primary);
  line-height: 1.15;
}
h1,
.h1 {
  font-size: 2rem;
  font-weight: 600;
  color: var(--color-primary);
}
h2,
.h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--color-primary);
}
h3,
.h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--color-primary);
}
body,
.body {
  font-size: 0.9375rem;
  font-weight: 400;
  line-height: 1.6;
}
.label-text {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-secondary);
}
.subtext {
  font-size: 0.875rem;
  color: var(--color-secondary);
}
a {
  color: var(--color-accent);
  text-decoration: none;
}
a:hover {
  text-decoration: underline;
}
```

---

## 3. BUTTONS

```html
<!-- Primary (tím) -->
<button class="btn btn-primary">Primary</button>

<!-- Secondary (navy) -->
<button class="btn btn-secondary">Secondary</button>

<!-- Outline -->
<button class="btn btn-outline-primary">Outline</button>

<!-- Ghost -->
<button class="btn btn-ghost">Ghost</button>

<!-- Pill shape — thêm class .rounded-pill -->
<button class="btn btn-primary rounded-pill px-4">Pill</button>

<!-- Size -->
<button class="btn btn-primary btn-sm">Small</button>
<button class="btn btn-primary btn-lg">Large</button>

<!-- Icon only -->
<button class="btn btn-ghost p-2" style="border-radius:var(--radius-md)">
  <i class="bi bi-search"></i>
</button>
```

```css
/* Tất cả btn đều không có shadow mặc định */
.btn {
  box-shadow: none !important;
  transition: all 0.15s ease;
}
.btn:focus {
  box-shadow: 0 0 0 3px rgba(99, 91, 255, 0.25) !important;
}
```

---

## 4. FORM CONTROLS

```html
<!-- Label: uppercase + spacing -->
<div class="mb-3">
  <label class="label-text mb-1">Email</label>
  <input
    type="email"
    class="form-control stripe-input"
    placeholder="iris@studio.com"
  />
</div>

<!-- Textarea -->
<div class="mb-3">
  <label class="label-text mb-1">Bio</label>
  <textarea class="form-control stripe-input" rows="3"></textarea>
</div>

<!-- Select -->
<div class="mb-3">
  <label class="label-text mb-1">Workspace</label>
  <select class="form-select stripe-input">
    <option>Studio Saikai</option>
  </select>
</div>

<!-- Checkbox — Bootstrap đã đủ, chỉ override màu -->
<div class="form-check">
  <input class="form-check-input" type="checkbox" checked />
  <label class="form-check-label">Weekly digest</label>
</div>

<!-- Radio card (bordered) -->
<div class="stripe-radio-card">
  <input type="radio" name="plan" id="pro" checked />
  <label for="pro">
    <strong>Pro</strong>
    <span class="subtext">$18 / month</span>
  </label>
</div>
```

```css
.stripe-input {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 0.625rem 0.875rem;
  font-size: 0.9375rem;
  color: var(--color-text);
  background: var(--color-surface);
  transition:
    border-color 0.15s,
    box-shadow 0.15s;
}
.stripe-input:focus {
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(99, 91, 255, 0.15);
  outline: none;
}
.form-check-input:checked {
  background-color: var(--color-accent);
  border-color: var(--color-accent);
}

/* Radio card */
.stripe-radio-card {
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 0.875rem 1rem;
  margin-bottom: 0.5rem;
  cursor: pointer;
  transition: border-color 0.15s;
}
.stripe-radio-card:has(input:checked) {
  border-color: var(--color-accent);
  background: rgba(99, 91, 255, 0.04);
}
.stripe-radio-card label {
  display: flex;
  flex-direction: column;
  cursor: pointer;
  margin: 0;
}
```

---

## 5. BADGES & CHIPS

```html
<!-- Neutral (outline) -->
<span class="stripe-badge stripe-badge-neutral">NEUTRAL</span>

<!-- Accent (tím) -->
<span class="stripe-badge stripe-badge-accent">ACCENT</span>

<!-- Solid (navy) -->
<span class="stripe-badge stripe-badge-solid">SOLID</span>

<!-- Status badges — dùng trong bảng -->
<span class="stripe-badge stripe-badge-accent">PUBLISHED</span>
<span class="stripe-badge stripe-badge-neutral">REVIEW</span>
<span class="stripe-badge stripe-badge-muted">DRAFT</span>

<!-- Tồn kho -->
<span class="stripe-badge stripe-badge-success">Còn hàng</span>
<span class="stripe-badge stripe-badge-warning">Sắp hết</span>
<span class="stripe-badge stripe-badge-danger">Hết hàng</span>
```

```css
.stripe-badge {
  display: inline-flex;
  align-items: center;
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 0.25rem 0.625rem;
  border-radius: var(--radius-pill);
  white-space: nowrap;
}
.stripe-badge-neutral {
  border: 1px solid var(--color-border);
  color: var(--color-secondary);
  background: transparent;
}
.stripe-badge-accent {
  background: var(--color-accent);
  color: #fff;
}
.stripe-badge-solid {
  background: var(--color-primary);
  color: #fff;
}
.stripe-badge-muted {
  background: #e3e8ef;
  color: var(--color-secondary);
}
.stripe-badge-success {
  background: #e6f9ef;
  color: #1a7a46;
}
.stripe-badge-warning {
  background: #fef3cd;
  color: #856404;
}
.stripe-badge-danger {
  background: #fdecea;
  color: #b91c1c;
}
```

---

## 6. CARDS

```html
<!-- Feature card -->
<div class="stripe-card">
  <p class="label-text mb-1">Feature</p>
  <h3 class="h3 mb-2">Editorial rigor</h3>
  <p class="subtext mb-3">
    Prose-first token file — decisions live next to their reasoning.
  </p>
  <a href="#" class="stripe-link">Learn more →</a>
</div>

<!-- Metric card -->
<div class="stripe-card">
  <p class="label-text mb-1">Metric</p>
  <div class="text-display mb-1">24,810</div>
  <p class="subtext" style="color:var(--color-success)">
    ▲ +12.4% vs last week
  </p>
</div>

<!-- Dashboard stat card -->
<div class="stripe-card text-center">
  <div class="h1 mb-1" style="color:var(--color-accent)">42</div>
  <p class="label-text mb-0">Tổng sản phẩm</p>
</div>
```

```css
.stripe-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: 1.25rem 1.5rem;
  box-shadow: var(--shadow-card);
}
.stripe-link {
  color: var(--color-accent);
  font-size: 0.9375rem;
  font-weight: 500;
  text-decoration: none;
}
.stripe-link:hover {
  text-decoration: underline;
}
```

---

## 7. NAVIGATION & ALERTS

```html
<!-- Tab navigation -->
<div class="stripe-tabs">
  <button class="stripe-tab active">Overview</button>
  <button class="stripe-tab">Analytics</button>
  <button class="stripe-tab">Settings</button>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
  <ol class="breadcrumb stripe-breadcrumb">
    <li class="breadcrumb-item"><a href="#">Workspace</a></li>
    <li class="breadcrumb-item"><a href="#">Projects</a></li>
    <li class="breadcrumb-item active">Heritage System</li>
  </ol>
</nav>

<!-- Alert info -->
<div class="stripe-alert stripe-alert-info">
  <i class="bi bi-info-circle me-2"></i>
  Your file is ready to publish. <a href="#">Review changes →</a>
</div>

<!-- Alert success -->
<div class="stripe-alert stripe-alert-success">
  <i class="bi bi-check-circle me-2"></i>
  <strong>Success.</strong> Published to the directory as v0.alpha.
</div>

<!-- Alert danger (flash message) -->
<div class="stripe-alert stripe-alert-danger">
  <strong>Lỗi.</strong> Email hoặc mật khẩu không đúng.
</div>
```

```css
/* Tabs */
.stripe-tabs {
  display: flex;
  gap: 4px;
  background: var(--color-neutral);
  padding: 4px;
  border-radius: var(--radius-md);
  width: fit-content;
  margin-bottom: 1.5rem;
}
.stripe-tab {
  border: none;
  background: transparent;
  padding: 0.4rem 0.875rem;
  border-radius: calc(var(--radius-md) - 2px);
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-secondary);
  cursor: pointer;
  transition: all 0.15s;
}
.stripe-tab.active,
.stripe-tab:hover {
  background: var(--color-surface);
  color: var(--color-primary);
  box-shadow: var(--shadow-card);
}

/* Breadcrumb */
.stripe-breadcrumb {
  font-size: 0.875rem;
}
.stripe-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
  color: var(--color-border);
}
.stripe-breadcrumb a {
  color: var(--color-secondary);
}
.stripe-breadcrumb .active {
  color: var(--color-primary);
  font-weight: 500;
}

/* Alerts */
.stripe-alert {
  padding: 0.875rem 1rem;
  border-radius: var(--radius-md);
  font-size: 0.9rem;
  margin-bottom: 1rem;
}
.stripe-alert-info {
  background: rgba(99, 91, 255, 0.07);
  color: var(--color-primary);
  border: 1px solid rgba(99, 91, 255, 0.2);
}
.stripe-alert-success {
  background: var(--color-accent);
  color: #fff;
}
.stripe-alert-danger {
  background: #fdecea;
  color: #b91c1c;
  border: 1px solid #f5c6c6;
}
.stripe-alert-warning {
  background: #fef3cd;
  color: #856404;
  border: 1px solid #fde68a;
}
.stripe-alert a {
  color: inherit;
  font-weight: 600;
}
```

---

## 8. TABLE

```html
<div class="stripe-card p-0">
  <!-- Table header -->
  <div
    class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom"
  >
    <div>
      <h3 class="h3 mb-0">Danh sách sản phẩm</h3>
      <p class="subtext mb-0">Cập nhật vừa xong</p>
    </div>
    <div class="d-flex gap-2">
      <input
        type="text"
        class="stripe-input"
        placeholder="Search..."
        style="width:220px"
      />
      <button class="btn btn-ghost">Filter</button>
      <button class="btn btn-primary">Export</button>
    </div>
  </div>

  <!-- Table -->
  <table class="table stripe-table mb-0">
    <thead>
      <tr>
        <th>Sản phẩm</th>
        <th>SKU</th>
        <th>Tồn kho</th>
        <th>Trạng thái</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <div class="d-flex align-items-center gap-3">
            <div class="stripe-avatar">SP</div>
            <div>
              <div class="fw-500">Tai nghe Bluetooth</div>
              <div class="subtext" style="font-size:.8rem">SKU-EL-001</div>
            </div>
          </div>
        </td>
        <td class="subtext">SKU-EL-001</td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <div class="stripe-progress" style="width:80px">
              <div class="stripe-progress-bar" style="width:78%"></div>
            </div>
            <span class="subtext" style="font-size:.8rem">50</span>
          </div>
        </td>
        <td><span class="stripe-badge stripe-badge-accent">Còn hàng</span></td>
        <td class="text-end">
          <button class="btn btn-ghost btn-sm p-1">
            <i class="bi bi-three-dots"></i>
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

```css
/* Table */
.stripe-table {
  font-size: 0.9rem;
  border-collapse: collapse;
}
.stripe-table thead th {
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-secondary);
  border-bottom: 1px solid var(--color-border);
  padding: 0.75rem 1.5rem;
  background: transparent;
}
.stripe-table tbody td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--color-border);
  vertical-align: middle;
}
.stripe-table tbody tr:last-child td {
  border-bottom: none;
}
.stripe-table tbody tr:hover {
  background: rgba(10, 37, 64, 0.02);
}
.fw-500 {
  font-weight: 500;
}

/* Avatar initials */
.stripe-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--color-accent);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stripe-avatar-dark {
  background: var(--color-primary);
}

/* Progress bar */
.stripe-progress {
  height: 6px;
  background: var(--color-border);
  border-radius: var(--radius-pill);
  overflow: hidden;
}
.stripe-progress-bar {
  height: 100%;
  background: var(--color-accent);
  border-radius: var(--radius-pill);
  transition: width 0.3s ease;
}
```

---

## 9. SIDEBAR & LAYOUT

```html
<!-- Cấu trúc layout chung — dùng trong layouts/header.php -->
<div class="d-flex" style="min-height:100vh">
  <!-- Sidebar -->
  <aside class="stripe-sidebar">
    <div class="stripe-sidebar-brand">
      <div
        class="stripe-avatar stripe-avatar-dark"
        style="border-radius:var(--radius-md)"
      >
        RC
      </div>
      <span>Retail Chain</span>
    </div>
    <nav class="stripe-sidebar-nav">
      <p class="label-text px-3 mb-1 mt-3">Tổng quan</p>
      <a href="#" class="stripe-nav-item active">
        <i class="bi bi-grid"></i> Dashboard
      </a>
      <p class="label-text px-3 mb-1 mt-3">Quản lý</p>
      <a href="#" class="stripe-nav-item">
        <i class="bi bi-building"></i> Chi nhánh
      </a>
      <a href="#" class="stripe-nav-item">
        <i class="bi bi-people"></i> Nhân viên
      </a>
      <a href="#" class="stripe-nav-item">
        <i class="bi bi-box-seam"></i> Sản phẩm
      </a>
      <a href="#" class="stripe-nav-item">
        <i class="bi bi-archive"></i> Tồn kho
      </a>
      <a href="#" class="stripe-nav-item text-danger mt-auto">
        <i class="bi bi-box-arrow-left"></i> Đăng xuất
      </a>
    </nav>
  </aside>

  <!-- Main content -->
  <main class="stripe-main">
    <!-- Page header -->
    <div class="stripe-page-header">
      <div>
        <h1 class="h1 mb-0">Sản phẩm</h1>
        <p class="subtext mb-0">Quản lý toàn bộ sản phẩm trong hệ thống</p>
      </div>
      <button class="btn btn-primary">+ Thêm sản phẩm</button>
    </div>
    <!-- nội dung trang -->
  </main>
</div>
```

```css
/* Sidebar */
.stripe-sidebar {
  width: 230px;
  min-height: 100vh;
  flex-shrink: 0;
  background: var(--color-primary);
  display: flex;
  flex-direction: column;
  padding: 1.25rem 0;
}
.stripe-sidebar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 1.25rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  font-weight: 600;
  font-size: 0.9375rem;
}
.stripe-sidebar-nav {
  display: flex;
  flex-direction: column;
  padding: 0.5rem 0;
  flex: 1;
}
.stripe-nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0.55rem 1.25rem;
  margin: 1px 0.75rem;
  border-radius: var(--radius-md);
  color: rgba(255, 255, 255, 0.65);
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.15s;
}
.stripe-nav-item:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
}
.stripe-nav-item.active {
  background: rgba(99, 91, 255, 0.35);
  color: #fff;
}
.stripe-nav-item i {
  font-size: 1rem;
  width: 18px;
  text-align: center;
}
.label-text.px-3 {
  padding-left: 1.25rem !important;
}

/* Main */
.stripe-main {
  flex: 1;
  padding: 2rem 2.5rem;
  background: var(--color-neutral);
  overflow-y: auto;
}
.stripe-page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.75rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid var(--color-border);
}
```

---

## 10. MODAL XÁC NHẬN XÓA

```html
<!-- Trigger -->
<button
  class="btn btn-ghost btn-sm text-danger"
  data-bs-toggle="modal"
  data-bs-target="#modalDelete"
>
  <i class="bi bi-trash"></i>
</button>

<!-- Modal -->
<div class="modal fade" id="modalDelete" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div
      class="modal-content stripe-card p-0"
      style="border-radius:var(--radius-lg)"
    >
      <div class="modal-body p-4">
        <h3 class="h3 mb-1">Xác nhận xóa</h3>
        <p class="subtext">
          Hành động này không thể hoàn tác. Bạn có chắc muốn tiếp tục?
        </p>
        <div class="d-flex gap-2 justify-content-end mt-3">
          <button class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
          <button class="btn btn-danger" style="border-radius:var(--radius-md)">
            Xóa
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
```

---

## 11. DASHBOARD CARDS (Module 9a)

```html
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stripe-card">
      <p class="label-text mb-2">Tổng chi nhánh</p>
      <div class="h1 mb-0" style="color:var(--color-accent)">3</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stripe-card">
      <p class="label-text mb-2">Tổng sản phẩm</p>
      <div class="h1 mb-0" style="color:var(--color-accent)">128</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stripe-card">
      <p class="label-text mb-2" style="color:var(--color-warning)">
        Sắp hết hàng
      </p>
      <div class="h1 mb-0" style="color:var(--color-warning)">7</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stripe-card">
      <p class="label-text mb-2" style="color:var(--color-danger)">Hết hàng</p>
      <div class="h1 mb-0" style="color:var(--color-danger)">2</div>
    </div>
  </div>
</div>
```

---

## 12. FILE CSS HOÀN CHỈNH — thứ tự import

```html
<!-- Trong layouts/header.php, theo thứ tự này -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link
  href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
  rel="stylesheet"
/>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
/>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"
/>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css" />
```

> `style.css` phải load **sau** Bootstrap để override đúng.

---

## 13. QUY TẮC AGENT KHI VIẾT VIEW

1. **Nền trang** luôn là `var(--color-neutral)` = #F6F9FC, KHÔNG dùng trắng thuần
2. **Card** dùng class `stripe-card`, KHÔNG dùng `card card-body` của Bootstrap
3. **Badge/trạng thái** dùng `stripe-badge stripe-badge-*`, KHÔNG dùng `badge bg-*` Bootstrap
4. **Alert/flash** dùng `stripe-alert stripe-alert-*`, KHÔNG dùng `alert alert-*` Bootstrap
5. **Table** dùng `stripe-table` bên trong `stripe-card p-0`
6. **Form input** dùng class `stripe-input` thay cho `form-control`
7. **Label** luôn có class `label-text` — uppercase + letter-spacing
8. **Button** dùng đúng variant: primary=tím, secondary=navy, ghost=transparent
9. **Icons** dùng Bootstrap Icons (`bi bi-*`) — đã có CDN trong header
10. **Sidebar** dùng class `stripe-sidebar` + `stripe-nav-item`, KHÔNG dùng `nav nav-pills`

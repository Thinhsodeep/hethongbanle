# Retail Chain System

Hệ thống quản lý chuỗi bán lẻ đa chi nhánh (PHP MVC thuần). Web root: `public/`.

## Yêu cầu

- PHP 8.0+ với `pdo_mysql`
- MySQL 8.x / MariaDB

## Cơ sở dữ liệu

```bash
mysql -u root -p < database/retail_chain.sql
mysql -u root -p < database/patch_passwords.sql
# hoặc (sau khi chỉnh config/database.php):
php database/patch_passwords.php
```

`patch_passwords.sql` / `patch_passwords.php` đặt mật khẩu test **`Abc@12345`** cho các tài khoản seed (chạy một lần).

Chỉnh kết nối tại `config/database.php` nếu user/pass MySQL khác `root`/rỗng.

## Chạy (development)

```bash
cd public
php -S localhost:8080 router.php
```

Mở: `http://localhost:8080` — mặc định vào `/auth/login`.

**Lưu ý:** Phải dùng `router.php` (PHP built-in không đọc `.htaccess`).

### Apache

DocumentRoot → `public/`, bật `mod_rewrite` (file `.htaccess` có sẵn).

## Tài khoản test

| Email | Vai trò | Mật khẩu |
|-------|---------|----------|
| admin@retailchain.vn | admin | Abc@12345 |
| manager.q1@retailchain.vn | manager | Abc@12345 |
| staff.q1@retailchain.vn | staff | Abc@12345 |

## Import sản phẩm từ Excel (AI)

Dành cho shop cũ có danh sách hàng trên Excel. Trang: **Sản phẩm → Import Excel (AI)** (`/productImport/index`).

Cơ chế 2 tầng (giống parse CV):

1. **Trích dữ liệu** — SheetJS trên trình duyệt đọc `.xlsx` / `.csv` → headers + rows JSON.
2. **Map cột** — `ProductImportMapper`: Groq AI (`mapWithAI`) hoặc **heuristic regex** (`mapWithHeuristic`) nếu không có key / API lỗi.

### Cấu hình Groq API key

1. Lấy key tại [console.groq.com/keys](https://console.groq.com/keys)
2. Cách 1: sao chép `.env.example` → `.env`, điền `GROQ_API_KEY=gsk_...`
3. Cách 2: sửa [`config/constants.php`](config/constants.php) (giống dự án CV)

```env
GROQ_API_KEY=gsk_your_key
GROQ_MODEL=llama-3.1-8b-instant
IMPORT_MAX_ROWS=500
IMPORT_DEBUG=false
```

**Test key:** `php test_groq.php` hoặc mở `test_groq.php` trên browser local.

Nếu key trống hoặc Groq lỗi → vẫn map được bằng heuristic + chỉnh tay trên form.

Debug (khi `IMPORT_DEBUG=true`): `debug_import_groq_raw.txt`, `debug_import_ai_result.txt`, `debug_import_groq_error.txt` ở thư mục gốc project.

### Cách dùng

1. Chuẩn bị file `.xlsx` / `.csv` (mỗi dòng = 1 sản phẩm + 1 SKU)
2. Tải file mẫu: `public/samples/sample_import_products.csv`
3. Upload → AI (hoặc thủ công) map cột → xem trước → Import
4. **Manager**: tồn kho gán vào chi nhánh đang đăng nhập
5. **Admin**: chọn chi nhánh trước khi import

Giới hạn: tối đa `IMPORT_MAX_ROWS` dòng (mặc định 500). POST JSON lớn có thể cần tăng `post_max_size` / `upload_max_filesize` trong `php.ini` (~2MB+).

## Cấu trúc

- `public/` — entry, CSS, JS, ảnh
- `config/` — BASE_URL, DB, `constants.php` (Groq, `.env`)
- `core/` — App, Controller, Database
- `app/` — Middlewares, Models, Controllers, Views, Services

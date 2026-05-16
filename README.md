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

## Cấu trúc

- `public/` — entry, CSS, JS, ảnh
- `config/` — BASE_URL, DB
- `core/` — App, Controller, Database
- `app/` — Middlewares, Models, Controllers, Views

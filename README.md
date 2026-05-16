# Retail Chain System

Ứng dụng PHP quản lý chuỗi bán lẻ (MVC nhẹ). Document root của web server phải trỏ vào thư mục `public/`.

## Yêu cầu

- PHP 8.0+ (khuyến nghị 8.2+) với extension `pdo_mysql`
- MySQL / MariaDB

## Cơ sở dữ liệu

1. Tạo database (hoặc dùng script trong repo):

   ```bash
   mysql -u root -p < database/retail_chain.sql
   ```

2. Sao chép cấu hình kết nối:

   - Chỉnh `config/database.php` trên máy local (hoặc tạo `config/database.local.php` và load trong code — không commit file chứa mật khẩu thật).

## Chạy project (development)

### PHP built-in server

Từ thư mục gốc project:

```bash
cd public
php -S localhost:8080
```

Mở trình duyệt: `http://localhost:8080`

### Apache / Nginx

- **DocumentRoot**: `.../retail-chain-system/public`
- Đảm bảo rewrite mọi request về `index.php` (front controller).

## Cấu trúc thư mục

- `public/` — entry `index.php`, CSS, JS, hình ảnh tĩnh
- `config/` — timezone, base URL, PDO (không đưa mật khẩu production lên Git)
- `core/` — router, controller/database base, middleware base
- `app/` — middleware theo vai trò, models, controllers, views

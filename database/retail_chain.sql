-- ==============================================================
--  HỆ THỐNG QUẢN LÝ CHUỖI BÁN LẺ ĐA CHI NHÁNH
--  File     : retail_chain.sql
--  Charset  : utf8mb4
--  Engine   : InnoDB
--  Tác giả  : [Nhóm của bạn]
-- ==============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS retail_chain;
CREATE DATABASE retail_chain
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE retail_chain;

-- ==============================================================
--  NHÓM 1 — HỆ THỐNG & PHÂN QUYỀN
-- ==============================================================

CREATE TABLE roles (
    role_id     INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    role_name   VARCHAR(50)      NOT NULL UNIQUE COMMENT 'admin | manager | staff | cashier',
    description VARCHAR(255),
    created_at  TIMESTAMP        DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Vai trò người dùng';

-- branches tạo trước, manager_id thêm bằng ALTER sau khi có users
CREATE TABLE branches (
    branch_id  INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    address    VARCHAR(255)  NOT NULL,
    phone      VARCHAR(20),
    manager_id INT UNSIGNED  NULL COMMENT 'FK → users.user_id, thêm sau',
    status     ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Chi nhánh';

CREATE TABLE users (
    user_id       INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    branch_id     INT UNSIGNED  NOT NULL,
    role_id       INT UNSIGNED  NOT NULL,
    full_name     VARCHAR(100)  NOT NULL,
    email         VARCHAR(100)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL COMMENT 'bcrypt hash',
    phone         VARCHAR(20),
    status        ENUM('active','inactive') DEFAULT 'active',
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_branch FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
    CONSTRAINT fk_user_role   FOREIGN KEY (role_id)   REFERENCES roles(role_id)
) ENGINE=InnoDB COMMENT='Nhân viên / tài khoản hệ thống';

-- Thêm FK manager_id sau khi bảng users đã tồn tại
ALTER TABLE branches
    ADD CONSTRAINT fk_branch_manager
    FOREIGN KEY (manager_id) REFERENCES users(user_id) ON DELETE SET NULL;

-- ==============================================================
--  NHÓM 2 — SẢN PHẨM & DANH MỤC
-- ==============================================================

CREATE TABLE categories (
    category_id INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    description TEXT,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Danh mục sản phẩm';

CREATE TABLE products (
    product_id   INT UNSIGNED      AUTO_INCREMENT PRIMARY KEY,
    category_id  INT UNSIGNED      NOT NULL,
    name         VARCHAR(200)      NOT NULL,
    sku          VARCHAR(50)       NOT NULL UNIQUE COMMENT 'Mã sản phẩm nội bộ',
    barcode      VARCHAR(50)       UNIQUE        COMMENT 'Mã vạch EAN/UPC',
    unit         VARCHAR(30)       DEFAULT 'cái' COMMENT 'Đơn vị tính',
    sell_price   DECIMAL(15,2)     NOT NULL DEFAULT 0,
    import_price DECIMAL(15,2)     NOT NULL DEFAULT 0,
    description  TEXT,
    image        VARCHAR(255),
    status       ENUM('active','inactive') DEFAULT 'active',
    created_at   TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB COMMENT='Sản phẩm';

-- ==============================================================
--  NHÓM 3 — TỒN KHO & CHUYỂN KHO
-- ==============================================================

CREATE TABLE inventory (
    inventory_id INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    branch_id    INT UNSIGNED  NOT NULL,
    product_id   INT UNSIGNED  NOT NULL,
    quantity     INT           NOT NULL DEFAULT 0  COMMENT 'Số lượng hiện tại',
    min_quantity INT           NOT NULL DEFAULT 5  COMMENT 'Ngưỡng cảnh báo hàng gần hết',
    updated_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_branch_product (branch_id, product_id),
    CONSTRAINT fk_inv_branch  FOREIGN KEY (branch_id)  REFERENCES branches(branch_id),
    CONSTRAINT fk_inv_product FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB COMMENT='Tồn kho theo chi nhánh';

CREATE TABLE stock_transfers (
    transfer_id    INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    from_branch_id INT UNSIGNED  NOT NULL COMMENT 'Chi nhánh xuất',
    to_branch_id   INT UNSIGNED  NOT NULL COMMENT 'Chi nhánh nhận',
    created_by     INT UNSIGNED  NOT NULL,
    status         ENUM('pending','approved','completed','cancelled') DEFAULT 'pending',
    note           TEXT,
    created_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tf_from    FOREIGN KEY (from_branch_id) REFERENCES branches(branch_id),
    CONSTRAINT fk_tf_to      FOREIGN KEY (to_branch_id)   REFERENCES branches(branch_id),
    CONSTRAINT fk_tf_creator FOREIGN KEY (created_by)     REFERENCES users(user_id)
) ENGINE=InnoDB COMMENT='Phiếu chuyển kho giữa chi nhánh';

CREATE TABLE stock_transfer_items (
    item_id     INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    transfer_id INT UNSIGNED  NOT NULL,
    product_id  INT UNSIGNED  NOT NULL,
    quantity    INT           NOT NULL DEFAULT 1,
    CONSTRAINT fk_tfi_transfer FOREIGN KEY (transfer_id) REFERENCES stock_transfers(transfer_id) ON DELETE CASCADE,
    CONSTRAINT fk_tfi_product  FOREIGN KEY (product_id)  REFERENCES products(product_id)
) ENGINE=InnoDB COMMENT='Chi tiết phiếu chuyển kho';

-- ==============================================================
--  NHÓM 4 — NHẬP HÀNG
-- ==============================================================

CREATE TABLE suppliers (
    supplier_id INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)  NOT NULL,
    phone       VARCHAR(20),
    email       VARCHAR(100),
    address     VARCHAR(255),
    status      ENUM('active','inactive') DEFAULT 'active',
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Nhà cung cấp';

CREATE TABLE purchase_orders (
    po_id        INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    branch_id    INT UNSIGNED  NOT NULL,
    supplier_id  INT UNSIGNED  NOT NULL,
    created_by   INT UNSIGNED  NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    status       ENUM('pending','received','cancelled') DEFAULT 'pending',
    note         TEXT,
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_po_branch   FOREIGN KEY (branch_id)   REFERENCES branches(branch_id),
    CONSTRAINT fk_po_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    CONSTRAINT fk_po_creator  FOREIGN KEY (created_by)  REFERENCES users(user_id)
) ENGINE=InnoDB COMMENT='Đơn nhập hàng';

CREATE TABLE purchase_order_items (
    item_id     INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    po_id       INT UNSIGNED  NOT NULL,
    product_id  INT UNSIGNED  NOT NULL,
    quantity    INT           NOT NULL DEFAULT 1,
    unit_price  DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_price DECIMAL(15,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    CONSTRAINT fk_poi_po      FOREIGN KEY (po_id)       REFERENCES purchase_orders(po_id) ON DELETE CASCADE,
    CONSTRAINT fk_poi_product FOREIGN KEY (product_id)  REFERENCES products(product_id)
) ENGINE=InnoDB COMMENT='Chi tiết đơn nhập hàng';

-- ==============================================================
--  NHÓM 5 — BÁN HÀNG
-- ==============================================================

CREATE TABLE customers (
    customer_id   INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)  NOT NULL,
    phone         VARCHAR(20)   UNIQUE,
    email         VARCHAR(100),
    address       VARCHAR(255),
    loyalty_points INT          DEFAULT 0 COMMENT 'Điểm tích lũy',
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Khách hàng';

CREATE TABLE orders (
    order_id       INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    branch_id      INT UNSIGNED  NOT NULL,
    customer_id    INT UNSIGNED  NULL COMMENT 'NULL = khách vãng lai',
    created_by     INT UNSIGNED  NOT NULL,
    total_amount   DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Tổng trước giảm giá',
    discount       DECIMAL(15,2) NOT NULL DEFAULT 0,
    final_amount   DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Thực thu',
    payment_method ENUM('cash','card','transfer') DEFAULT 'cash',
    status         ENUM('completed','cancelled','refunded') DEFAULT 'completed',
    note           TEXT,
    created_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ord_branch   FOREIGN KEY (branch_id)   REFERENCES branches(branch_id),
    CONSTRAINT fk_ord_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL,
    CONSTRAINT fk_ord_creator  FOREIGN KEY (created_by)  REFERENCES users(user_id)
) ENGINE=InnoDB COMMENT='Đơn bán hàng';

CREATE TABLE order_items (
    item_id     INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED  NOT NULL,
    product_id  INT UNSIGNED  NOT NULL,
    quantity    INT           NOT NULL DEFAULT 1,
    unit_price  DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_price DECIMAL(15,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    CONSTRAINT fk_oi_order   FOREIGN KEY (order_id)   REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB COMMENT='Chi tiết đơn bán hàng';

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================
--  DỮ LIỆU MẪU (SEED DATA)
-- ==============================================================

-- Roles
INSERT INTO roles (role_name, description) VALUES
('admin',   'Quản trị viên — toàn quyền hệ thống'),
('manager', 'Quản lý chi nhánh — quản lý nhánh của mình'),
('staff',   'Nhân viên kho — nhập hàng, chuyển kho'),
('cashier', 'Thu ngân — tạo đơn bán hàng');

-- Branches (manager_id cập nhật sau khi có users)
INSERT INTO branches (name, address, phone) VALUES
('Chi nhánh Quận 1',      '123 Nguyễn Huệ, Q.1, TP.HCM',              '02812345001'),
('Chi nhánh Quận 7',      '456 Nguyễn Thị Thập, Q.7, TP.HCM',         '02812345002'),
('Chi nhánh Bình Thạnh',  '789 Xô Viết Nghệ Tĩnh, Q.BT, TP.HCM',     '02812345003');

-- Users — password_hash là placeholder, PHP sẽ dùng password_hash('Abc@12345', PASSWORD_BCRYPT)
INSERT INTO users (branch_id, role_id, full_name, email, password_hash, phone) VALUES
(1, 1, 'Nguyễn Văn Admin',       'admin@retailchain.vn',       '$2y$10$PLACEHOLDER_HASH_ADMIN', '0901000001'),
(1, 2, 'Trần Thị Lan (QL Q1)',   'manager.q1@retailchain.vn',  '$2y$10$PLACEHOLDER_HASH_MGR1',  '0901000002'),
(2, 2, 'Lê Văn Minh (QL Q7)',    'manager.q7@retailchain.vn',  '$2y$10$PLACEHOLDER_HASH_MGR2',  '0901000003'),
(3, 2, 'Phạm Thị Hoa (QL BT)',   'manager.bt@retailchain.vn',  '$2y$10$PLACEHOLDER_HASH_MGR3',  '0901000004'),
(1, 3, 'Hoàng Văn Kho',          'staff.q1@retailchain.vn',    '$2y$10$PLACEHOLDER_HASH_STF1',  '0901000005'),
(1, 4, 'Ngô Thị Thu Ngân',       'cashier.q1@retailchain.vn',  '$2y$10$PLACEHOLDER_HASH_CAS1',  '0901000006');

-- Gán quản lý cho từng chi nhánh
UPDATE branches SET manager_id = 2 WHERE branch_id = 1;
UPDATE branches SET manager_id = 3 WHERE branch_id = 2;
UPDATE branches SET manager_id = 4 WHERE branch_id = 3;

-- Categories
INSERT INTO categories (name, description) VALUES
('Điện tử',         'Thiết bị điện tử, phụ kiện công nghệ'),
('Thực phẩm',       'Thực phẩm đóng gói, đồ uống'),
('Gia dụng',        'Đồ dùng gia đình'),
('Văn phòng phẩm',  'Dụng cụ văn phòng, học tập');

-- Products
INSERT INTO products (category_id, name, sku, barcode, unit, sell_price, import_price) VALUES
(1, 'Tai nghe Bluetooth XB300',  'SKU-EL-001', '8938505010001', 'cái',  350000, 220000),
(1, 'Cáp sạc Type-C 1m',         'SKU-EL-002', '8938505010002', 'cái',   85000,  45000),
(1, 'Sạc dự phòng 10000mAh',     'SKU-EL-003', '8938505010003', 'cái',  250000, 160000),
(2, 'Nước suối Aquafina 500ml',  'SKU-FD-001', '8934588020001', 'chai',   8000,   5000),
(2, 'Bánh quy Oreo 137g',        'SKU-FD-002', '8934588020002', 'gói',   25000,  18000),
(2, 'Cà phê G7 3in1 (hộp 20 gói)', 'SKU-FD-003', '8934588020003', 'hộp', 65000, 48000),
(3, 'Chổi quét nhà công nghiệp', 'SKU-GD-001', '8934588030001', 'cái',   45000,  28000),
(3, 'Khăn lau đa năng Vileda',   'SKU-GD-002', '8934588030002', 'cái',   55000,  35000),
(4, 'Bút bi Thiên Long TL-027',  'SKU-VP-001', '8934588040001', 'cái',    5000,   3000),
(4, 'Tập học sinh 96 trang',     'SKU-VP-002', '8934588040002', 'quyển', 12000,   8000);

-- Inventory — mỗi sản phẩm tại mỗi chi nhánh
INSERT INTO inventory (branch_id, product_id, quantity, min_quantity) VALUES
-- Chi nhánh 1
(1,1,50,10),(1,2,120,20),(1,3,40,10),(1,4,300,50),(1,5,200,30),(1,6,100,20),(1,7,80,15),(1,8,60,10),(1,9,500,100),(1,10,300,50),
-- Chi nhánh 2
(2,1,30,10),(2,2,80,20),(2,3,25,10),(2,4,200,50),(2,5,150,30),(2,6,70,20),(2,7,60,15),(2,8,40,10),(2,9,350,100),(2,10,200,50),
-- Chi nhánh 3
(3,1,20,10),(3,2,60,20),(3,3,15,10),(3,4,150,50),(3,5,100,30),(3,6,50,20),(3,7,40,15),(3,8,30,10),(3,9,250,100),(3,10,150,50);

-- Suppliers
INSERT INTO suppliers (name, phone, email, address) VALUES
('Cty TNHH Điện tử Sài Gòn',       '02877001001', 'contact@dientusaigon.vn',    '10 Lý Thường Kiệt, Q.10, HCM'),
('Cty CP Thực phẩm Việt',           '02877001002', 'info@thucphamviet.vn',        '20 Cộng Hòa, Tân Bình, HCM'),
('NCC Gia dụng Đại Việt',           '02877001003', 'sales@giadungdaiviet.vn',     '30 Trường Chinh, Tân Phú, HCM'),
('NCC Văn phòng phẩm Thiên Long',   '02877001004', 'order@thienlonggroup.com.vn', '40 Nguyễn Oanh, Gò Vấp, HCM');

-- Customers
INSERT INTO customers (full_name, phone, email, loyalty_points) VALUES
('Nguyễn Thị Lan',   '0901111001', 'lan.nguyen@gmail.com',  150),
('Trần Văn Bình',    '0901111002', 'binh.tran@gmail.com',    80),
('Lê Hoàng Nam',     '0901111003', NULL,                      0),
('Phạm Thị Cúc',     '0901111004', 'cuc.pham@gmail.com',    220),
('Đỗ Minh Tuấn',     '0901111005', NULL,                     45);

-- Purchase Orders mẫu
INSERT INTO purchase_orders (branch_id, supplier_id, created_by, total_amount, status, note) VALUES
(1, 1, 5, 2640000, 'received', 'Nhập hàng điện tử tháng 5'),
(2, 2, 3, 1150000, 'pending',  'Đơn nhập thực phẩm chờ duyệt');

INSERT INTO purchase_order_items (po_id, product_id, quantity, unit_price) VALUES
(1, 1, 10, 220000),
(1, 2, 20,  45000),
(2, 4, 100,  5000),
(2, 5,  50, 18000);

-- Stock Transfers mẫu
INSERT INTO stock_transfers (from_branch_id, to_branch_id, created_by, status, note) VALUES
(1, 3, 5, 'completed', 'Bổ sung tai nghe cho chi nhánh Bình Thạnh'),
(2, 1, 3, 'pending',   'Chuyển bút bi từ Q7 sang Q1');

INSERT INTO stock_transfer_items (transfer_id, product_id, quantity) VALUES
(1, 1, 10),
(1, 2, 20),
(2, 9, 100);

-- Orders mẫu
INSERT INTO orders (branch_id, customer_id, created_by, total_amount, discount, final_amount, payment_method, status) VALUES
(1, 1, 6, 435000,     0, 435000, 'cash',     'completed'),
(1, 2, 6, 250000, 25000, 225000, 'card',     'completed'),
(1, NULL, 6, 13000,   0,  13000, 'cash',     'completed');

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 350000),
(1, 2, 1,  85000),
(2, 3, 1, 250000),
(3, 4, 1,   8000),
(3, 9, 1,   5000);

-- ==============================================================
--  VIEWS HỖ TRỢ BÁO CÁO
-- ==============================================================

-- View: Tồn kho kèm cảnh báo
CREATE OR REPLACE VIEW v_inventory_status AS
SELECT
    b.name          AS branch_name,
    p.sku,
    p.name          AS product_name,
    c.name          AS category_name,
    i.quantity,
    i.min_quantity,
    CASE
        WHEN i.quantity = 0             THEN 'Hết hàng'
        WHEN i.quantity <= i.min_quantity THEN 'Sắp hết'
        ELSE 'Còn hàng'
    END             AS stock_status,
    i.updated_at
FROM inventory i
JOIN branches  b ON b.branch_id  = i.branch_id
JOIN products  p ON p.product_id = i.product_id
JOIN categories c ON c.category_id = p.category_id;

-- View: Doanh thu theo chi nhánh
CREATE OR REPLACE VIEW v_revenue_by_branch AS
SELECT
    b.branch_id,
    b.name        AS branch_name,
    COUNT(o.order_id) AS total_orders,
    SUM(o.final_amount) AS total_revenue,
    DATE(o.created_at)  AS order_date
FROM orders o
JOIN branches b ON b.branch_id = o.branch_id
WHERE o.status = 'completed'
GROUP BY b.branch_id, b.name, DATE(o.created_at);

-- View: Top sản phẩm bán chạy
CREATE OR REPLACE VIEW v_top_products AS
SELECT
    p.product_id,
    p.sku,
    p.name          AS product_name,
    c.name          AS category_name,
    SUM(oi.quantity) AS total_sold,
    SUM(oi.total_price) AS total_revenue
FROM order_items oi
JOIN orders   o ON o.order_id   = oi.order_id
JOIN products p ON p.product_id = oi.product_id
JOIN categories c ON c.category_id = p.category_id
WHERE o.status = 'completed'
GROUP BY p.product_id, p.sku, p.name, c.name
ORDER BY total_sold DESC;

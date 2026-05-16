-- Chạy một lần sau khi import retail_chain.sql (seed dùng placeholder hash)
-- Mật khẩu test cho tất cả tài khoản: Abc@12345

USE retail_chain;

UPDATE users SET password_hash = '$2y$10$qqcSFUv5EgyrQQe9jB.qDesGSfElrtFaDlj2JxyMvK4UJAlIL/HmS'
WHERE email IN (
    'admin@retailchain.vn',
    'manager.q1@retailchain.vn',
    'manager.q7@retailchain.vn',
    'manager.bt@retailchain.vn',
    'staff.q1@retailchain.vn',
    'cashier.q1@retailchain.vn'
);

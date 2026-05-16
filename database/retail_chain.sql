-- Retail Chain — schema khởi tạo
-- Thay thế hoặc bổ sung bằng file SQL đầy đủ theo thiết kế của nhóm.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `retail_chain`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `retail_chain`;

-- Ví dụ bảng tối thiểu (có thể xóa khi đã có script đầy đủ)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

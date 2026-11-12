-- ============================================================================
-- database/tai-khoan.sql
-- Phân quyền CSDL theo nguyên tắc đặc quyền tối thiểu (Principle of Least Privilege).
-- Tạo tài khoản nhom14_app chỉ có quyền DML (SELECT, INSERT, UPDATE, DELETE).
-- Cấm hoàn toàn các quyền DDL (DROP, ALTER, CREATE, TRUNCATE) để phòng chống phá hoại CSDL.
-- Chạy MỘT lần bằng tài khoản root trong phpMyAdmin hoặc MySQL CLI.
-- ============================================================================

CREATE USER IF NOT EXISTS 'nhom14_app'@'localhost'
  IDENTIFIED BY 'thay-bang-mat-khau-dai-ngau-nhien';

GRANT SELECT, INSERT, UPDATE, DELETE
  ON ltweb_nhom14.* TO 'nhom14_app'@'localhost';

FLUSH PRIVILEGES;

-- Kiểm tra quyền hạn đã cấp:
-- SHOW GRANTS FOR 'nhom14_app'@'localhost';
--
-- Khi chạy bằng tài khoản nhom14_app, các thao tác DDL sẽ bị MySQL chặn lại:
-- DROP TABLE su_kien;   --> ERROR 1142 (42000): DROP command denied to user 'nhom14_app'@'localhost'
-- CREATE TABLE test(id INT); --> ERROR 1142 (42000): CREATE command denied to user 'nhom14_app'@'localhost'

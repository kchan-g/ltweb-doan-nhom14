-- ============================================================================
-- database/schema.sql
-- Kịch bản thiết kế Cơ sở dữ liệu quan hệ cho website UniEvent (Nhóm 14)
-- Học phần: Thiết kế và Lập trình Web - Học kỳ 1, Năm học 2026 - 2027
-- CSDL: ltweb_nhom14 | Bảng mã: utf8mb4 | Collation: utf8mb4_unicode_ci
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS ltweb_nhom14;
CREATE DATABASE ltweb_nhom14
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ltweb_nhom14;

-- ----------------------------------------------------------------------------
-- 1. BẢNG DANH MỤC SỰ KIỆN (danh_muc)
-- Quản lý các nhóm sự kiện trong trường đại học.
-- ----------------------------------------------------------------------------
CREATE TABLE danh_muc (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ten       VARCHAR(100) NOT NULL,
  slug      VARCHAR(100) NOT NULL UNIQUE,
  mo_ta     TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. BẢNG SỰ KIỆN (su_kien / san_pham)
-- Thực thể chính của đồ án: các sự kiện, hội thảo, cuộc thi, văn nghệ...
-- Ràng buộc CHECK: gia >= 0, ton_kho >= 0.
-- Khóa ngoại danh_muc_id không ghi ON DELETE để chặn xóa danh mục còn sự kiện (lỗi 1451).
-- ----------------------------------------------------------------------------
CREATE TABLE su_kien (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  danh_muc_id   INT UNSIGNED NOT NULL,
  ten           VARCHAR(200) NOT NULL,
  gia           INT UNSIGNED NOT NULL DEFAULT 0 CHECK (gia >= 0),
  ton_kho       INT UNSIGNED NOT NULL DEFAULT 0 CHECK (ton_kho >= 0),
  dia_diem      VARCHAR(255) NOT NULL,
  thoi_gian     DATETIME NOT NULL,
  diem_ren_luyen INT UNSIGNED NOT NULL DEFAULT 0,
  dien_gia      VARCHAR(255) NULL,
  anh           VARCHAR(500) NULL,
  mo_ta_ngan    TEXT NULL,
  mo_ta_chi_tiet MEDIUMTEXT NULL,
  video         VARCHAR(255) NULL,
  ngay_tao      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (danh_muc_id) REFERENCES danh_muc(id),
  INDEX idx_su_kien_dm_gia (danh_muc_id, gia),
  INDEX idx_su_kien_thoi_gian (thoi_gian)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. BẢNG NGƯỜI DÙNG (nguoi_dung)
-- Quản lý tài khoản quản trị viên và sinh viên / khách hàng.
-- Mật khẩu luôn lưu chuỗi băm 60 ký tự bằng password_hash().
-- ----------------------------------------------------------------------------
CREATE TABLE nguoi_dung (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(150) NOT NULL UNIQUE,
  ho_ten        VARCHAR(100) NOT NULL,
  mat_khau      VARCHAR(255) NOT NULL,
  vai_tro       ENUM('quan_tri', 'khach_hang') NOT NULL DEFAULT 'khach_hang',
  so_dien_thoai VARCHAR(20) NULL,
  ngay_tao      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. BẢNG ĐƠN ĐẶT VÉ (don_hang)
-- Quản lý giao dịch đăng ký vé / giữ chỗ sự kiện trong phiên hoặc thanh toán.
-- ----------------------------------------------------------------------------
CREATE TABLE don_hang (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nguoi_dung_id INT UNSIGNED NOT NULL,
  ma_don        VARCHAR(30) NOT NULL UNIQUE,
  ngay_dat      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tong_tien     INT UNSIGNED NOT NULL DEFAULT 0,
  trang_thai    ENUM('cho_xac_nhan', 'da_thanh_toan', 'hoan_thanh', 'da_huy') NOT NULL DEFAULT 'cho_xac_nhan',
  ghi_chu       TEXT NULL,
  FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE RESTRICT,
  INDEX idx_don_hang_nguoi_dung (nguoi_dung_id, ngay_dat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. BẢNG CHI TIẾT ĐƠN ĐẶT VÉ (chi_tiet_don)
-- Quan hệ n - n giữa don_hang và su_kien. Khóa chính ghép (don_hang_id, su_kien_id).
-- Cột don_gia: giá tại thời điểm đặt (cố ý lưu lặp có chủ đích để bảo toàn giá trị lịch sử).
-- FK su_kien_id ON DELETE RESTRICT để chặn xóa sự kiện đã có trong đơn hàng (lỗi 1451).
-- ----------------------------------------------------------------------------
CREATE TABLE chi_tiet_don (
  don_hang_id   INT UNSIGNED NOT NULL,
  su_kien_id    INT UNSIGNED NOT NULL,
  so_luong      SMALLINT UNSIGNED NOT NULL CHECK (so_luong > 0),
  don_gia       INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (don_hang_id, su_kien_id),
  FOREIGN KEY (don_hang_id) REFERENCES don_hang(id) ON DELETE CASCADE,
  FOREIGN KEY (su_kien_id) REFERENCES su_kien(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. BẢNG LIÊN HỆ & PHẢN HỒI (lien_he)
-- Tiếp nhận ý kiến phản hồi từ người dùng hoặc đối tác, hỗ trợ đính kèm tệp ảnh.
-- ----------------------------------------------------------------------------
CREATE TABLE lien_he (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ho_ten        VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  so_dien_thoai VARCHAR(20) NOT NULL,
  khoa_vien     VARCHAR(100) NULL,
  noi_dung      TEXT NOT NULL,
  anh           VARCHAR(500) NULL,
  ngay_tao      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PHẦN B: CÁC BẢNG TRANG CÁ NHÂN VÀ THÀNH VIÊN
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 7. BẢNG THÀNH VIÊN NHÓM (thanh_vien)
-- Quản lý 5 thành viên nhóm 14 và liên kết với tài khoản hệ thống (nguoi_dung).
-- ----------------------------------------------------------------------------
CREATE TABLE thanh_vien (
  mssv          CHAR(10) PRIMARY KEY,
  ho_ten        VARCHAR(100) NOT NULL,
  nguoi_dung_id INT UNSIGNED NULL UNIQUE,
  FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 8. BẢNG SỔ LƯU BÚT TRANG CÁ NHÂN (tv_luu_but)
-- Cho phép khách ghé thăm để lại lời nhắn, hỗ trợ phân trang và chủ trang xóa.
-- ----------------------------------------------------------------------------
CREATE TABLE tv_luu_but (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv      CHAR(10) NOT NULL,
  ten_khach VARCHAR(50) NOT NULL,
  vai_tro   VARCHAR(50) NOT NULL DEFAULT 'Khách ghé thăm',
  loi_nhan  VARCHAR(500) NOT NULL,
  thoi_gian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE,
  INDEX idx_tv_luubut_mssv (mssv, thoi_gian)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 9. BẢNG BỘ ĐẾM LƯỢT XEM THEO NGÀY (tv_luot_xem)
-- Lưu thống kê lượt xem theo ngày (INSERT ... ON DUPLICATE KEY UPDATE).
-- ----------------------------------------------------------------------------
CREATE TABLE tv_luot_xem (
  mssv      CHAR(10) NOT NULL,
  ngay      DATE NOT NULL,
  so_luot   INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (mssv, ngay),
  FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 10. BẢNG ĐÁNH GIÁ NĂNG LỰC CÁ NHÂN (tv_danh_gia)
-- Dành cho chức năng đánh giá kỹ năng trên trang của thành viên Phan Nhuận.
-- ----------------------------------------------------------------------------
CREATE TABLE tv_danh_gia (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv      CHAR(10) NOT NULL,
  ho_ten    VARCHAR(100) NOT NULL,
  ky_nang   VARCHAR(100) NOT NULL,
  so_sao    TINYINT UNSIGNED NOT NULL CHECK (so_sao BETWEEN 1 AND 5),
  nhan_xet  TEXT NULL,
  ngay_tao  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 11. BẢNG LỊCH SỬ TÍNH ĐIỂM HỌC PHẦN (tv_diem_hoc_phan)
-- Dành cho chức năng tính điểm học phần trên trang của thành viên Lê Phú Đạt.
-- ----------------------------------------------------------------------------
CREATE TABLE tv_diem_hoc_phan (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv      CHAR(10) NOT NULL,
  ten_mon   VARCHAR(150) NOT NULL,
  diem_a1   DECIMAL(3, 1) NOT NULL,
  diem_a2   DECIMAL(3, 1) NOT NULL,
  diem_a3   DECIMAL(3, 1) NOT NULL,
  diem_tb   DECIMAL(3, 1) NOT NULL,
  diem_chu  VARCHAR(5) NOT NULL,
  ngay_tinh DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 12. BẢNG DỰ TOÁN PHÁT TRIỂN WEB (tv_du_toan_web)
-- Dành cho chức năng ước tính chi phí trên trang của thành viên Nguyễn Thị Kiều Trang.
-- ----------------------------------------------------------------------------
CREATE TABLE tv_du_toan_web (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv          CHAR(10) NOT NULL,
  loai_website  VARCHAR(100) NOT NULL,
  so_trang      INT UNSIGNED NOT NULL,
  goi_ui        VARCHAR(100) NOT NULL,
  tong_chi_phi  INT UNSIGNED NOT NULL,
  tien_do_ngay  INT UNSIGNED NOT NULL,
  ngay_tao      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 13. BẢNG ĐẶT LỊCH TƯ VẤN HỌC TẬP & KẾT NỐI (tv_tu_van)
-- Dành cho chức năng đặt lịch tư vấn trên trang của thành viên Nguyễn Hoài Bảo.
-- ----------------------------------------------------------------------------
CREATE TABLE tv_tu_van (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv          CHAR(10) NOT NULL,
  ho_ten        VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  chu_de        VARCHAR(100) NOT NULL,
  noi_dung      TEXT NOT NULL,
  trang_thai    VARCHAR(50) NOT NULL DEFAULT 'chua_xu_ly',
  ngay_gui      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 14. BẢNG ĐĂNG KÝ BẢN TIN CÔNG NGHỆ (tv_ban_tin)
-- Dành cho chức năng đăng ký bản tin trên trang của thành viên Nguyễn Thị Kiều Trang.
-- ----------------------------------------------------------------------------
CREATE TABLE tv_ban_tin (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv          CHAR(10) NOT NULL,
  ho_ten        VARCHAR(100) NOT NULL DEFAULT 'Ẩn danh',
  email         VARCHAR(150) NOT NULL,
  chu_de        VARCHAR(100) NOT NULL,
  ngay_dang_ky  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

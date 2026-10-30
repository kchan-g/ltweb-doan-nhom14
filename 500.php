<?php
/**
 * 500.php
 * Trang thông báo lỗi 500 (Internal Server Error - Sự cố máy chủ).
 *
 * QUY TẮC AN TOÀN BẮT BUỘC:
 * Trang này KHÔNG ĐƯỢC PHÉP truy vấn CSDL hay gọi bất kỳ hàm nào liên quan đến db().
 * Khi chính CSDL gặp sự cố, trang lỗi vẫn phải hiển thị độc lập, trang nhã,
 * không làm lộ đường dẫn nội bộ máy chủ khi chạy ở môi trường sản xuất.
 */

declare(strict_types=1);

http_response_code(500);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>500 - Sự cố máy chủ nội bộ | UniEvent</title>
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="stylesheet" href="css/01-bien.css">
  <link rel="stylesheet" href="css/02-chuan-hoa.css">
  <link rel="stylesheet" href="css/03-bo-cuc.css">
  <link rel="stylesheet" href="css/04-thanh-phan.css">
  <link rel="stylesheet" href="css/05-tien-ich.css">
</head>
<body class="trang trang-loi">
  <header class="vung-dau">
    <div class="container site-header__inner" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 0;">
      <a href="index.php" class="thuong-hieu" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: inherit;">
        <img src="images/logo.png" alt="Logo UniEvent" width="44" height="44" />
        <div>
          <div class="thuong-hieu__ten" style="font-weight: 700; font-size: 1.25rem;">UniEvent</div>
          <div class="thuong-hieu__mota" style="font-size: 0.8rem; color: #64748b;">Hệ thống Quản lý Sự kiện ĐHSP Đà Nẵng</div>
        </div>
      </a>
      <a href="index.php" class="nut-bam nut-phu" style="padding: 0.4rem 0.8rem; font-size: 0.9rem;">Về Trang chủ</a>
    </div>
  </header>

  <main class="vung-chinh noi-dung-chinh container py-5 text-center" style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
    <div style="max-width: 600px; margin: 40px auto; padding: 35px; background: var(--nen-surface, #ffffff); border-radius: var(--radius-vua, 12px); border: 1px solid var(--border-nhat, #e2e8f0); box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.1));">
      <div style="font-size: 4rem; line-height: 1; margin-bottom: 1rem;">⚠️ 500</div>
      <h1 class="h2 mb-3" style="color: var(--mau-chinh-toi, #1e293b); font-size: 1.75rem; font-weight: 700;">Đã xảy ra sự cố kỹ thuật</h1>
      <p class="text-phu mb-4" style="color: #64748b; line-height: 1.6;">
        Hệ thống UniEvent đang gặp gián đoạn kết nối tạm thời. Chi tiết sự cố đã được tự động lưu vào tệp nhật ký hệ thống (<code>logs/php-error.log</code>) để kỹ thuật viên xử lý.
      </p>
      <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
        <a href="index.php" class="nut-bam nut-chinh" style="padding: 0.6rem 1.2rem; background: var(--mau-chinh); color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;">Tải lại trang chủ</a>
        <a href="lien-he.php" class="nut-bam nut-phu" style="padding: 0.6rem 1.2rem; background: var(--nen-section); color: var(--chu-chinh); border: 1px solid var(--border-nhat); text-decoration: none; border-radius: 6px;">Báo cáo sự cố</a>
      </div>
    </div>
  </main>

  <footer class="vung-chan" style="background: #0f172a; color: #94a3b8; padding: 2rem 0; text-align: center; font-size: 0.875rem;">
    <div class="container">
      <p style="margin: 0;">&copy; <?= date('Y') ?> UniEvent - Trường Đại học Sư phạm, Đại học Đà Nẵng. Bản quyền thuộc về Nhóm 14.</p>
    </div>
  </footer>
</body>
</html>

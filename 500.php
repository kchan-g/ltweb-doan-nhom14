<?php
/**
 * 500.php
 * Trang thông báo lỗi 500 (Lỗi máy chủ nội bộ).
 * Luôn trả về đúng mã trạng thái HTTP 500 Internal Server Error,
 * không làm lộ đường dẫn nội bộ máy chủ khi chạy ở môi trường sản xuất (MOI_TRUONG = 'prod').
 */

declare(strict_types=1);

if (!defined('MOI_TRUONG')) {
    require_once __DIR__ . '/inc/config.php';
}

http_response_code(500);

$tieuDe = '500 - Lỗi máy chủ nội bộ';
$trang  = '500';

require __DIR__ . '/inc/header.php';
?>
<main class="vung-chinh noi-dung-chinh container py-5 text-center">
  <div style="max-width: 600px; margin: 40px auto; padding: 30px; background: var(--nen-surface); border-radius: var(--radius-vua); border: 1px solid var(--border-nhat); box-shadow: var(--shadow-sm);">
    <div style="font-size: 4rem; line-height: 1; margin-bottom: 1rem;">⚠️ 500</div>
    <h1 class="h2 mb-3" style="color: var(--mau-chinh-toi);">Đã xảy ra sự cố kỹ thuật</h1>
    <p class="text-phu mb-4">
      Hệ thống UniEvent đang gặp gián đoạn tạm thời. Chi tiết lỗi đã được tự động lưu vào tệp nhật ký hệ thống. Vui lòng thử lại sau ít phút hoặc liên hệ Ban quản trị để được hỗ trợ.
    </p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="index.php" class="nut-bam nut-chinh">Về Trang chủ</a>
      <a href="lien-he.php" class="nut-bam nut-phu">Báo cáo sự cố</a>
    </div>
  </div>
</main>
<?php require __DIR__ . '/inc/footer.php'; ?>

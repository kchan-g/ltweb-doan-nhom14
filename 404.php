<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

http_response_code(404);

$tieuDe = '404 - Không tìm thấy trang';
$trang  = '404';

require __DIR__ . '/inc/header.php';
?>
<main class="vung-chinh noi-dung-chinh container py-5 text-center">
  <div style="max-width: 580px; margin: 40px auto; padding: 48px 32px; background: var(--nen-surface); border-radius: 20px; border: 1px solid var(--border-nhat); box-shadow: var(--shadow-card);">
    <div style="font-size: 4rem; line-height: 1; margin-bottom: 1.5rem;">🔍 404</div>
    <h1 class="h2 mb-3 font-hien-thi fw-800" style="color: var(--mau-chinh-toi);">Không tìm thấy trang yêu cầu</h1>
    <p class="text-phu mb-4" style="line-height: 1.7; font-size: 1rem;">
      Sự kiện hoặc liên kết bạn đang tìm kiếm không tồn tại, đã bị xóa hoặc đường dẫn (?id=...) không chính xác.
    </p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="index.php" class="nut-bam nut-nhan py-2 px-4" style="font-weight:700;">Quay lại Trang chủ</a>
      <a href="danh-sach.php" class="nut-bam nut-phu py-2 px-4">Xem danh sách sự kiện</a>
    </div>
  </div>
</main>
<?php require __DIR__ . '/inc/footer.php'; ?>

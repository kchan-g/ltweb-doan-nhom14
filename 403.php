<?php
/**
 * 403.php
 * Trang thông báo lỗi 403 (Forbidden - Truy cập bị từ chối).
 * Hiển thị khi người dùng không đủ đặc quyền truy cập trang quản trị hoặc tài nguyên bị bảo vệ.
 */

declare(strict_types=1);

if (!defined('MOI_TRUONG')) {
    require_once __DIR__ . '/inc/config.php';
}

http_response_code(403);

$tieuDe = '403 - Truy cập bị từ chối';
$trang  = '403';
$bodyClass = 'trang-loi';

require __DIR__ . '/inc/header.php';
?>
<main class="vung-chinh noi-dung-chinh container py-5 text-center">
  <div style="max-width: 600px; margin: 40px auto; padding: 30px; background: var(--nen-surface); border-radius: var(--radius-vua); border: 1px solid var(--border-nhat); box-shadow: var(--shadow-sm);">
    <div style="font-size: 4rem; line-height: 1; margin-bottom: 1rem;">⛔ 403</div>
    <h1 class="h2 mb-3" style="color: var(--mau-chinh-toi);">Truy Cập Bị Từ Chối</h1>
    <p class="text-phu mb-4">
      Bạn không có quyền truy cập vào tài nguyên hoặc trang quản trị này. Nếu bạn là Quản trị viên, vui lòng đăng nhập bằng tài khoản có đặc quyền <code>quan_tri</code>.
    </p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="index.php" class="nut-bam nut-chinh">Về Trang chủ</a>
      <a href="dang-nhap.php" class="nut-bam nut-phu">Đổi tài khoản đăng nhập</a>
    </div>
  </div>
</main>
<?php require __DIR__ . '/inc/footer.php'; ?>

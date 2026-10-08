<?php
// chi-tiet.php — Chi tiết sự kiện & Ghi nhận Cookie an toàn
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

// 1. Kiểm tra tham số ?id= phải là số nguyên dương
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$kho = new KhoSanPham(__DIR__ . '/data/su-kien.json');
$sp = ($id !== false && $id > 0) ? $kho->timTheoId($id) : null;

// Nếu không tìm thấy sự kiện, trả về HTTP 404 và nạp trang 404.php dùng chung
if (!$sp) {
    http_response_code(404);
    $tieuDe = 'Không tìm thấy sự kiện - UniEvent';
    $trang  = '404';
    require_once __DIR__ . '/404.php';
    exit;
}

// 2. Xử lý Cookie "da_xem" (Ghi trước khi xuất HTML)
$cookieRaw = $_COOKIE['da_xem'] ?? '';
$danhSachCu = array_filter(array_map('intval', explode(',', $cookieRaw)));

// Đưa ID hiện tại lên đầu mảng, loại bỏ trùng lặp và lấy tối đa 4 ID mới nhất
$danhSachMoi = array_values(array_unique([$sp->id, ...$danhSachCu]));
$danhSachLuu = array_slice($danhSachMoi, 0, 4);

// Thiết lập cookie an toàn: HttpOnly, SameSite=Lax, hạn 30 ngày
setcookie('da_xem', implode(',', $danhSachLuu), [
    'expires'  => time() + 30 * 24 * 3600,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);

$gio = new GioHang();
$tieuDe = $sp->ten . ' - UniEvent';
$trang  = 'chi-tiet';
require_once __DIR__ . '/inc/header.php';
?>

<main class="vung-chinh container py-4" style="max-width: 960px; margin: 0 auto;">
    <nav aria-label="breadcrumb" class="mb-4">
        <a href="index.php" class="text-decoration-none">Trang chủ</a> &raquo; 
        <a href="danh-sach.php" class="text-decoration-none">Sự kiện</a> &raquo; 
        <span class="text-muted"><?= e($sp->ten) ?></span>
    </nav>

    <article class="the-tin p-4 bg-white rounded shadow-sm border mb-4">
        <div class="row g-4">
            <div class="col-md-5">
                <img src="<?= e($sp->hinhAnh ?? 'images/hero-image.jpg') ?>" 
                     alt="<?= e($sp->ten) ?>" 
                     class="img-fluid rounded w-100 object-fit-cover shadow-sm" style="max-height: 320px;">
            </div>
            <div class="col-md-7">
                <span class="<?= e($sp->badgeClass ?? 'badge') ?> mb-2 d-inline-block"><?= e($sp->tenDanhMuc ?? 'Sự kiện') ?></span>
                <h1 class="h3 fw-bold text-primary mb-3"><?= e($sp->ten) ?></h1>
                
                <p class="mb-2"><strong>Thời gian:</strong> <?= e($sp->thoiGian ?? '') ?>, <?= e($sp->ngay ?? '') ?></p>
                <p class="mb-2"><strong>Địa điểm:</strong> <?= e($sp->diaDiem ?? '') ?></p>
                <p class="mb-2"><strong>Quyền lợi:</strong> +<?= (int)($sp->diemRenLuyen ?? 0) ?> điểm rèn luyện</p>
                <p class="mb-3"><strong>Chi phí:</strong> 
                    <span class="text-danger fw-bold"><?= ($sp->giaVe ?? 0) > 0 ? vnd($sp->giaVe) : 'Miễn phí' ?></span>
                </p>

                <hr class="my-3">

                <!-- Biểu mẫu POST đăng ký vé / thêm vào giỏ -->
                <form action="gio-hang.php" method="POST" class="d-flex align-items-center gap-3">
                    <input type="hidden" name="hanh_dong" value="them">
                    <input type="hidden" name="id" value="<?= e($sp->id) ?>">
                    
                    <label for="so_luong" class="fw-bold mb-0">Số vé:</label>
                    <input type="number" id="so_luong" name="so_luong" value="1" min="1" max="5" 
                           class="form-control text-center" style="width: 70px;" required>
                    
                    <button type="submit" class="nut-bam nut-nhan nut-nho">
                        Đăng ký tham gia
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-4 pt-3 border-top">
            <h2 class="h5 fw-bold mb-2">Chi tiết nội dung sự kiện</h2>
            <div class="text-secondary lh-lg">
                <?= nl2br(e($sp->moTaChiTiet ?? $sp->moTaNgan ?? 'Nội dung sự kiện đang được cập nhật.')) ?>
            </div>
        </div>
    </article>
</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

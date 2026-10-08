<?php
// quan-tri.php — Bảng quản trị danh sách phản hồi liên hệ (STT 9 - Trang)
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/bao-ve.php'; // Kiểm tra quyền admin

use App\Data\KhoLienHe;

$khoLH = new KhoLienHe(__DIR__ . '/storage/lien-he.jsonl');
$danhSach = $khoLH->tatCa();

$tieuDe = 'Quản trị hệ thống - UniEvent';
$trang  = 'quan-tri';
require __DIR__ . '/inc/header.php';
?>

<main class="vung-chinh container py-4" style="max-width: 1060px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem; flex-wrap: wrap; gap: 10px;">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: var(--mau-chinh, #123b6d);">Hộp thư Phản hồi &amp; Liên hệ</h1>
            <p class="text-muted small mb-0">
                Đang đăng nhập: <strong><?= e($_SESSION['nguoi_dung']['ten']) ?></strong> (<?= e($_SESSION['nguoi_dung']['email']) ?>)
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="index.php" class="nut-bam nut-phu nut-nho" style="text-decoration:none;">Về trang chủ</a>
            <a href="dang-xuat.php" class="nut-bam nut-nho" style="color: #b91c1c; border-color: #fca5a5; background: #fff5f5; text-decoration: none;">
                Đăng xuất
            </a>
        </div>
    </div>

    <div class="card shadow-sm border rounded overflow-hidden bg-white">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 12px; width: 16%;">Thời gian</th>
                        <th style="padding: 12px; width: 22%;">Người gửi</th>
                        <th style="padding: 12px; width: 44%;">Nội dung liên hệ</th>
                        <th style="padding: 12px; width: 18%; text-align: center;">Tệp đính kèm</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($danhSach)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 32px; color: #64748b;">
                                📭 Hộp thư hiện chưa có liên hệ nào được gửi tới.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($danhSach as $lh): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px; font-size: 0.85rem; color: #64748b;">
                                    <?= e($lh['thoiGian'] ?? $lh['thoi_gian'] ?? 'Chưa rõ') ?>
                                </td>
                                <td style="padding: 12px;">
                                    <div style="font-weight: 600; color: #1e293b;"><?= e($lh['hoTen'] ?? $lh['ho_ten'] ?? 'Khách') ?></div>
                                    <div style="font-size: 0.85rem; color: #64748b;">📧 <?= e($lh['email'] ?? '') ?></div>
                                    <?php if (!empty($lh['sdt'])): ?>
                                        <div style="font-size: 0.85rem; color: #64748b;">📞 <?= e($lh['sdt']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; color: #334155; line-height: 1.5;">
                                    <?= nl2br(e($lh['noiDung'] ?? $lh['noi_dung'] ?? '')) ?>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <?php 
                                        $tepAnh = $lh['anh'] ?? $lh['tep_tin'] ?? null;
                                        if (!empty($tepAnh) && file_exists(__DIR__ . '/uploads/' . $tepAnh)): 
                                    ?>
                                        <a href="uploads/<?= e($tepAnh) ?>" target="_blank" class="nut-bam nut-nho" style="font-size: 0.8rem; padding: 4px 8px;">
                                            Xem ảnh
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 0.85rem;">Không có</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
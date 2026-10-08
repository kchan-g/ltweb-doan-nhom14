<?php
/**
 * quan-tri.php
 * Trang quản trị tiếp nhận phản hồi & liên hệ sinh viên.
 * Yêu cầu đăng nhập hợp lệ qua inc/bao-ve.php.
 * Hiển thị toàn bộ tin nhắn liên hệ từ storage/lien-he.jsonl (mới nhất lên đầu)
 * kèm ảnh minh chứng đã tải lên, toàn bộ dữ liệu in ra qua hàm e().
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/bao-ve.php'; // Bắt buộc đăng nhập

use App\Data\KhoLienHe;

$khoLienHe = new KhoLienHe(__DIR__ . '/storage/lien-he.jsonl');
$danhSach = $khoLienHe->tatCa();
$tongSo = count($danhSach);

$tieuDe = 'Bảng điều khiển Quản trị - UniEvent';
$trang  = 'quan-tri';

require __DIR__ . '/inc/header.php';
?>

<main class="vung-chinh noi-dung-chinh container py-4" id="noi-dung-chinh">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <h1 class="tieu-de-muc mb-1">Quản lý Liên hệ &amp; Phản hồi</h1>
      <p class="section-head__mota mb-0">
        Xem danh sách phản hồi từ sinh viên gửi qua biểu mẫu liên-he.php (Lưu trữ an toàn tại <code>storage/lien-he.jsonl</code>).
      </p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge badge--xanh" style="font-size:0.95rem; padding:8px 12px;">
        Tổng cộng: <?= $tongSo ?> liên hệ
      </span>
      <a href="dang-xuat.php" class="nut-bam nut-canh-bao nut-nho">Đăng xuất</a>
    </div>
  </div>

  <?php if ($tongSo === 0): ?>
    <div class="text-center p-5 my-4" style="background:var(--nen-surface); border-radius:var(--radius-vua); border:1px dashed var(--border-nhat);">
      <div style="font-size:3rem; margin-bottom:1rem;">📭</div>
      <h2 class="h4" style="color:var(--mau-chinh-toi);">Chưa có liên hệ nào</h2>
      <p class="text-phu mb-3">Hiện tại chưa có sinh viên hoặc ban tổ chức nào gửi phản hồi qua biểu mẫu liên hệ.</p>
      <a href="lien-he.php" target="_blank" class="nut-bam nut-chinh">Mở trang gửi thử liên hệ</a>
    </div>
  <?php else: ?>
    <div class="bang-wrapper shadow-sm mb-4">
      <table>
        <caption>Danh sách toàn bộ phản hồi từ sinh viên (Mới nhất xếp trước)</caption>
        <thead>
          <tr>
            <th scope="col" style="width:130px;">Thời gian</th>
            <th scope="col" style="width:160px;">Họ tên</th>
            <th scope="col" style="width:180px;">Liên hệ</th>
            <th scope="col" style="width:140px;">Khoa / Viện</th>
            <th scope="col">Nội dung phản hồi</th>
            <th scope="col" style="width:110px; text-align:center;">Ảnh đính kèm</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($danhSach as $lh): ?>
            <tr>
              <td class="fs-meta text-phu" style="white-space:nowrap;">
                <?= e($lh['thoiGian'] ?? 'N/A') ?>
              </td>
              <td>
                <strong class="text-chinh"><?= e($lh['hoTen'] ?? '') ?></strong>
              </td>
              <td style="font-size:0.875rem;">
                <div>📧 <a href="mailto:<?= e($lh['email'] ?? '') ?>"><?= e($lh['email'] ?? '') ?></a></div>
                <div>📞 <a href="tel:<?= e($lh['sdt'] ?? '') ?>"><?= e($lh['sdt'] ?? '') ?></a></div>
              </td>
              <td class="fs-meta">
                <?= e($lh['khoa'] ?? 'Không ghi rõ') ?>
              </td>
              <td style="line-height:1.6; word-break:break-word;">
                <?= nl2br(e($lh['noiDung'] ?? '')) ?>
              </td>
              <td style="text-align:center;">
                <?php if (!empty($lh['anh']) && file_exists(__DIR__ . '/uploads/' . $lh['anh'])): ?>
                  <a href="uploads/<?= e($lh['anh']) ?>" target="_blank" title="Xem ảnh gốc">
                    <img
                      src="uploads/<?= e($lh['anh']) ?>"
                      alt="Ảnh đính kèm từ <?= e($lh['hoTen'] ?? '') ?>"
                      width="50"
                      height="50"
                      style="border-radius:4px; object-fit:cover; border:1px solid var(--border-nhat);"
                    />
                  </a>
                <?php else: ?>
                  <span class="text-phu fs-meta">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>

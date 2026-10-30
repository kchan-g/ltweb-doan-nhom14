<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/don-hang.php';

use App\Data\KhoSuKien;
use App\Services\GioHang;

yeuCauDangNhap();

$kho = new KhoSuKien(db());
$gio = new GioHang();
$nd  = nguoiDung();

$chiTietGio = $gio->chiTiet($kho);

if (empty($chiTietGio)) {
    flash('Giỏ đăng ký vé của bạn đang rỗng. Vui lòng chọn sự kiện trước khi đặt vé.');
    header('Location: gio-hang.php');
    exit;
}

$loiDatHang = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ghiChu = trim((string)($_POST['ghi_chu'] ?? ''));

    try {
        $uid = (int)($nd['id'] ?? 0);
        $donHangId = luuDonHang(db(), $uid, $gio->tatCa(), $ghiChu);
        $gio->xoaHet();

        flash("🎉 Đặt vé sự kiện thành công! Đơn hàng #{$donHangId} đã được tạo và ghi nhận an toàn.");
        header("Location: don-hang.php?id={$donHangId}");
        exit;
    } catch (RuntimeException $e) {
        $loiDatHang = $e->getMessage();
    } catch (Throwable $e) {
        error_log('Lỗi hệ thống khi đặt vé: ' . $e->getMessage());
        $loiDatHang = 'Đã xảy ra sự cố kỹ thuật trong quá trình xử lý đơn vé. Vui lòng thử lại sau.';
    }
}

$tongTien = 0;
$tongDiem = 0;
foreach ($chiTietGio as $item) {
    $tongTien += $item['thanhTien'];
    $tongDiem += $item['suKien']->diemRenLuyen * $item['soLuong'];
}

$tieuDe = 'Xác nhận đặt vé sự kiện - UniEvent';
$trang  = 'gio-hang';

require __DIR__ . '/inc/header.php';
?>

<main class="vung-chinh noi-dung-chinh container py-5">
  <div style="max-width: 1040px; margin: 0 auto; width: 100%;">
    
    <nav class="breadcrumb mb-4" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <a href="gio-hang.php">Phiếu đăng ký</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai">Xác nhận đặt vé</span>
    </nav>

    <div class="trang-tieu-de mb-4">
      <h1 class="font-hien-thi fw-800 text-chinh mb-2">Xác Nhận Đặt Vé &amp; Đăng Ký</h1>
      <p class="do-dai-chuan text-phu" style="font-size: 1.05rem;">
        Vui lòng kiểm tra lại danh sách sự kiện và thông tin sinh viên trước khi gửi yêu cầu lên hệ thống.
      </p>
    </div>

    <?php if ($loiDatHang !== null): ?>
      <div class="hop-thong-bao mb-4" role="alert" style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); color:#ef4444; padding:16px 20px; border-radius:12px; box-shadow:var(--shadow-sm);">
        <strong style="font-size:1.05rem;">⚠️ Không thể hoàn tất đơn đặt vé:</strong>
        <p class="mt-1 mb-2"><?= e($loiDatHang) ?></p>
        <a href="gio-hang.php" class="nut-bam nut-phu nut-nho">Quay lại giỏ vé điều chỉnh</a>
      </div>
    <?php endif; ?>

    <div class="dat-hang-grid">
      <div>
        <div style="background:var(--nen-surface); border:1px solid var(--border-nhat); border-radius:16px; padding:28px; box-shadow:var(--shadow-card);">
          <h2 class="h5 mb-4 font-hien-thi fw-700" style="color:var(--mau-chinh-toi);">
            📋 Danh Sách Vé Đăng Ký (<?= count($chiTietGio) ?> mục)
          </h2>

          <div class="bang-wrapper mb-4">
            <table style="width:100%; min-width: 440px; border-collapse:collapse;">
              <thead>
                <tr>
                  <th style="padding:12px 16px; text-align:left;">Sự kiện</th>
                  <th style="width:90px; padding:12px 10px; text-align:center; white-space:nowrap;">Số lượng</th>
                  <th style="width:140px; padding:12px 16px; text-align:right; white-space:nowrap;">Thành tiền</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($chiTietGio as $item): ?>
                  <?php $sk = $item['suKien']; ?>
                  <tr>
                    <td style="padding:12px 16px;">
                      <strong class="text-chinh"><?= e($sk->ten) ?></strong>
                      <div class="fs-meta text-phu mt-1" style="font-size:0.75rem;">
                        📍 <?= e($sk->diaDiem) ?> | Còn lại: <?= $sk->ton_kho ?> vé
                      </div>
                    </td>
                    <td style="padding:12px 10px; text-align:center; font-weight:600; white-space:nowrap;">
                      <?= $item['soLuong'] ?>
                    </td>
                    <td style="padding:12px 16px; text-align:right; font-weight:700; color:var(--mau-chinh); white-space:nowrap;">
                      <?= vnd($item['thanhTien']) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="background:var(--nen-section);">
                  <th colspan="2" style="padding:14px 16px; text-align:right; font-weight:700; font-size:1rem; white-space:nowrap;">Tổng thanh toán:</th>
                  <td style="padding:14px 16px; text-align:right; font-weight:800; color:var(--mau-chinh); font-size:1.2rem; white-space:nowrap;">
                    <?= vnd($tongTien) ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div style="font-size:0.875rem; color:#16a34a; background:rgba(34,197,94,0.12); padding:12px 16px; border-radius:10px; border:1px solid rgba(34,197,94,0.25); font-weight:500;">
            ✓ Quyền lợi rèn luyện tích lũy dự kiến: <strong>+<?= $tongDiem ?> Điểm rèn luyện</strong>
          </div>
        </div>
      </div>

      <div>
        <div style="background:var(--nen-surface); border:1px solid var(--border-nhat); border-radius:16px; padding:28px; box-shadow:var(--shadow-card);">
          <h2 class="h5 mb-4 font-hien-thi fw-700" style="color:var(--mau-chinh-toi);">
            👤 Thông Tin Sinh Viên
          </h2>

          <div class="mb-4" style="font-size:0.9rem; line-height:1.7; background:var(--nen-section); padding:16px; border-radius:10px; border:1px solid var(--border-nhat);">
            <div><span class="text-phu">Họ và tên:</span> <strong><?= e($nd['ho_ten'] ?? '') ?></strong></div>
            <div><span class="text-phu">Email:</span> <strong><?= e($nd['email'] ?? '') ?></strong></div>
            <div><span class="text-phu">Điện thoại:</span> <strong><?= e($nd['so_dien_thoai'] ?? 'Chưa cập nhật') ?></strong></div>
          </div>

          <form action="dat-hang.php" method="POST">
            <div class="form-group mb-4">
              <label for="ghi_chu" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
                Ghi chú thêm (không bắt buộc):
              </label>
              <textarea
                id="ghi_chu"
                name="ghi_chu"
                rows="3"
                class="form-control"
                placeholder="Ví dụ: Sinh viên khóa 22 khoa Toán - Tin..."
                style="width:100%; padding:10px 14px; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.875rem; font-family:inherit;"
              ></textarea>
            </div>

            <button type="submit" class="nut-bam nut-nhan nut-full py-3" style="font-size:1.05rem; font-weight:700; width:100%;">
              ✓ Xác Nhận Đăng Ký Giữ Chỗ
            </button>
            <div class="mt-3 text-center">
              <a href="gio-hang.php" class="text-phu" style="font-size:0.875rem; text-decoration:none;">
                ← Quay lại chỉnh sửa giỏ vé
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>

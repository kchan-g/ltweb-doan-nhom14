<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSuKien;
use App\Services\GioHang;

$kho = new KhoSuKien(db());
$gio = new GioHang();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanhDong = (string)($_POST['hanh_dong'] ?? '');

    if ($hanhDong === 'them') {
        $idRaw = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($idRaw === false || $idRaw <= 0) {
            flash('Mã sự kiện không hợp lệ.');
            header('Location: gio-hang.php');
            exit;
        }

        $sk = $kho->timTheoId($idRaw);
        if ($sk === null) {
            flash('Sự kiện không tồn tại trong hệ thống.');
            header('Location: gio-hang.php');
            exit;
        }

        $soLuongRaw = $_POST['so_luong'] ?? null;
        if (!is_numeric($soLuongRaw)) {
            flash('Số lượng vé không đúng định dạng số nguyên.');
            header('Location: gio-hang.php');
            exit;
        }

        $slInt = (int)$soLuongRaw;
        if ((string)$slInt !== trim((string)$soLuongRaw)) {
            flash('Số lượng vé phải là một số nguyên hợp lệ.');
            header('Location: gio-hang.php');
            exit;
        }

        if ($slInt <= 0) {
            flash('Số lượng vé phải lớn hơn 0 (tối thiểu 1 vé).');
            header('Location: gio-hang.php');
            exit;
        }

        if ($slInt > 50) {
            flash("Số lượng vé yêu cầu ({$slInt}) vượt quá giới hạn cho phép (tối đa 50 vé mỗi lượt đăng ký).");
            header('Location: gio-hang.php');
            exit;
        }

        if ($sk->ton_kho <= 0) {
            flash("Sự kiện \"{$sk->ten}\" đã hết vé đăng ký.");
            header('Location: gio-hang.php');
            exit;
        }

        if ($slInt > $sk->ton_kho) {
            flash("Số lượng vé yêu cầu ({$slInt}) vượt quá số vé còn lại trong kho ({$sk->ton_kho} vé).");
            header('Location: gio-hang.php');
            exit;
        }

        $gio->them($idRaw, $slInt);
        flash("Đã thêm sự kiện \"{$sk->ten}\" vào giỏ vé thành công!");
        header('Location: gio-hang.php');
        exit;
    }

    if ($hanhDong === 'cap_nhat') {
        $idRaw = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($idRaw === false || $idRaw <= 0) {
            flash('Mã sự kiện không hợp lệ.');
            header('Location: gio-hang.php');
            exit;
        }

        $sk = $kho->timTheoId($idRaw);
        if ($sk === null) {
            flash('Sự kiện không tồn tại trong hệ thống.');
            header('Location: gio-hang.php');
            exit;
        }

        $soLuongRaw = $_POST['so_luong'] ?? null;
        if (!is_numeric($soLuongRaw)) {
            flash('Số lượng vé không đúng định dạng số nguyên.');
            header('Location: gio-hang.php');
            exit;
        }

        $slInt = (int)$soLuongRaw;
        if ((string)$slInt !== trim((string)$soLuongRaw)) {
            flash('Số lượng vé phải là một số nguyên hợp lệ.');
            header('Location: gio-hang.php');
            exit;
        }

        if ($slInt <= 0) {
            flash('Số lượng vé cập nhật không hợp lệ (phải từ 1 đến 50 vé). Nếu muốn xóa sự kiện khỏi giỏ, vui lòng nhấn nút ✕.');
            header('Location: gio-hang.php');
            exit;
        }

        if ($slInt > 50) {
            flash("Số lượng vé yêu cầu ({$slInt}) vượt quá giới hạn cho phép (tối đa 50 vé mỗi sự kiện).");
            header('Location: gio-hang.php');
            exit;
        }

        if ($slInt > $sk->ton_kho) {
            flash("Số lượng vé yêu cầu ({$slInt}) vượt quá số vé còn lại trong kho ({$sk->ton_kho} vé).");
            header('Location: gio-hang.php');
            exit;
        }

        $gio->capNhat($idRaw, $slInt);
        flash("Đã cập nhật số lượng vé cho sự kiện \"{$sk->ten}\" thành {$slInt} vé.");
        header('Location: gio-hang.php');
        exit;
    }

    if ($hanhDong === 'xoa') {
        $idRaw = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($idRaw !== false && $idRaw > 0) {
            $sk = $kho->timTheoId($idRaw);
            $gio->xoa($idRaw);
            $tenSK = $sk ? $sk->ten : "Sự kiện #{$idRaw}";
            flash("Đã xóa sự kiện \"{$tenSK}\" khỏi giỏ vé.");
        }
        header('Location: gio-hang.php');
        exit;
    }

    if ($hanhDong === 'xoa_het') {
        $gio->xoaHet();
        flash('Đã xóa toàn bộ sự kiện khỏi giỏ vé.');
        header('Location: gio-hang.php');
        exit;
    }

    if ($hanhDong === 'xac_nhan_tat_ca') {
        $soLuong = $gio->xacNhanTatCa();
        if ($soLuong > 0) {
            flash("🎉 Đã đăng ký thành công {$soLuong} vé sự kiện!");
        } else {
            flash('Hiện không có sự kiện nào trong danh sách chờ xác nhận.');
        }
        header('Location: gio-hang.php#da-xac-nhan');
        exit;
    }

    if ($hanhDong === 'huy_ve') {
        $idRaw = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($idRaw !== false && $idRaw > 0) {
            $sk = $kho->timTheoId($idRaw);
            $gio->huyXacNhan($idRaw);
            $tenSK = $sk ? $sk->ten : "Sự kiện #{$idRaw}";
            flash("Đã hủy vé của sự kiện \"{$tenSK}\".");
        }
        header('Location: gio-hang.php#da-xac-nhan');
        exit;
    }
}

$chiTietGio = $gio->chiTiet($kho);
$danhSachDaXacNhan = $gio->danhSachDaXacNhan($kho);
$thongBaoFlash = flash();

$tongTien = 0;
$tongDiem = 0;
foreach ($chiTietGio as $item) {
    $tongTien += $item['thanhTien'];
    $tongDiem += $item['suKien']->diemRenLuyen * $item['soLuong'];
}

$tongDiemDaXacNhan = 0;
foreach ($danhSachDaXacNhan as $item) {
    $tongDiemDaXacNhan += $item['suKien']->diemRenLuyen;
}

$tieuDe = 'Phiếu đăng ký giữ chỗ sự kiện - UniEvent';
$trang  = 'gio-hang';

require __DIR__ . '/inc/header.php';
?>

<main>
  <div class="container">
    <nav class="breadcrumb mb-4" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai">Phiếu đăng ký sự kiện</span>
    </nav>

    <div class="trang-tieu-de mb-4">
      <h1 class="font-hien-thi fw-800 text-chinh mb-2">Phiếu Đăng Ký Giữ Chỗ &amp; Giỏ Vé</h1>
      <p class="do-dai-chuan text-phu" style="font-size: 1.05rem;">
        Quản lý danh sách các sự kiện bạn dự định tham gia, theo dõi số lượng vé, giá tiền và điểm rèn luyện tích lũy.
      </p>
    </div>

    <?php if (!empty($thongBaoFlash)): ?>
      <div class="hop-thong-bao mb-4" role="alert" style="background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.3); color:var(--mau-chinh); padding:14px 20px; border-radius:12px; font-weight:500; box-shadow:var(--shadow-sm);">
        <strong style="color:var(--mau-chinh);">Thông báo:</strong> <?= e($thongBaoFlash) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($chiTietGio) && empty($danhSachDaXacNhan)): ?>
      <div class="p-5 text-center my-4" style="background:var(--nen-surface); border-radius:16px; border:1px dashed var(--border-dam); box-shadow:var(--shadow-sm);">
        <div style="font-size:3.5rem; margin-bottom:1rem;">🎫</div>
        <h2 class="h4" style="color:var(--mau-chinh-toi); font-weight:700;">Phiếu đăng ký của bạn đang trống</h2>
        <p class="text-phu mb-4" style="max-width:500px; margin-left:auto; margin-right:auto;">
          Bạn chưa chọn sự kiện nào vào phiếu. Hãy khám phá danh sách sự kiện phong phú tại trường để bắt đầu đăng ký!
        </p>
        <a href="danh-sach.php" class="nut-bam nut-nhan" style="padding: 10px 24px;">Khám phá sự kiện ngay</a>
      </div>
    <?php else: ?>

      <?php if (!empty($chiTietGio)): ?>
        <section aria-labelledby="tieu-de-danh-sach-cho" class="mb-5">
          <div class="section-head mb-4">
            <div>
              <h2 id="tieu-de-danh-sach-cho" class="h4 font-hien-thi fw-700" style="color:#b45309;">
                ⏳ Danh Sách Sự Kiện Đang Chờ Đặt Vé (<?= count($chiTietGio) ?> mục)
              </h2>
              <p class="section-head__mota mt-1">
                Các sự kiện đang giữ trong phiên. Vui lòng kiểm tra số lượng và tiến hành đặt vé để giữ chỗ an toàn trong CSDL.
              </p>
            </div>
          </div>

          <div class="bang-card mb-4">
            <div class="bang-card__header">
              <h2 class="bang-card__tieu-de">
                <?= uiIcon('ticket', 'icon-brand', 18) ?>
                <span>Danh sách sự kiện đang chờ đặt vé</span>
              </h2>
              <span class="bang-card__dem"><?= count($chiTietGio) ?> sự kiện</span>
            </div>
            <div class="bang-wrapper">
              <table style="width:100%; min-width: 660px; border-collapse:collapse;">
              <thead>
                <tr>
                  <th scope="col" style="min-width:240px;">Sự kiện</th>
                  <th scope="col" style="width:120px; white-space:nowrap;">Đơn giá</th>
                  <th scope="col" style="width:130px; text-align:center; white-space:nowrap;">Số lượng</th>
                  <th scope="col" style="width:130px; text-align:right; white-space:nowrap;">Thành tiền</th>
                  <th scope="col" style="width:100px; text-align:center; white-space:nowrap;">Quyền lợi</th>
                  <th scope="col" style="width:70px; text-align:center; white-space:nowrap;">Xóa</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($chiTietGio as $item): ?>
                  <?php $sk = $item['suKien']; ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img
                          src="<?= e($sk->hinhAnh) ?>"
                          alt="<?= e($sk->ten) ?>"
                          width="64"
                          height="44"
                          style="border-radius:8px; object-fit:cover; flex-shrink:0; border:1px solid var(--border-nhat);"
                        />
                        <div>
                          <a href="chi-tiet.php?id=<?= $sk->id ?>" class="fw-bold text-chinh" style="font-size:0.95rem; text-decoration:none;">
                            <?= e($sk->ten) ?>
                          </a>
                          <div class="fs-meta text-phu mt-1">
                            <?= uiIcon('calendar', 'icon-muted', 13) ?> <?= e($sk->thoiGian) ?> | <?= uiIcon('pin', 'icon-muted', 13) ?> <?= e($sk->diaDiem) ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td style="white-space:nowrap;">
                      <strong><?= vnd($sk->gia) ?></strong>
                    </td>
                    <td style="text-align:center;">
                      <form action="gio-hang.php" method="POST" class="d-flex align-items-center justify-content-center gap-1" style="margin:0;">
                        <input type="hidden" name="hanh_dong" value="cap_nhat">
                        <input type="hidden" name="id" value="<?= $sk->id ?>">
                        <input
                          type="number"
                          name="so_luong"
                          value="<?= $item['soLuong'] ?>"
                          min="1"
                          max="50"
                          class="form-control"
                          style="width:65px; height:36px; padding:4px 6px; text-align:center; border:1.5px solid var(--border-nhat); border-radius:6px; font-weight:600;"
                        />
                        <button type="submit" class="nut-bam nut-phu nut-nho" style="padding:6px 10px;" title="Cập nhật số lượng">↻</button>
                      </form>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                      <strong style="color:var(--mau-chinh); font-size:1rem;"><?= vnd($item['thanhTien']) ?></strong>
                    </td>
                    <td style="text-align:center; white-space:nowrap;">
                      <span class="badge" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; font-weight:600;">
                        +<?= $sk->diemRenLuyen * $item['soLuong'] ?> Điểm
                      </span>
                    </td>
                    <td style="text-align:center;">
                      <form action="gio-hang.php" method="POST" style="margin:0;">
                        <input type="hidden" name="hanh_dong" value="xoa">
                        <input type="hidden" name="id" value="<?= $sk->id ?>">
                        <button type="submit" class="nut-bam nut-canh-bao nut-nho" style="padding:6px 10px; border-radius:6px;" title="Xóa mục này">
                          ✕
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="background:var(--nen-section);">
                  <th colspan="3" scope="row" style="text-align:right; font-weight:700; font-size:1.05rem;">
                    Tổng cộng thanh toán:
                  </th>
                  <td colspan="3" style="text-align:left;">
                    <strong style="font-size:1.25rem; color:var(--mau-chinh); font-weight:800; white-space:nowrap;">
                      <?= vnd($tongTien) ?>
                    </strong>
                    <span class="ms-2 fs-meta text-phu" style="white-space:nowrap;">(Tổng: +<?= $tongDiem ?> Điểm rèn luyện)</span>
                  </td>
                </tr>
              </tfoot>
            </table>
            </div>
          </div>

          <div class="gio-hang__dieu-huong">
            <div class="nhom-nut-trai">
              <a href="danh-sach.php" class="nut-bam nut-phu">← Tiếp tục chọn sự kiện</a>
              <form action="gio-hang.php" method="POST" style="margin:0; display:inline-flex;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ vé này?');">
                <input type="hidden" name="hanh_dong" value="xoa_het">
                <button type="submit" class="nut-bam nut-canh-bao">Xóa giỏ vé</button>
              </form>
            </div>

            <div class="nhom-nut-phai">
              <a href="dat-hang.php" class="nut-bam nut-nhan">
                💳 Tiến Hành Đặt Vé / Đăng Ký →
              </a>
            </div>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($danhSachDaXacNhan)): ?>
        <section id="da-xac-nhan" class="mt-5 pt-4" style="border-top:1px dashed var(--border-dam);">
          <div class="section-head mb-4">
            <div>
              <h2 class="h4 font-hien-thi fw-700" style="color:#15803d;">
                <?= uiIcon('check', 'icon-success', 16) ?> Vé Đã Xác Nhận Trong Phiên (<?= count($danhSachDaXacNhan) ?> vé)
              </h2>
              <p class="section-head__mota mt-1">
                Danh sách vé điện tử bạn đã xác nhận giữ chỗ nhanh trong phiên làm việc.
              </p>
            </div>
          </div>

          <div class="bang-card mb-4">
            <div class="bang-card__header">
              <h2 class="bang-card__tieu-de">
                <?= uiIcon('check', 'icon-success', 18) ?>
                <span>Danh sách vé đã xác nhận giữ chỗ</span>
              </h2>
              <span class="bang-card__dem"><?= count($danhSachDaXacNhan) ?> vé</span>
            </div>
            <div class="bang-wrapper">
              <table style="width:100%; min-width: 660px; border-collapse:collapse;">
              <thead>
                <tr>
                  <th scope="col" style="min-width:240px;">Sự kiện</th>
                  <th scope="col" style="width:160px; white-space:nowrap;">Mã vé điện tử</th>
                  <th scope="col" style="width:140px; white-space:nowrap;">Thời gian</th>
                  <th scope="col" style="width:100px; text-align:center; white-space:nowrap;">Quyền lợi</th>
                  <th scope="col" style="width:130px; text-align:center; white-space:nowrap;">Trạng thái</th>
                  <th scope="col" style="width:90px; text-align:center; white-space:nowrap;">Hủy vé</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($danhSachDaXacNhan as $item): ?>
                  <?php $sk = $item['suKien']; ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img
                          src="<?= e($sk->hinhAnh) ?>"
                          alt="<?= e($sk->ten) ?>"
                          width="64"
                          height="44"
                          style="border-radius:8px; object-fit:cover; flex-shrink:0; border:1px solid var(--border-nhat);"
                        />
                        <div>
                          <a href="chi-tiet.php?id=<?= $sk->id ?>" class="fw-bold text-chinh" style="font-size:0.95rem; text-decoration:none;">
                            <?= e($sk->ten) ?>
                          </a>
                          <div class="fs-meta text-phu mt-1">
                            <?= uiIcon('calendar', 'icon-muted', 13) ?> <?= e($sk->thoiGian) ?> | <?= uiIcon('pin', 'icon-muted', 13) ?> <?= e($sk->diaDiem) ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td style="white-space:nowrap;">
                      <code style="background:var(--nen-section); padding:6px 10px; border-radius:6px; font-weight:700; color:var(--mau-chinh); font-size:0.875rem; border:1px solid var(--border-nhat);">
                        <?= e($item['maVe']) ?>
                      </code>
                    </td>
                    <td style="white-space:nowrap;">
                      <span class="fs-meta text-phu">🕒 <?= e($item['thoiGianXacNhan']) ?></span>
                    </td>
                    <td style="text-align:center; white-space:nowrap;">
                      <span class="badge" style="background:rgba(59,130,246,0.12); color:var(--mau-chinh); border:1px solid rgba(59,130,246,0.25); font-weight:600;">
                        +<?= $sk->diemRenLuyen ?> Điểm
                      </span>
                    </td>
                    <td style="text-align:center; white-space:nowrap;">
                      <span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-weight:700;">
                        ✓ Đã xác nhận
                      </span>
                    </td>
                    <td style="text-align:center;">
                      <form action="gio-hang.php" method="POST" style="margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn hủy vé sự kiện này?');">
                        <input type="hidden" name="hanh_dong" value="huy_ve">
                        <input type="hidden" name="id" value="<?= $sk->id ?>">
                        <button type="submit" class="nut-bam nut-canh-bao nut-nho" style="padding:6px 12px; font-size:0.8rem; border-radius:6px;">
                          Hủy vé
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            </div>
          </div>
        </section>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>

<?php
/**
 * gio-hang.php
 * Trang quản lý Phiếu đăng ký giữ chỗ sự kiện phía máy chủ bằng Session ($_SESSION['gio']).
 * Xử lý: Thêm sự kiện, đổi số lượng chỗ, xóa 1 sự kiện, xóa toàn bộ theo mô hình Post/Redirect/Get (PRG).
 * Toàn bộ thông tin sự kiện được đối chiếu từ tệp dữ liệu máy chủ.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSuKien;
use App\Services\GioHang;

$kho = new KhoSuKien(__DIR__ . '/data/su-kien.json');
$gio = new GioHang();

// 1. XỬ LÝ BIỂU MẪU POST (Thêm, Xác nhận, Hủy, Xóa) - Áp dụng Post/Redirect/Get (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanhDong = $_POST['hanh_dong'] ?? '';

    if ($hanhDong === 'them') {
        $idRaw = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if ($idRaw === false || $idRaw <= 0 || $kho->timTheoId($idRaw) === null) {
            flash('Mã sự kiện không hợp lệ hoặc sự kiện không tồn tại trong hệ thống.');
            header('Location: gio-hang.php');
            exit;
        }

        // Ràng buộc: Mỗi tài khoản sinh viên chỉ có thể đăng ký 1 vé cho mỗi sự kiện
        if ($gio->daXacNhan($idRaw)) {
            flash('Bạn đã xác nhận đăng ký sự kiện này rồi (mỗi tài khoản sinh viên chỉ được đăng ký tối đa 1 vé cho mỗi sự kiện).');
            header('Location: gio-hang.php#da-xac-nhan');
            exit;
        }

        if ($gio->dangChoXacNhan($idRaw)) {
            flash('Sự kiện này đã có trong danh sách chờ xác nhận. Vui lòng bấm "Xác Nhận Đăng Ký Giữ Chỗ" bên dưới để hoàn tất.');
            header('Location: gio-hang.php');
            exit;
        }

        $gio->them($idRaw, 1);
        flash('Đã thêm sự kiện vào phiếu! Trạng thái hiện tại: ⏳ Chờ xác nhận đăng ký. Vui lòng bấm "Xác Nhận Đăng Ký Giữ Chỗ" bên dưới để hoàn tất.');
        header('Location: gio-hang.php');
        exit;
    }

    if ($hanhDong === 'xac_nhan_tat_ca') {
        $soLuong = $gio->xacNhanTatCa();
        if ($soLuong > 0) {
            flash("🎉 Chúc mừng bạn đã đăng ký tham gia thành công {$soLuong} sự kiện! Số lượng vé đã đăng ký của bạn được +{$soLuong}.");
        } else {
            flash('Hiện không có sự kiện nào trong danh sách chờ xác nhận.');
        }
        header('Location: gio-hang.php#da-xac-nhan');
        exit;
    }

    if ($hanhDong === 'xac_nhan_mot') {
        $idRaw = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($idRaw !== false && $idRaw > 0 && $gio->xacNhan($idRaw)) {
            flash('🎉 Đã xác nhận đăng ký thành công! Số lượng sự kiện đã đăng ký của bạn được +1.');
        }
        header('Location: gio-hang.php#da-xac-nhan');
        exit;
    }

    if ($hanhDong === 'xoa') {
        $idRaw = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($idRaw !== false && $idRaw > 0) {
            $gio->xoa($idRaw);
            flash('Đã xóa sự kiện khỏi danh sách chờ xác nhận.');
        }
        header('Location: gio-hang.php');
        exit;
    }

    if ($hanhDong === 'huy_ve') {
        $idRaw = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($idRaw !== false && $idRaw > 0) {
            $gio->huyXacNhan($idRaw);
            flash('Đã hủy đăng ký sự kiện thành công.');
        }
        header('Location: gio-hang.php#da-xac-nhan');
        exit;
    }

    if ($hanhDong === 'xoa_het') {
        $gio->xoaHet();
        flash('Đã xóa toàn bộ danh sách chờ xác nhận đăng ký.');
        header('Location: gio-hang.php');
        exit;
    }

    header('Location: gio-hang.php');
    exit;
}

// 2. HIỂN THỊ DANH SÁCH CHI TIẾT
$chiTietGio = $gio->chiTiet($kho);
$danhSachDaXacNhan = $gio->danhSachDaXacNhan($kho);

$tongDiem = 0;
foreach ($chiTietGio as $item) {
    $tongDiem += $item['suKien']->diemRenLuyen * $item['soLuong'];
}

$tongDiemDaXacNhan = 0;
foreach ($danhSachDaXacNhan as $item) {
    $tongDiemDaXacNhan += $item['suKien']->diemRenLuyen;
}

$thongBao = flash();

$tieuDe = 'Phiếu đăng ký sự kiện - UniEvent';
$trang  = 'gio-hang';

require __DIR__ . '/inc/header.php';
?>

<main>
  <div class="container">
    <nav class="breadcrumb mb-3" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai">Phiếu đăng ký sự kiện</span>
    </nav>

    <div class="trang-tieu-de mb-4">
      <h1>Phiếu Đăng Ký Giữ Chỗ Sự Kiện</h1>
      <p class="do-dai-chuan text-phu">
        Xem lại danh sách các sự kiện bạn đã chọn giữ chỗ (mỗi sự kiện tối đa 1 vé/sinh viên) và tiến hành xác nhận tham gia để tích lũy điểm rèn luyện.
      </p>
    </div>

    <?php if ($thongBao !== null): ?>
      <?php $isLoi = str_contains($thongBao, 'không hợp lệ') || str_contains($thongBao, 'Lỗi') || str_contains($thongBao, 'không tồn tại'); ?>
      <div class="hop-thong-bao <?= $isLoi ? 'hop-thong-bao--loi' : 'hop-thong-bao--thanh-cong' ?> mb-4" role="status">
        <span class="hop-thong-bao__icon" aria-hidden="true"><?= $isLoi ? '⚠️' : '✓' ?></span>
        <div>
          <p class="mb-0"><?= e($thongBao) ?></p>
        </div>
      </div>
    <?php endif; ?>

  <?php if (empty($chiTietGio) && empty($danhSachDaXacNhan)): ?>
    <!-- DANH SÁCH RỖNG -->
    <div class="text-center p-5 my-4" style="background:var(--nen-surface); border-radius:var(--radius-vua); border:1px dashed var(--border-nhat);">
      <div style="font-size:3.5rem; margin-bottom:1rem;">📋</div>
      <h2 class="h4" style="color:var(--mau-chinh-toi);">Chưa có sự kiện nào trong phiếu đăng ký</h2>
      <p class="text-phu mb-4">
        Bạn chưa chọn đăng ký sự kiện nào. Hãy khám phá các hội thảo, cuộc thi và hoạt động hấp dẫn tại UniEvent nhé!
      </p>
      <a href="danh-sach.php" class="nut-bam nut-chinh">Khám phá danh sách sự kiện</a>
    </div>
  <?php else: ?>

    <?php if (!empty($chiTietGio)): ?>
      <!-- 1. BẢNG CHI TIẾT SỰ KIỆN CHỜ XÁC NHẬN ĐĂNG KÝ -->
      <section class="mb-5">
        <div class="section-head mb-3">
          <div>
            <h2 class="h4" style="color:#b45309;">
              ⏳ Danh Sách Chờ Xác Nhận Đăng Ký (<?= count($chiTietGio) ?> sự kiện)
            </h2>
            <p class="section-head__mota">
              Các sự kiện bạn đã chọn giữ chỗ nhưng chưa hoàn tất xác nhận. Vui lòng bấm <strong>"Xác Nhận Đăng Ký Giữ Chỗ"</strong> để được ghi nhận vào hệ thống.
            </p>
          </div>
        </div>

        <div class="bang-wrapper shadow-sm mb-4">
          <table>
            <caption>Danh sách sự kiện đang chờ sinh viên xác nhận đăng ký giữ chỗ</caption>
            <thead>
              <tr>
                <th scope="col" style="width:45%;">Sự kiện</th>
                <th scope="col">Quyền lợi</th>
                <th scope="col" style="width:160px;">Số lượng chỗ</th>
                <th scope="col">Trạng thái</th>
                <th scope="col" style="text-align:center;">Hủy</th>
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
                        width="60"
                        height="40"
                        style="border-radius:4px; object-fit:cover; flex-shrink:0;"
                      />
                      <div>
                        <a href="chi-tiet.php?id=<?= $sk->id ?>" class="fw-bold text-chinh" style="font-size:0.95rem;">
                          <?= e($sk->ten) ?>
                        </a>
                        <div class="fs-meta text-phu mt-1">
                          📅 <?= e($sk->thoiGian) ?> | 📍 <?= e($sk->diaDiem) ?>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge badge--xanh">+<?= $sk->diemRenLuyen ?> Điểm rèn luyện</span>
                  </td>
                  <td>
                    <!-- Ràng buộc: Mỗi tài khoản sinh viên chỉ được đăng ký tối đa 1 vé cho mỗi sự kiện -->
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge" style="background:var(--nen-surface); border:1px solid var(--border-nhat); font-size:0.95rem; font-weight:600; padding:5px 12px; color:var(--mau-chinh);">
                        1 vé
                      </span>
                    </div>
                  </td>
                  <td>
                    <span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-weight:600;">
                      ⏳ Chờ xác nhận
                    </span>
                  </td>
                  <td style="text-align:center;">
                    <!-- Biểu mẫu xóa một sự kiện khỏi danh sách chờ (PRG) -->
                    <form action="gio-hang.php" method="POST" style="margin:0;">
                      <input type="hidden" name="hanh_dong" value="xoa">
                      <input type="hidden" name="id" value="<?= $sk->id ?>">
                      <button type="submit" class="nut-bam nut-canh-bao nut-nho" style="padding:4px 10px;" title="Hủy sự kiện khỏi danh sách chờ">
                        ✕
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr style="background:var(--nen-surface);">
                <th colspan="3" scope="row" style="text-align:right; font-weight:700; font-size:1.05rem;">
                  Tổng điểm rèn luyện tích lũy dự kiến:
                </th>
                <td colspan="2">
                  <strong style="font-size:1.15rem; color:var(--mau-chinh); font-weight:800;">
                    +<?= $tongDiem ?> Điểm rèn luyện
                  </strong>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- HÀNH ĐỘNG PHIẾU ĐĂNG KÝ -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div class="d-flex gap-2">
            <a href="danh-sach.php" class="nut-bam nut-phu">← Tiếp tục chọn sự kiện</a>
            <!-- Nút xóa hết danh sách chờ -->
            <form action="gio-hang.php" method="POST" style="margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa toàn bộ danh sách chờ xác nhận này không?');">
              <input type="hidden" name="hanh_dong" value="xoa_het">
              <button type="submit" class="nut-bam nut-canh-bao">Xóa danh sách chờ</button>
            </form>
          </div>

          <!-- Nút Xác nhận đăng ký chính thức (POST -> chuyển sang Đã đăng ký và +1 vào Đã đăng ký) -->
          <form action="gio-hang.php" method="POST" style="margin:0;">
            <input type="hidden" name="hanh_dong" value="xac_nhan_tat_ca">
            <button type="submit" class="nut-bam nut-nhan py-2 px-4" style="font-size:1rem; font-weight:700;">
              ✓ Xác Nhận Đăng Ký Giữ Chỗ (+<?= count($chiTietGio) ?> vé) →
            </button>
          </form>
        </div>
      </section>
    <?php endif; ?>

    <!-- 2. BẢNG DANH SÁCH SỰ KIỆN ĐÃ ĐĂNG KÝ THÀNH CÔNG -->
    <?php if (!empty($danhSachDaXacNhan)): ?>
      <section id="da-xac-nhan" class="mt-5 pt-3" style="border-top:1px dashed var(--border-nhat);">
        <div class="section-head mb-3">
          <div>
            <h2 class="h4" style="color:#15803d;">
              ✓ Sự Kiện Đã Đăng Ký Thành Công (<span id="dem-so-ve-xac-nhan"><?= count($danhSachDaXacNhan) ?></span> vé)
            </h2>
            <p class="section-head__mota">
              Danh sách các sự kiện bạn đã hoàn tất đăng ký giữ chỗ chính thức. Hãy xuất trình mã vé hoặc thẻ sinh viên để quét điểm danh khi tham gia.
            </p>
          </div>
        </div>

        <div class="bang-wrapper shadow-sm mb-4">
          <table>
            <caption>Danh sách các sự kiện sinh viên đã đăng ký tham gia thành công</caption>
            <thead>
              <tr>
                <th scope="col" style="width:40%;">Sự kiện</th>
                <th scope="col" style="width:140px;">Mã vé điện tử</th>
                <th scope="col">Thời gian xác nhận</th>
                <th scope="col">Quyền lợi</th>
                <th scope="col">Trạng thái</th>
                <th scope="col" style="text-align:center;">Hủy vé</th>
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
                        width="60"
                        height="40"
                        style="border-radius:4px; object-fit:cover; flex-shrink:0;"
                      />
                      <div>
                        <a href="chi-tiet.php?id=<?= $sk->id ?>" class="fw-bold text-chinh" style="font-size:0.95rem;">
                          <?= e($sk->ten) ?>
                        </a>
                        <div class="fs-meta text-phu mt-1">
                          📅 <?= e($sk->thoiGian) ?> | 📍 <?= e($sk->diaDiem) ?>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <code style="background:#f1f5f9; padding:4px 8px; border-radius:4px; font-weight:700; color:var(--mau-chinh); font-size:0.875rem;">
                      <?= e($item['maVe']) ?>
                    </code>
                  </td>
                  <td>
                    <span class="fs-meta text-phu">
                      🕒 <?= e($item['thoiGianXacNhan']) ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge badge--xanh">+<?= $sk->diemRenLuyen ?> Điểm rèn luyện</span>
                  </td>
                  <td>
                    <span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-weight:600;">
                      ✓ Đã đăng ký
                    </span>
                  </td>
                  <td style="text-align:center;">
                    <!-- Biểu mẫu hủy vé đã xác nhận (PRG) -->
                    <form action="gio-hang.php" method="POST" style="margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đăng ký tham gia sự kiện này không?');">
                      <input type="hidden" name="hanh_dong" value="huy_ve">
                      <input type="hidden" name="id" value="<?= $sk->id ?>">
                      <button type="submit" class="nut-bam nut-canh-bao nut-nho" style="padding:4px 8px; font-size:0.75rem;" title="Hủy đăng ký vé sự kiện này">
                        Hủy vé
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr style="background:var(--nen-surface);">
                <th colspan="3" scope="row" style="text-align:right; font-weight:700; font-size:1.05rem;">
                  Tổng điểm rèn luyện đã xác nhận:
                </th>
                <td colspan="3">
                  <strong style="font-size:1.15rem; color:#15803d; font-weight:800;">
                    +<?= $tongDiemDaXacNhan ?> Điểm rèn luyện
                  </strong>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </section>
    <?php endif; ?>

  <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>

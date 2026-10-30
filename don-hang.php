<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

yeuCauDangNhap();

$nd      = nguoiDung();
$uid     = (int)($nd['id'] ?? 0);
$laAdmin = laQuanTri();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $laAdmin) {
    $donId = filter_var($_POST['don_id'] ?? null, FILTER_VALIDATE_INT);
    $trangThaiMoi = (string)($_POST['trang_thai'] ?? '');

    $dsTrangThaiHopLe = ['cho_xac_nhan', 'da_thanh_toan', 'hoan_thanh', 'da_huy'];

    if ($donId && in_array($trangThaiMoi, $dsTrangThaiHopLe, true)) {
        $st = db()->prepare('UPDATE don_hang SET trang_thai = ? WHERE id = ?');
        $st->execute([$trangThaiMoi, $donId]);
        flash("Đã cập nhật trạng thái đơn hàng #{$donId} thành công!");
    }

    $chuyenHuong = !empty($_POST['quay_lai_id']) ? "don-hang.php?id={$_POST['quay_lai_id']}" : 'don-hang.php';
    header("Location: {$chuyenHuong}");
    exit;
}

$idXemChiTiet = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($idXemChiTiet !== false && $idXemChiTiet !== null && $idXemChiTiet > 0) {
    if ($laAdmin) {
        $st = db()->prepare(
            'SELECT d.*, u.ho_ten, u.email, u.so_dien_thoai
             FROM don_hang d
             JOIN nguoi_dung u ON d.nguoi_dung_id = u.id
             WHERE d.id = ?'
        );
        $st->execute([$idXemChiTiet]);
    } else {
        $st = db()->prepare(
            'SELECT d.*, u.ho_ten, u.email, u.so_dien_thoai
             FROM don_hang d
             JOIN nguoi_dung u ON d.nguoi_dung_id = u.id
             WHERE d.id = ? AND d.nguoi_dung_id = ?'
        );
        $st->execute([$idXemChiTiet, $uid]);
    }

    $don = $st->fetch();

    if ($don === false) {
        http_response_code(404);
        require __DIR__ . '/404.php';
        exit;
    }

    $stCt = db()->prepare(
        'SELECT ct.*, sk.ten, sk.anh, sk.dia_diem, sk.thoi_gian, sk.diem_ren_luyen
         FROM chi_tiet_don ct
         JOIN su_kien sk ON ct.su_kien_id = sk.id
         WHERE ct.don_hang_id = ?'
    );
    $stCt->execute([$idXemChiTiet]);
    $danhSachVe = $stCt->fetchAll();

    $tieuDe = "Chi tiết đơn hàng {$don['ma_don']} - UniEvent";
    $trang  = 'don-hang';

    require __DIR__ . '/inc/header.php';
    ?>
    <main class="vung-chinh noi-dung-chinh container py-5">
      <div style="max-width: 900px; margin: 0 auto;">
        <nav class="breadcrumb mb-4" aria-label="Đường dẫn phân cấp">
          <a href="index.php">Trang chủ</a>
          <span aria-hidden="true">/</span>
          <a href="don-hang.php">Danh sách đơn hàng</a>
          <span aria-hidden="true">/</span>
          <span class="hien-tai"><?= e($don['ma_don']) ?></span>
        </nav>

        <?php $msg = flash(); if ($msg): ?>
          <div class="hop-thong-bao mb-4" role="alert" style="background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.35); color:#10b981; padding:14px 20px; border-radius:12px; box-shadow:var(--shadow-sm);">
            <?= e($msg) ?>
          </div>
        <?php endif; ?>

        <div style="background:var(--nen-surface); border:1px solid var(--border-nhat); border-radius:16px; padding:32px; box-shadow:var(--shadow-card);">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4 pb-3" style="border-bottom:1px solid var(--border-nhat);">
            <div>
              <span class="badge" style="background:var(--nen-section); color:var(--chu-phu); border:1px solid var(--border-nhat); font-size:0.8rem; font-weight:700;">ĐƠN HÀNG #<?= $don['id'] ?></span>
              <h1 class="h3 mt-2 mb-1 font-hien-thi fw-800" style="color:var(--mau-chinh-toi);">
                Mã đơn: <span style="color:var(--mau-chinh);"><?= e($don['ma_don']) ?></span>
              </h1>
              <div class="text-phu fs-meta mt-1" style="font-size:0.875rem;">
                Ngày đặt: <?= uiIcon('clock', 'icon-muted', 14) ?> <?= date('d/m/Y H:i', strtotime($don['ngay_dat'])) ?>
              </div>
            </div>

            <div class="text-end">
              <?php
              $nhanTrangThai = [
                  'cho_xac_nhan'  => ['Chờ xác nhận', 'rgba(245,158,11,0.15)', '#d97706', 'rgba(245,158,11,0.3)'],
                  'da_thanh_toan' => ['Đã thanh toán', 'rgba(34,197,94,0.15)', '#16a34a', 'rgba(34,197,94,0.3)'],
                  'hoan_thanh'    => ['Hoàn thành', 'rgba(59,130,246,0.15)', '#2563eb', 'rgba(59,130,246,0.3)'],
                  'da_huy'        => ['Đã hủy', 'rgba(239,68,68,0.15)', '#ef4444', 'rgba(239,68,68,0.3)'],
              ];
              $tt = $nhanTrangThai[$don['trang_thai']] ?? ['Không xác định', 'var(--nen-section)', 'var(--chu-phu)', 'var(--border-nhat)'];
              ?>
              <span class="badge" style="background:<?= $tt[1] ?>; color:<?= $tt[2] ?>; border:1px solid <?= $tt[3] ?>; padding:8px 16px; font-size:0.9rem; font-weight:700; border-radius:var(--radius-pill);">
                <?= e($tt[0]) ?>
              </span>

              <?php if ($laAdmin): ?>
                <form action="don-hang.php" method="POST" class="mt-2 d-flex gap-1 align-items-center justify-content-end">
                  <input type="hidden" name="don_id" value="<?= $don['id'] ?>">
                  <input type="hidden" name="quay_lai_id" value="<?= $don['id'] ?>">
                  <select name="trang_thai" class="form-control" style="font-size:0.8rem; padding:4px 8px; border-radius:6px; border:1px solid var(--border-nhat);">
                    <option value="cho_xac_nhan" <?= $don['trang_thai'] === 'cho_xac_nhan' ? 'selected' : '' ?>>Chờ xác nhận</option>
                    <option value="da_thanh_toan" <?= $don['trang_thai'] === 'da_thanh_toan' ? 'selected' : '' ?>>Đã thanh toán</option>
                    <option value="hoan_thanh" <?= $don['trang_thai'] === 'hoan_thanh' ? 'selected' : '' ?>>Hoàn thành</option>
                    <option value="da_huy" <?= $don['trang_thai'] === 'da_huy' ? 'selected' : '' ?>>Đã hủy</option>
                  </select>
                  <button type="submit" class="nut-bam nut-phu nut-nho" style="padding:4px 10px; font-size:0.8rem;">Lưu</button>
                </form>
              <?php endif; ?>
            </div>
          </div>

          <div class="row g-3 mb-4" style="display:flex; flex-wrap:wrap; gap:16px;">
            <div style="flex:1 1 280px; background:var(--nen-section); padding:18px; border-radius:12px; font-size:0.875rem; border:1px solid var(--border-nhat);">
              <strong style="color:var(--mau-chinh-toi); font-size:0.95rem;">Thông tin người đặt:</strong>
              <div class="mt-2">Họ tên: <strong><?= e($don['ho_ten']) ?></strong></div>
              <div class="mt-1">Email: <?= e($don['email']) ?></div>
              <div class="mt-1">Số điện thoại: <?= e($don['so_dien_thoai'] ?? 'Chưa cung cấp') ?></div>
            </div>
            <?php if (!empty($don['ghi_chu'])): ?>
              <div style="flex:1 1 280px; background:var(--nen-section); padding:18px; border-radius:12px; font-size:0.875rem; border:1px solid var(--border-nhat);">
                <strong style="color:var(--mau-chinh-toi); font-size:0.95rem;">Ghi chú đính kèm:</strong>
                <p class="mt-2 mb-0 text-phu" style="font-style:italic; line-height:1.6;"><?= e($don['ghi_chu']) ?></p>
              </div>
            <?php endif; ?>
          </div>

          <h2 class="h5 mb-3 font-hien-thi fw-700" style="color:var(--mau-chinh-toi);">
            Danh Sách Sự Kiện Đã Đặt
          </h2>

          <div class="bang-wrapper mb-4">
            <table style="width:100%; min-width: 580px; border-collapse:collapse;">
              <thead>
                <tr>
                  <th style="padding:10px 12px; text-align:left;">Sự kiện</th>
                  <th style="padding:10px 12px; text-align:center;">Đơn giá lúc đặt</th>
                  <th style="padding:10px 12px; text-align:center;">Số lượng</th>
                  <th style="padding:10px 12px; text-align:right;">Thành tiền</th>
                </tr>
              </thead>
              <tbody>
                <?php $tongDiem = 0; foreach ($danhSachVe as $item): ?>
                  <?php
                    $tongDiem += ((int)$item['diem_ren_luyen']) * ((int)$item['so_luong']);
                    $thanhTien = ((int)$item['don_gia']) * ((int)$item['so_luong']);
                  ?>
                  <tr>
                    <td style="padding:12px;">
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?= e($item['anh']) ?>" alt="" width="56" height="38" style="border-radius:6px; object-fit:cover; border:1px solid var(--border-nhat);">
                        <div>
                          <strong class="text-chinh"><?= e($item['ten']) ?></strong>
                          <div class="text-phu fs-meta mt-1" style="font-size:0.75rem;">
                            <?= uiIcon('calendar', 'icon-muted', 13) ?> <?= date('d/m/Y H:i', strtotime($item['thoi_gian'])) ?> | <?= uiIcon('pin', 'icon-muted', 13) ?> <?= e($item['dia_diem']) ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td style="padding:12px; text-align:center;">
                      <?= vnd((int)$item['don_gia']) ?>
                    </td>
                    <td style="padding:12px; text-align:center; font-weight:600;">
                      <?= $item['so_luong'] ?> vé
                    </td>
                    <td style="padding:12px; text-align:right; font-weight:700; color:var(--mau-chinh);">
                      <?= vnd($thanhTien) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="background:var(--nen-section); font-size:1.05rem;">
                  <th colspan="3" style="padding:14px 12px; text-align:right; font-weight:700;">Tổng thanh toán:</th>
                  <td style="padding:14px 12px; text-align:right; font-weight:800; color:var(--mau-chinh); font-size:1.25rem;">
                    <?= vnd((int)$don['tong_tien']) ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">
            <a href="don-hang.php" class="nut-bam nut-phu" style="padding: 10px 18px;">← Quay lại danh sách đơn</a>
            <div style="font-size:0.9rem; color:#16a34a; font-weight:600; background:rgba(34,197,94,0.12); padding:8px 14px; border-radius:8px; border:1px solid rgba(34,197,94,0.25);">
              <?= uiIcon('check', 'icon-success', 14) ?> Tổng điểm rèn luyện tích lũy: +<?= $tongDiem ?> Điểm
            </div>
          </div>
        </div>
      </div>
    </main>
    <?php
    require __DIR__ . '/inc/footer.php';
    exit;
}

if ($laAdmin) {
    $sql = 'SELECT d.*, u.ho_ten, u.email,
                   COUNT(ct.su_kien_id) AS so_mon,
                   COALESCE(SUM(ct.so_luong), 0) AS tong_ve
            FROM don_hang d
            JOIN nguoi_dung u ON d.nguoi_dung_id = u.id
            LEFT JOIN chi_tiet_don ct ON d.id = ct.don_hang_id
            GROUP BY d.id
            ORDER BY d.ngay_dat DESC';
    $st = db()->query($sql);
    $danhSachDon = $st->fetchAll();
} else {
    $sql = 'SELECT d.*, u.ho_ten, u.email,
                   COUNT(ct.su_kien_id) AS so_mon,
                   COALESCE(SUM(ct.so_luong), 0) AS tong_ve
            FROM don_hang d
            JOIN nguoi_dung u ON d.nguoi_dung_id = u.id
            LEFT JOIN chi_tiet_don ct ON d.id = ct.don_hang_id
            WHERE d.nguoi_dung_id = ?
            GROUP BY d.id
            ORDER BY d.ngay_dat DESC';
    $st = db()->prepare($sql);
    $st->execute([$uid]);
    $danhSachDon = $st->fetchAll();
}

$thongBaoFlash = flash();
$tieuDe = $laAdmin ? 'Quản lý danh sách đơn hàng - UniEvent' : 'Đơn hàng của tôi - UniEvent';
$trang  = 'don-hang';

require __DIR__ . '/inc/header.php';
?>

<main class="vung-chinh noi-dung-chinh container py-5">
  <div class="trang-tieu-de mb-4">
    <nav class="breadcrumb mb-3" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai"><?= $laAdmin ? 'Quản trị đơn hàng' : 'Đơn hàng của tôi' ?></span>
    </nav>
    <h1 class="font-hien-thi fw-800 text-chinh mb-2"><?= $laAdmin ? 'Quản Trị Toàn Bộ Đơn Hàng' : 'Đơn Hàng Của Tôi' ?></h1>
    <p class="do-dai-chuan text-phu" style="font-size: 1.05rem;">
      <?= $laAdmin
          ? 'Quản trị viên có thể theo dõi tiến độ đăng ký vé, cập nhật trạng thái đơn và xem chi tiết phản hồi.'
          : 'Danh sách các vé và đơn đăng ký sự kiện bạn đã thực hiện trên cổng thông tin UniEvent.' ?>
    </p>
  </div>

  <?php if ($thongBaoFlash): ?>
    <div class="hop-thong-bao mb-4" role="alert" style="background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.3); color:var(--mau-chinh); padding:14px 20px; border-radius:12px; box-shadow:var(--shadow-sm);">
      <?= e($thongBaoFlash) ?>
    </div>
  <?php endif; ?>

  <?php if (empty($danhSachDon)): ?>
    <div class="p-5 text-center my-4" style="background:var(--nen-surface); border-radius:16px; border:1px dashed var(--border-dam); box-shadow:var(--shadow-sm);">
      <div style="margin-bottom:1rem; color:var(--mau-chinh);"><?= uiIcon('box', '', 48) ?></div>
      <h2 class="h4" style="color:var(--mau-chinh-toi); font-weight:700;">Bạn chưa có đơn đặt vé nào</h2>
      <p class="text-phu mb-4" style="max-width:500px; margin-left:auto; margin-right:auto;">
        Hãy duyệt danh sách sự kiện hấp dẫn và đăng ký vé tham gia để theo dõi đơn hàng tại đây.
      </p>
      <a href="danh-sach.php" class="nut-bam nut-nhan" style="padding: 10px 24px;">Khám phá sự kiện ngay</a>
    </div>
  <?php else: ?>
    <div class="bang-card mb-4">
      <div class="bang-card__header">
        <h2 class="bang-card__tieu-de">
          <?= uiIcon('ticket', 'icon-brand', 18) ?>
          <span>Danh sách các đơn đặt vé trong hệ thống</span>
        </h2>
        <span class="bang-card__dem"><?= count($danhSachDon) ?> đơn hàng</span>
      </div>
      <div class="bang-wrapper">
        <table style="width:100%; min-width: 700px;">
        <thead>
          <tr>
            <th scope="col">Mã đơn</th>
            <?php if ($laAdmin): ?>
              <th scope="col">Khách hàng</th>
            <?php endif; ?>
            <th scope="col">Ngày đặt</th>
            <th scope="col" style="text-align:center;">Số vé</th>
            <th scope="col" style="text-align:right;">Tổng tiền</th>
            <th scope="col" style="text-align:center;">Trạng thái</th>
            <th scope="col" style="text-align:center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($danhSachDon as $d): ?>
            <?php
            $nhanTrangThai = [
                'cho_xac_nhan'  => ['Chờ xác nhận', 'rgba(245,158,11,0.15)', '#d97706', 'rgba(245,158,11,0.3)'],
                'da_thanh_toan' => ['Đã thanh toán', 'rgba(34,197,94,0.15)', '#16a34a', 'rgba(34,197,94,0.3)'],
                'hoan_thanh'    => ['Hoàn thành', 'rgba(59,130,246,0.15)', '#2563eb', 'rgba(59,130,246,0.3)'],
                'da_huy'        => ['Đã hủy', 'rgba(239,68,68,0.15)', '#ef4444', 'rgba(239,68,68,0.3)'],
            ];
            $tt = $nhanTrangThai[$d['trang_thai']] ?? ['Không rõ', 'var(--nen-section)', 'var(--chu-phu)', 'var(--border-nhat)'];
            ?>
            <tr>
              <td>
                <a href="don-hang.php?id=<?= $d['id'] ?>" class="fw-bold" style="color:var(--mau-chinh); text-decoration:none;">
                  <?= e($d['ma_don']) ?>
                </a>
              </td>
              <?php if ($laAdmin): ?>
                <td>
                  <strong class="text-chinh"><?= e($d['ho_ten']) ?></strong>
                  <div class="fs-meta text-phu" style="font-size:0.75rem;"><?= e($d['email']) ?></div>
                </td>
              <?php endif; ?>
              <td>
                <?= uiIcon('clock', 'icon-muted', 13) ?> <?= date('d/m/Y H:i', strtotime($d['ngay_dat'])) ?>
              </td>
              <td style="text-align:center;">
                <?= $d['tong_ve'] ?> vé (<?= $d['so_mon'] ?> sự kiện)
              </td>
              <td style="text-align:right; font-weight:700; color:var(--mau-chinh);">
                <?= vnd((int)$d['tong_tien']) ?>
              </td>
              <td style="text-align:center;">
                <?php if ($laAdmin): ?>
                  <form action="don-hang.php" method="POST" style="margin:0; display:inline-flex; gap:6px; align-items:center;">
                    <input type="hidden" name="don_id" value="<?= $d['id'] ?>">
                    <select name="trang_thai" class="form-control" style="font-size:0.75rem; padding:4px 6px; border-radius:6px; border:1px solid var(--border-nhat);">
                      <option value="cho_xac_nhan" <?= $d['trang_thai'] === 'cho_xac_nhan' ? 'selected' : '' ?>>Chờ xác nhận</option>
                      <option value="da_thanh_toan" <?= $d['trang_thai'] === 'da_thanh_toan' ? 'selected' : '' ?>>Đã thanh toán</option>
                      <option value="hoan_thanh" <?= $d['trang_thai'] === 'hoan_thanh' ? 'selected' : '' ?>>Hoàn thành</option>
                      <option value="da_huy" <?= $d['trang_thai'] === 'da_huy' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                    <button type="submit" class="nut-bam nut-phu nut-nho" style="padding:4px 8px; font-size:0.75rem;">Lưu</button>
                  </form>
                <?php else: ?>
                  <span class="badge" style="background:<?= $tt[1] ?>; color:<?= $tt[2] ?>; border:1px solid <?= $tt[3] ?>; padding:5px 10px; font-weight:600; border-radius:var(--radius-pill);">
                    <?= e($tt[0]) ?>
                  </span>
                <?php endif; ?>
              </td>
              <td style="text-align:center;">
                <a href="don-hang.php?id=<?= $d['id'] ?>" class="nut-bam nut-phu nut-nho" style="padding:6px 14px;">
                  Chi tiết
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>

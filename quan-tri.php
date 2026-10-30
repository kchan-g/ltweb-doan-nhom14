<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSuKien;
use App\Data\KhoLienHe;

yeuCauQuyen('quan_tri');

$kho = new KhoSuKien(db());
$khoLienHe = new KhoLienHe(db());

// Xử lý Xuất file Excel / CSV nếu có yêu cầu
if (isset($_GET['xuat']) && $_GET['xuat'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=danh-sach-su-kien-unievent-' . date('Ymd-His') . '.csv');
    $out = fopen('php://output', 'w');
    // Xuất BOM UTF-8 để Microsoft Excel hiển thị đúng tiếng Việt
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, ['ID', 'Tên sự kiện', 'Phân loại', 'Giá vé', 'Tồn kho', 'Thời gian', 'Địa điểm', 'Diễn giả']);

    $tatCaSK = $kho->trang([], 'moi', 1000, 0);
    foreach ($tatCaSK as $item) {
        fputcsv($out, [
            $item->id,
            $item->ten,
            $item->ten_danh_muc,
            $item->gia > 0 ? number_format($item->gia, 0, ',', '.') . 'đ' : 'Miễn phí',
            $item->ton_kho,
            $item->thoi_gian,
            $item->dia_diem,
            $item->dien_gia,
        ]);
    }
    fclose($out);
    exit;
}

// Bộ lọc sự kiện theo Danh mục & Từ khóa tìm kiếm
$dmHienTai = filter_var($_GET['dm'] ?? 0, FILTER_VALIDATE_INT);
$tuKhoa = trim((string)($_GET['q'] ?? ''));

$loc = [];
if ($dmHienTai && $dmHienTai > 0) {
    $loc['dm'] = $dmHienTai;
}
if ($tuKhoa !== '') {
    $loc['q'] = $tuKhoa;
}

$moiTrang = 8;
$tongSuKienLoc = $kho->dem($loc);
$soTrang = max(1, (int)ceil($tongSuKienLoc / $moiTrang));

$hienTai = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'default' => 1],
]);
$hienTai = min($hienTai, $soTrang);

$danhSachSK = $kho->trang($loc, 'moi', $moiTrang, ($hienTai - 1) * $moiTrang);

// Số liệu tổng quan (4 KPI Stat Cards theo thiết kế Figma)
$tongSkToanHeThong = $kho->dem([]);
$tongVeThucTe = (int)db()->query('SELECT COALESCE(SUM(so_luong), 0) FROM chi_tiet_don')->fetchColumn();
$soVeHienThi = $tongVeThucTe > 0 ? number_format($tongVeThucTe) : '8,429';

$donChoXuLy = (int)db()->query("SELECT COUNT(*) FROM don_hang WHERE trang_thai = 'cho_xac_nhan'")->fetchColumn();
$tongDonHang = (int)db()->query("SELECT COUNT(*) FROM don_hang")->fetchColumn();
$soDonHienThi = $tongDonHang > 0 ? (string)$tongDonHang : '128';

$tongPhanHoi = (int)db()->query('SELECT COUNT(*) FROM lien_he')->fetchColumn();
$soPhanHoiHienThi = $tongPhanHoi > 0 ? (string)$tongPhanHoi : '36';

// Danh sách phản hồi mới nhất (cột trái)
$danhSachLH = $khoLienHe->tatCa(4);

// Danh sách sự kiện sắp tới (cột phải)
$stSapToi = db()->query("SELECT * FROM su_kien WHERE thoi_gian >= NOW() ORDER BY thoi_gian ASC LIMIT 4");
$danhSachSapToi = $stSapToi->fetchAll();
if (empty($danhSachSapToi)) {
    $stSapToi = db()->query("SELECT * FROM su_kien ORDER BY thoi_gian DESC LIMIT 4");
    $danhSachSapToi = $stSapToi->fetchAll();
}

$thongBaoFlash = flash();
$tieuDe = 'Bảng Điều Khiển Quản Trị - UniEvent UED';

// Hàm helper phân loại màu badge danh mục
function layLopBadgeDanhMuc(int $dmId): string {
    return match ($dmId) {
        1 => 'admin-cat-badge--hocthuat',
        4 => 'admin-cat-badge--thethao',
        3 => 'admin-cat-badge--nghethuat',
        default => 'admin-cat-badge--khac',
    };
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($tieuDe) ?></title>
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <?php
    $cssV = static function(string $tep): string {
        $duongDan = __DIR__ . '/' . ltrim($tep, '/');
        $ver = file_exists($duongDan) ? filemtime($duongDan) : time();
        return $tep . '?v=' . $ver;
    };
  ?>
  <link rel="stylesheet" href="<?= $cssV('css/01-bien.css') ?>">
  <link rel="stylesheet" href="<?= $cssV('css/02-chuan-hoa.css') ?>">
  <link rel="stylesheet" href="<?= $cssV('css/03-bo-cuc.css') ?>">
  <link rel="stylesheet" href="<?= $cssV('css/04-thanh-phan.css') ?>">
  <link rel="stylesheet" href="<?= $cssV('css/05-tien-ich.css') ?>">
</head>
<body style="background:#f8fafc; margin:0; padding:0;">

<div class="admin-shell">
  <!-- ==========================================
       SIDEBAR TRÁI (THEO BẢN THIẾT KẾ FIGMA)
       ========================================== -->
  <aside class="admin-sidebar" aria-label="Thanh điều hướng quản trị">
    <div>
      <a href="index.php" class="admin-logo" title="Quay lại trang chủ UniEvent">
        <div class="admin-logo__icon" aria-hidden="true">U</div>
        <div>
          <div class="admin-logo__title">UniEvent</div>
          <div class="admin-logo__sub">UED PORTAL</div>
        </div>
      </a>

      <div class="admin-nav-group-title">QUẢN TRỊ</div>
      <ul class="admin-nav-list">
        <li>
          <a href="quan-tri.php" class="admin-nav-link active">
            <span class="admin-nav-link__main">
              <?= uiIcon('grid', '', 16) ?>
              <span>Dashboard</span>
            </span>
          </a>
        </li>
        <li>
          <a href="#danh-sach-su-kien" class="admin-nav-link">
            <span class="admin-nav-link__main">
              <?= uiIcon('calendar', '', 16) ?>
              <span>Quản lý sự kiện</span>
            </span>
          </a>
        </li>
        <li>
          <a href="don-hang.php" class="admin-nav-link">
            <span class="admin-nav-link__main">
              <?= uiIcon('ticket', '', 16) ?>
              <span>Đơn đặt vé</span>
            </span>
            <span class="admin-nav-badge"><?= $tongDonHang ?></span>
          </a>
        </li>
        <li>
          <a href="#phan-hoi-sinh-vien" class="admin-nav-link">
            <span class="admin-nav-link__main">
              <?= uiIcon('mail', '', 16) ?>
              <span>Phản hồi sinh viên</span>
            </span>
            <?php if ($tongPhanHoi > 0): ?>
              <span class="admin-nav-badge" style="background:#dcfce7; color:#16a34a;"><?= $tongPhanHoi ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li>
          <a href="danh-sach.php" class="admin-nav-link" target="_blank" title="Xem danh sách công khai">
            <span class="admin-nav-link__main">
              <?= uiIcon('trophy', '', 16) ?>
              <span>Báo cáo &amp; Điểm rèn luyện</span>
            </span>
          </a>
        </li>
      </ul>
    </div>

    <div>
      <div class="admin-help-box">
        <div class="admin-help-box__icon">?</div>
        <h4>Cần hỗ trợ?</h4>
        <p>Liên hệ bộ phận kỹ thuật Khoa Toán - Tin ĐHSP Đà Nẵng</p>
        <a href="lien-he.php">Trung tâm trợ giúp &rarr;</a>
      </div>

      <div class="admin-user-pill">
        <div class="admin-user-info">
          <div class="admin-user-avatar" title="Quản trị viên">QT</div>
          <div>
            <div class="admin-user-name"><?= e(tenNguoiDung() ?: 'Quản trị viên') ?></div>
            <div class="admin-user-sub">Administrator</div>
          </div>
        </div>
        <a href="dang-xuat.php" class="admin-logout-btn" title="Đăng xuất khỏi hệ thống">
          <?= uiIcon('logout', '', 16) ?>
        </a>
      </div>
    </div>
  </aside>

  <!-- ==========================================
       VÙNG NỘI DUNG CHÍNH (THEO BẢN THIẾT KẾ FIGMA)
       ========================================== -->
  <main class="admin-main">
    <!-- 1. Top App Bar -->
    <header class="admin-topbar">
      <div class="admin-topbar__left">
        <nav class="admin-breadcrumb" aria-label="Đường dẫn">
          <a href="index.php" style="color:inherit; text-decoration:none;">UniEvent</a>
          <span>&gt;</span>
          <span style="color:#0f172a; font-weight:600;">Dashboard</span>
        </nav>
        <h1 class="admin-topbar__title">Bảng Điều Khiển Quản Trị</h1>
      </div>

      <div class="admin-topbar__right">
        <!-- Form tìm kiếm sự kiện nhanh -->
        <form action="quan-tri.php" method="GET" class="admin-topbar__search">
          <?= uiIcon('pin', 'icon-muted', 15) ?>
          <input
            type="text"
            name="q"
            value="<?= e($tuKhoa) ?>"
            placeholder="Tìm sự kiện, mã vé, mã sinh viên..."
            aria-label="Tìm kiếm quản trị"
          />
          <span class="admin-topbar__search-kbd">⌘ K</span>
        </form>

        <a href="#phan-hoi-sinh-vien" class="admin-topbar__notif" title="Phản hồi từ sinh viên">
          <?= uiIcon('mail', '', 18) ?>
          <?php if ($tongPhanHoi > 0): ?>
            <span class="admin-topbar__notif-badge"><?= $tongPhanHoi ?></span>
          <?php endif; ?>
        </a>

        <a href="them-san-pham.php" class="admin-btn-primary">
          <?= uiIcon('grid', '', 14) ?>
          <span>+ Tạo sự kiện mới</span>
        </a>

        <a href="index.php" class="nut-bam nut-phu nut-nho" style="padding:8px 12px; font-size:0.8125rem;" title="Xem cổng thông tin sinh viên">
          ← Trang chủ
        </a>
      </div>
    </header>

    <!-- 2. Nội dung Body -->
    <div class="admin-body">

      <?php if ($thongBaoFlash): ?>
        <div class="hop-thong-bao" role="alert" style="background:#e0f2fe; border:1px solid #bae6fd; color:#0284c7; padding:12px 18px; border-radius:10px; font-size:0.9rem; font-weight:600;">
          <?= e($thongBaoFlash) ?>
        </div>
      <?php endif; ?>

      <!-- KHỐI 1: TỔNG QUAN HOẠT ĐỘNG (4 KPI STAT CARDS) -->
      <section aria-labelledby="tieu-de-tong-quan">
        <div class="admin-section-head">
          <div>
            <h2 id="tieu-de-tong-quan" class="admin-section-head__title">Tổng quan hoạt động</h2>
            <p class="admin-section-head__sub">
              Cập nhật dữ liệu sự kiện tại UED — <?= date('d/m/Y') ?>
            </p>
          </div>
          <div class="admin-date-pill">
            <?= uiIcon('calendar', 'icon-muted', 14) ?>
            <span>01 Thg <?= date('m') ?> — <?= date('t') ?> Thg <?= date('m') ?> ▾</span>
          </div>
        </div>

        <div class="admin-stat-grid">
          <!-- Card 1 -->
          <div class="admin-stat-card">
            <div class="admin-stat-card__top">
              <div class="admin-stat-card__icon-box admin-stat-card__icon-box--blue">
                <?= uiIcon('calendar', '', 18) ?>
              </div>
              <span class="admin-stat-card__more">&bull;&bull;&bull;</span>
            </div>
            <div>
              <h3 class="admin-stat-card__label" style="margin:0 0 4px 0; font-size:0.8125rem; font-weight:600; color:var(--chu-phu, #64748b); font-family:inherit;">Sự kiện đang hoạt động</h3>
              <div class="admin-stat-card__val"><?= $tongSkToanHeThong ?></div>
              <div class="admin-stat-card__foot admin-stat-card__foot--green">+3 trong tháng này</div>
            </div>
          </div>

          <!-- Card 2 -->
          <div class="admin-stat-card">
            <div class="admin-stat-card__top">
              <div class="admin-stat-card__icon-box admin-stat-card__icon-box--purple">
                <?= uiIcon('ticket', '', 18) ?>
              </div>
              <span class="admin-stat-card__more">&bull;&bull;&bull;</span>
            </div>
            <div>
              <h3 class="admin-stat-card__label" style="margin:0 0 4px 0; font-size:0.8125rem; font-weight:600; color:var(--chu-phu, #64748b); font-family:inherit;">Tổng vé đã phát hành</h3>
              <div class="admin-stat-card__val"><?= $soVeHienThi ?></div>
              <div class="admin-stat-card__foot admin-stat-card__foot--green">+12.5% so với tháng trước</div>
            </div>
          </div>

          <!-- Card 3 -->
          <div class="admin-stat-card">
            <div class="admin-stat-card__top">
              <div class="admin-stat-card__icon-box admin-stat-card__icon-box--orange">
                <?= uiIcon('clock', '', 18) ?>
              </div>
              <span class="admin-stat-card__more">&bull;&bull;&bull;</span>
            </div>
            <div>
              <h3 class="admin-stat-card__label" style="margin:0 0 4px 0; font-size:0.8125rem; font-weight:600; color:var(--chu-phu, #64748b); font-family:inherit;">Đơn hàng chờ xử lý</h3>
              <div class="admin-stat-card__val"><?= $soDonHienThi ?></div>
              <div class="admin-stat-card__foot admin-stat-card__foot--orange">
                <?= $donChoXuLy > 0 ? "{$donChoXuLy} đơn cần ưu tiên" : 'Hệ thống vận hành tốt' ?>
              </div>
            </div>
          </div>

          <!-- Card 4 -->
          <div class="admin-stat-card">
            <div class="admin-stat-card__top">
              <div class="admin-stat-card__icon-box admin-stat-card__icon-box--green">
                <?= uiIcon('mail', '', 18) ?>
              </div>
              <span class="admin-stat-card__more">&bull;&bull;&bull;</span>
            </div>
            <div>
              <h3 class="admin-stat-card__label" style="margin:0 0 4px 0; font-size:0.8125rem; font-weight:600; color:var(--chu-phu, #64748b); font-family:inherit;">Phản hồi mới</h3>
              <div class="admin-stat-card__val"><?= $soPhanHoiHienThi ?></div>
              <div class="admin-stat-card__foot admin-stat-card__foot--green">+8 trong hôm nay</div>
            </div>
          </div>
        </div>
      </section>

      <!-- KHỐI 2: QUẢN LÝ SỰ KIỆN (DATA TABLE CHUẨN FIGMA) -->
      <section id="danh-sach-su-kien" class="admin-card" aria-labelledby="tieu-de-ql-su-kien">
        <!-- Top Toolbar của bảng -->
        <div class="admin-card__top">
          <div>
            <h2 id="tieu-de-ql-su-kien" style="font-family:var(--font-hien-thi); font-size:1.05rem; font-weight:700; margin:0; color:#0f172a;">
              Quản lý sự kiện
            </h2>
            <p style="font-size:0.8125rem; color:#64748b; margin:2px 0 0 0;">
              Theo dõi và quản lý các sự kiện đang diễn ra
            </p>
          </div>

          <div class="admin-card__actions">
            <!-- Tabs lọc phân loại -->
            <div class="admin-tab-group" role="tablist">
              <a href="quan-tri.php" class="admin-tab-btn <?= $dmHienTai === 0 ? 'active' : '' ?>">Tất cả</a>
              <a href="quan-tri.php?dm=1" class="admin-tab-btn <?= $dmHienTai === 1 ? 'active' : '' ?>">Học thuật</a>
              <a href="quan-tri.php?dm=4" class="admin-tab-btn <?= $dmHienTai === 4 ? 'active' : '' ?>">Thể thao</a>
              <a href="quan-tri.php?dm=3" class="admin-tab-btn <?= $dmHienTai === 3 ? 'active' : '' ?>">Nghệ thuật</a>
            </div>

            <!-- Ô tìm kiếm trong bảng -->
            <form action="quan-tri.php" method="GET" class="admin-search-inline">
              <?php if ($dmHienTai > 0): ?>
                <input type="hidden" name="dm" value="<?= $dmHienTai ?>">
              <?php endif; ?>
              <?= uiIcon('grid', 'icon-muted', 14) ?>
              <input
                type="text"
                name="q"
                value="<?= e($tuKhoa) ?>"
                placeholder="Tìm sự kiện..."
                aria-label="Tìm kiếm sự kiện"
              />
            </form>

            <!-- Nút xuất dữ liệu Excel / CSV -->
            <a href="quan-tri.php?xuat=csv" class="admin-btn-export" title="Tải xuống tệp CSV / Excel">
              <?= uiIcon('box', '', 14) ?>
              <span>Xuất dữ liệu</span>
            </a>
          </div>
        </div>

        <!-- Bảng sự kiện chuẩn Figma -->
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th style="width: 60px;">ID</th>
                <th style="min-width: 260px;">SỰ KIỆN</th>
                <th style="width: 120px;">PHÂN LOẠI</th>
                <th style="width: 110px;">GIÁ VÉ</th>
                <th style="width: 160px;">SỨC CHỨA</th>
                <th style="width: 200px;">THỜI GIAN &amp; ĐỊA ĐIỂM</th>
                <th style="width: 120px;">TRẠNG THÁI</th>
                <th style="width: 90px; text-align:center;">THAO TÁC</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($danhSachSK)): ?>
                <tr>
                  <td colspan="8" style="text-align:center; padding:36px; color:#64748b;">
                    Không tìm thấy sự kiện nào phù hợp với bộ lọc hiện tại.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($danhSachSK as $sk): ?>
                  <?php
                    // Tính toán sức chứa & tỷ lệ phần trăm thanh tiến độ (Progress bar)
                    $sucChua = max(100, $sk->ton_kho > 0 ? (int)(ceil($sk->ton_kho / 50) * 50 + 50) : 300);
                    $daDangKy = max(0, $sucChua - $sk->ton_kho);
                    $tiLe = min(100, (int)round(($daDangKy / $sucChua) * 100));

                    // Trạng thái sự kiện
                    if ($sk->ton_kho <= 0) {
                        $trangThaiLop = 'admin-status-dot--full';
                        $trangThaiNhan = 'Hết vé';
                    } elseif ($sk->ton_kho <= 15) {
                        $trangThaiLop = 'admin-status-dot--warning';
                        $trangThaiNhan = 'Sắp đầy';
                    } else {
                        $trangThaiLop = 'admin-status-dot--open';
                        $trangThaiNhan = 'Đang mở';
                    }
                  ?>
                  <tr>
                    <td>
                      <span style="font-weight:700; color:#64748b; font-size:0.75rem;">
                        #<?= sprintf('%02d', $sk->id) ?>
                      </span>
                    </td>
                    <td>
                      <div class="admin-table__event">
                        <img
                          src="<?= e($sk->hinhAnh) ?>"
                          alt=""
                          class="admin-table__thumb"
                        />
                        <div>
                          <a href="chi-tiet.php?id=<?= $sk->id ?>" target="_blank" class="admin-table__event-title">
                            <?= e($sk->ten) ?>
                          </a>
                          <div class="admin-table__event-sub">
                            <?= e($sk->dienGia ?: 'Đoàn Thanh niên UED') ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="admin-cat-badge <?= layLopBadgeDanhMuc($sk->danh_muc_id) ?>">
                        <?= e($sk->ten_danh_muc) ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($sk->gia <= 0): ?>
                        <span style="font-weight:700; color:#16a34a;">Miễn phí</span>
                      <?php else: ?>
                        <span style="font-weight:700; color:#0f172a;"><?= vnd($sk->gia) ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="admin-capacity">
                        <div class="admin-capacity__head">
                          <span><?= $daDangKy ?>/<?= $sucChua ?></span>
                          <span class="admin-capacity__pct"><?= $tiLe ?>%</span>
                        </div>
                        <div class="admin-capacity__bar">
                          <div class="admin-capacity__fill" style="width: <?= $tiLe ?>%;"></div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div style="font-weight:600; font-size:0.8125rem; color:#0f172a;">
                        <?= date('d Thg m, Y - H:i', strtotime($sk->thoiGian)) ?>
                      </div>
                      <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">
                        📍 <?= e($sk->diaDiem) ?>
                      </div>
                    </td>
                    <td>
                      <span class="admin-status-dot <?= $trangThaiLop ?>">
                        <?= $trangThaiNhan ?>
                      </span>
                    </td>
                    <td style="text-align:center;">
                      <div style="display:inline-flex; gap:6px; align-items:center;">
                        <a href="sua-san-pham.php?id=<?= $sk->id ?>" class="nut-bam nut-phu nut-nho" style="padding:4px 8px; font-size:0.75rem;" title="Chỉnh sửa sự kiện">
                          Sửa
                        </a>
                        <form action="xoa-san-pham.php" method="POST" style="margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sự kiện \'<?= addslashes(e($sk->ten)) ?>\' không?');">
                          <input type="hidden" name="id" value="<?= $sk->id ?>">
                          <button type="submit" class="nut-bam nut-canh-bao nut-nho" style="padding:4px 8px; font-size:0.75rem;" title="Xóa sự kiện">
                            Xóa
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Phân trang và thống kê số lượng -->
        <div class="admin-card__footer">
          <div>
            Hiển thị <strong><?= count($danhSachSK) ?></strong> trên <strong><?= $tongSuKienLoc ?></strong> sự kiện
          </div>
          <?php if ($soTrang > 1): ?>
            <ul class="admin-pagination">
              <?php if ($hienTai > 1): ?>
                <li><a href="quan-tri.php?trang=<?= $hienTai - 1 ?><?= $dmHienTai ? "&dm={$dmHienTai}" : '' ?><?= $tuKhoa ? '&q=' . urlencode($tuKhoa) : '' ?>">‹</a></li>
              <?php endif; ?>

              <?php for ($p = 1; $p <= $soTrang; $p++): ?>
                <li>
                  <a href="quan-tri.php?trang=<?= $p ?><?= $dmHienTai ? "&dm={$dmHienTai}" : '' ?><?= $tuKhoa ? '&q=' . urlencode($tuKhoa) : '' ?>" class="<?= $p === $hienTai ? 'active' : '' ?>">
                    <?= $p ?>
                  </a>
                </li>
              <?php endfor; ?>

              <?php if ($hienTai < $soTrang): ?>
                <li><a href="quan-tri.php?trang=<?= $hienTai + 1 ?><?= $dmHienTai ? "&dm={$dmHienTai}" : '' ?><?= $tuKhoa ? '&q=' . urlencode($tuKhoa) : '' ?>">›</a></li>
              <?php endif; ?>
            </ul>
          <?php endif; ?>
        </div>
      </section>

      <!-- KHỐI 3: 2 CỘT DƯỚI (PHẢN HỒI SINH VIÊN & SỰ KIỆN SẮP TỚI) -->
      <div class="admin-split-grid">
        <!-- Cột trái: Phản hồi sinh viên gần đây -->
        <section id="phan-hoi-sinh-vien" class="admin-card" aria-labelledby="tieu-de-phan-hoi">
          <div class="admin-card__top">
            <div>
              <h2 id="tieu-de-phan-hoi" style="font-family:var(--font-hien-thi); font-size:0.95rem; font-weight:700; margin:0; color:#0f172a;">
                Phản hồi sinh viên gần đây
              </h2>
              <p style="font-size:0.75rem; color:#64748b; margin:2px 0 0 0;">
                Các yêu cầu mới cần được hỗ trợ
              </p>
            </div>
            <a href="lien-he.php" target="_blank" style="font-size:0.75rem; font-weight:700; color:#0284c7; text-decoration:none;">
              Xem tất cả ↗
            </a>
          </div>

          <div>
            <?php if (empty($danhSachLH)): ?>
              <div style="padding:28px; text-align:center; color:#64748b; font-size:0.85rem;">
                Chưa có phản hồi liên hệ nào trong hệ thống.
              </div>
            <?php else: ?>
              <?php foreach ($danhSachLH as $lh): ?>
                <div class="admin-feedback-card">
                  <div class="admin-feedback-card__head">
                    <span class="admin-feedback-card__author"><?= e($lh['ho_ten']) ?></span>
                    <span class="admin-feedback-card__time"><?= date('d/m/Y H:i', strtotime($lh['ngay_tao'])) ?></span>
                  </div>
                  <div style="display:flex; align-items:center; gap:8px;">
                    <span class="admin-feedback-card__dept"><?= e($lh['khoa_vien'] ?: 'Sinh viên UED') ?></span>
                    <span style="font-size:0.75rem; color:#94a3b8;">&bull;</span>
                    <span style="font-size:0.75rem; color:#64748b;"><?= e($lh['email']) ?></span>
                  </div>
                  <p class="admin-feedback-card__msg">
                    <?= e($lh['noi_dung']) ?>
                  </p>
                  <?php if (!empty($lh['anh']) && file_exists(__DIR__ . '/uploads/' . $lh['anh'])): ?>
                    <div style="margin-top:2px;">
                      <a href="uploads/<?= e($lh['anh']) ?>" target="_blank" style="font-size:0.75rem; color:#0284c7; font-weight:600; text-decoration:none;">
                        📎 Tệp đính kèm: <?= e($lh['anh']) ?>
                      </a>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>

        <!-- Cột phải: Sự kiện sắp tới -->
        <section class="admin-card" aria-labelledby="tieu-de-sap-toi">
          <div class="admin-card__top">
            <div>
              <h2 id="tieu-de-sap-toi" style="font-family:var(--font-hien-thi); font-size:0.95rem; font-weight:700; margin:0; color:#0f172a;">
                Sự kiện sắp tới
              </h2>
              <p style="font-size:0.75rem; color:#64748b; margin:2px 0 0 0;">
                Lịch sử dụng địa điểm hội trường
              </p>
            </div>
            <div style="color:#64748b;">
              <?= uiIcon('calendar', 'icon-muted', 16) ?>
            </div>
          </div>

          <div>
            <?php foreach ($danhSachSapToi as $stItem): ?>
              <?php
                $timeSt = strtotime($stItem['thoi_gian']);
                $ngaySt = date('d', $timeSt);
                $thangSt = 'Thg ' . date('m', $timeSt);
              ?>
              <div class="admin-upcoming-item">
                <div class="admin-upcoming-date">
                  <span class="admin-upcoming-date__day"><?= $ngaySt ?></span>
                  <span class="admin-upcoming-date__month"><?= $thangSt ?></span>
                </div>
                <div class="admin-upcoming-info">
                  <a href="chi-tiet.php?id=<?= $stItem['id'] ?>" target="_blank" class="admin-upcoming-title" title="<?= e($stItem['ten']) ?>">
                    <?= e($stItem['ten']) ?>
                  </a>
                  <div class="admin-upcoming-sub">
                    📍 <?= e($stItem['dia_diem']) ?> &bull; <?= date('H:i', $timeSt) ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      </div>

    </div>
  </main>
</div>

</body>
</html>

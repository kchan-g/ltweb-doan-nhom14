<?php
declare(strict_types=1);

$goc ??= '';
$trang ??= '';
$tieuDe ??= 'UniEvent';

$gio = new App\Services\GioHang();

$menu = [
    'index' => 'Trang chủ',
    'danh-sach' => 'Sự kiện',
    'gio-hang' => 'Phiếu đăng ký (' . $gio->soMon() . ')',
    'gioi-thieu' => 'Giới thiệu',
    'lien-he' => 'Liên hệ',
];

if (daDangNhap()) {
    $menu['don-hang'] = laAdmin() ? 'Quản lý đơn' : 'Đơn hàng của tôi';
}

if (laAdmin()) {
    $menu['quan-tri'] = 'Quản trị';
}

// Chỉ kích hoạt Dark Mode riêng cho chức năng cá nhân của Hưng
$duongDanHienTai = $_SERVER['SCRIPT_NAME'] ?? '';
$laTrangHung = str_contains($duongDanHienTai, '3120224065_Hung');
$laDarkHung = $laTrangHung && ((isset($bodyClass) && str_contains((string)$bodyClass, 'dark-theme')) || (($_COOKIE['hung_theme'] ?? '') === 'dark'));

$lopBody = trim($bodyClass ?? '');
if ($laDarkHung && !str_contains($lopBody, 'dark-theme')) {
    $lopBody .= ' dark-theme';
}
?>
<!DOCTYPE html>
<html lang="vi" <?= $laDarkHung ? 'class="dark-theme" data-theme="dark"' : '' ?>>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($tieuDe) ?> | UniEvent - ĐH Sư Phạm Đà Nẵng</title>
  <link rel="icon" type="image/svg+xml" href="<?= $goc ?>images/favicon.svg">

  <?php if ($laTrangHung): ?>
  <script>
    (function() {
      try {
        var t = localStorage.getItem('hung_profile_theme') || (document.cookie.indexOf('hung_theme=dark') !== -1 ? 'dark' : 'light');
        if (t === 'dark') {
          document.documentElement.classList.add('dark-theme');
          document.documentElement.setAttribute('data-theme', 'dark');
          document.addEventListener('DOMContentLoaded', function() {
            if (document.body) document.body.classList.add('dark-theme');
          });
        }
      } catch (e) {}
    })();
  </script>
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <?php
    $cssV = static function(string $tep) use ($goc): string {
        $duongDan = __DIR__ . '/../' . ltrim($tep, '/');
        $ver = file_exists($duongDan) ? filemtime($duongDan) : time();
        return $goc . $tep . '?v=' . $ver;
    };
  ?>
  <link rel="stylesheet" href="<?= $cssV('css/01-bien.css') ?>">
  <link rel="stylesheet" href="<?= $cssV('css/02-chuan-hoa.css') ?>">
  <link rel="stylesheet" href="<?= $cssV('css/03-bo-cuc.css') ?>">
  <link rel="stylesheet" href="<?= $cssV('css/04-thanh-phan.css') ?>">
  <link rel="stylesheet" href="<?= $cssV('css/05-tien-ich.css') ?>">
</head>
<body class="trang <?= e($lopBody) ?>" data-user-id="<?= idNguoiDung() ?? '' ?>">
  <!-- Thanh Thông Tin Cơ Quan - Đại học Sư phạm Đà Nẵng (Authentic University Topbar) -->
  <aside class="top-bar-truong" aria-label="Thông tin cơ quan trường">
    <div class="container d-flex justify-content-between align-items-center flex-wrap">
      <div class="top-bar__don-vi d-flex align-items-center gap-2">
        <span class="top-bar__icon" aria-hidden="true"><?= uiIcon("building", "icon-brand", 15) ?></span>
        <span class="fw-bold">Đoàn TNCS Hồ Chí Minh – Hội Sinh viên Trường ĐH Sư phạm, ĐHĐN</span>
        <span class="d-none-mobile text-muted">| <?= uiIcon("pin", "icon-muted", 13) ?> 459 Tôn Đức Thắng, TP. Đà Nẵng</span>
      </div>
      <div class="top-bar__lien-ket d-flex align-items-center gap-3">
        <span class="d-none-mobile"><?= uiIcon("phone", "icon-brand", 13) ?> Hỗ trợ SV: <strong>(0236) 3841 323</strong></span>
        <a href="https://ued.udn.vn" target="_blank" rel="noopener noreferrer" class="top-bar__link-truong">Cổng thông tin UED <?= uiIcon("external", "", 12) ?></a>
      </div>
    </div>
  </aside>

  <header class="vung-dau">
    <div class="container site-header__inner">
      <a href="<?= $goc ?>index.php" class="thuong-hieu">
        <img
          src="<?= $goc ?>images/logo.png"
          alt="Logo UniEvent - Cổng sự kiện Đại học Sư phạm Đà Nẵng"
          class="logo-img"
          width="44"
          height="44"
        />
        <div>
          <div class="thuong-hieu__ten">UniEvent</div>
          <div class="thuong-hieu__mota">Hệ thống Quản lý Sự kiện ĐHSP Đà Nẵng</div>
        </div>
      </a>

      <div class="header-phai">
        <button
          type="button"
          class="nut-menu"
          id="nut-menu"
          aria-label="Mở menu điều hướng"
          aria-expanded="false"
          aria-controls="menu-chinh"
        >
          <span aria-hidden="true"><?= uiIcon("menu", "", 18) ?></span>
        </button>

        <div class="search-box" role="search">
          <label for="tim-kiem-header" class="chi-danh-cho-sr">Tìm kiếm sự kiện</label>
          <input
            type="text"
            id="tim-kiem-header"
            placeholder="Tìm kiếm sự kiện, hội thảo..."
          />
          <button type="button" aria-label="Tìm kiếm">
            <svg
              width="15"
              height="15"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
              aria-hidden="true"
            >
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </button>
        </div>

        <div class="nhom-tien-ich-header" role="group" aria-label="Tiện ích vé và lưu trữ sự kiện">
          <a href="<?= $goc ?>gio-hang.php" class="nut-yeu-thich-header" title="Xem phiếu đăng ký">
            <span class="nut-tien-ich__noi-dung"><?= uiIcon("ticket", "icon-brand", 14) ?> <span class="chu-nhan">Phiếu đăng ký</span></span>
            <?php if ($gio->soMonChoXacNhan() > 0): ?>
              <span class="huy-hieu-dem" id="dem-gio-hang" style="background:#f59e0b; color:#ffffff;" aria-live="polite"><?= $gio->soMonChoXacNhan() ?></span>
            <?php endif; ?>
          </a>

          <a href="<?= $goc ?>gio-hang.php#da-xac-nhan" class="nut-yeu-thich-header" title="Danh sách sự kiện đã đăng ký">
            <span class="nut-tien-ich__noi-dung"><?= uiIcon("check", "icon-success", 14) ?> <span class="chu-nhan">Đã đăng ký</span></span>
            <span class="huy-hieu-dem" id="dem-da-dang-ky" style="background:#16a34a; color:#ffffff;" aria-live="polite"><?= $gio->soMonDaXacNhan() ?></span>
          </a>

          <a href="<?= $goc ?>danh-sach.php#yeu-thich" class="nut-yeu-thich-header" title="Danh sách sự kiện đã lưu">
            <span class="nut-tien-ich__noi-dung"><?= uiIcon("bookmark", "icon-gold", 14) ?> <span class="chu-nhan">Đã lưu</span></span>
            <span class="huy-hieu-dem" id="dem-yeu-thich" aria-live="polite">0</span>
          </a>
        </div>

        <div class="header-tai-khoan-wrap">
        <?php if (daDangNhap()): ?>
          <?php if (laAdmin()): ?>
            <div class="tai-khoan-admin" role="group" aria-label="Tài khoản Quản trị">
              <span class="badge-quantri" title="Quản trị viên hệ thống"><?= uiIcon("shield", "icon-brand", 14) ?> <?= e(tenNguoiDung()) ?></span>
              <a href="<?= $goc ?>quan-tri.php" class="nut-bam nut-phu nut-nho" style="padding: 4px 8px; font-size: 0.75rem;" title="Mở trang Quản trị">Quản trị</a>
              <a href="<?= $goc ?>don-hang.php" class="nut-bam nut-phu nut-nho" style="padding: 4px 8px; font-size: 0.75rem;" title="Xem các đơn hàng">Đơn hàng</a>
              <a href="<?= $goc ?>dang-xuat.php" class="nut-bam nut-canh-bao nut-nho" style="padding: 4px 8px; font-size: 0.75rem;" title="Đăng xuất khỏi hệ thống">Thoát</a>
            </div>
          <?php else: ?>
            <div class="tai-khoan-sinhvien d-flex align-items-center gap-2" role="group" aria-label="Tài khoản Sinh viên">
              <span class="fw-bold" style="font-size: 0.8125rem; color: var(--mau-chinh);"><?= uiIcon("user", "icon-brand", 15) ?> <?= e(tenNguoiDung()) ?></span>
              <a href="<?= $goc ?>don-hang.php" class="nut-bam nut-phu nut-nho" style="padding: 4px 10px; font-size: 0.75rem;" title="Xem vé đã đặt">Vé của tôi</a>
              <a href="<?= $goc ?>dang-xuat.php" class="nut-bam nut-canh-bao nut-nho" style="padding: 4px 10px; font-size: 0.75rem;" title="Đăng xuất tài khoản">Đăng xuất</a>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="tai-khoan" id="khu-vuc-tai-khoan" style="display: flex; gap: 8px; align-items: center;">
            <a href="<?= $goc ?>dang-nhap.php" class="nut-login-ued" style="text-decoration:none; display:inline-block;" title="Đăng nhập tài khoản">
              Đăng nhập
            </a>
            <a href="<?= $goc ?>dang-ky.php" class="nut-bam nut-phu nut-nho" style="padding: 6px 12px; font-size: 0.8125rem; text-decoration: none;" title="Đăng ký tài khoản mới">
              Đăng ký
            </a>
          </div>
        <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <nav
    class="vung-menu site-nav"
    id="menu-chinh"
    aria-label="Điều hướng chính"
  >
    <div class="container">
      <ul>
        <?php foreach ($menu as $tep => $nhan): ?>
          <li>
            <a href="<?= $goc . $tep ?>.php"
               <?= $tep === $trang ? 'class="active" aria-current="page"' : '' ?>>
              <?= e($nhan) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>

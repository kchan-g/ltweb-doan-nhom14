<?php
/**
 * inc/header.php
 * Khung đầu trang (Header & Navigation) dùng chung cho mọi trang trên website UniEvent,
 * bao gồm cả các trang chính ở thư mục gốc và trang thành viên trong thanhvien/<MSSV>_<ten>/.
 */

declare(strict_types=1);

$goc ??= '';
$trang ??= '';
$tieuDe ??= 'UniEvent';

// Khởi tạo đối tượng Giỏ hàng để đếm số vé đang có
$gio = new App\Services\GioHang();

// Danh sách các mục trong menu chính
$menu = [
    'index' => 'Trang chủ',
    'danh-sach' => 'Sự kiện',
    'gio-hang' => 'Phiếu đăng ký (' . $gio->soMon() . ')',
    'gioi-thieu' => 'Giới thiệu',
    'lien-he' => 'Liên hệ',
];

if (!empty($_SESSION['nguoi_dung']) && (($_SESSION['nguoi_dung']['vai_tro'] ?? '') === 'admin' || (function_exists('laAdmin') && laAdmin()))) {
    $menu['quan-tri'] = 'Quản trị';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($tieuDe) ?> | UniEvent - ĐH Sư Phạm Đà Nẵng</title>
  <link rel="icon" type="image/svg+xml" href="<?= $goc ?>images/favicon.svg">

  <!-- CSS Design System -->
  <link rel="stylesheet" href="<?= $goc ?>css/01-bien.css">
  <link rel="stylesheet" href="<?= $goc ?>css/02-chuan-hoa.css">
  <link rel="stylesheet" href="<?= $goc ?>css/03-bo-cuc.css">
  <link rel="stylesheet" href="<?= $goc ?>css/04-thanh-phan.css">
  <link rel="stylesheet" href="<?= $goc ?>css/05-tien-ich.css">
</head>
<body class="trang <?= e($bodyClass ?? '') ?>">
  <!-- Header (Vùng "dau") -->
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
          <div class="thuong-hieu__mota">
            Hệ thống Quản lý Sự kiện ĐHSP Đà Nẵng
          </div>
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
          <span aria-hidden="true">☰</span>
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

        <a href="<?= $goc ?>gio-hang.php" class="nut-yeu-thich-header" title="Xem phiếu đăng ký (sự kiện chờ xác nhận)">
          <span>Phiếu đăng ký</span>
          <?php if ($gio->soMonChoXacNhan() > 0): ?>
            <span class="huy-hieu-dem" id="dem-gio-hang" style="background:#f59e0b; color:#ffffff;" title="Có <?= $gio->soMonChoXacNhan() ?> sự kiện chờ xác nhận" aria-live="polite"><?= $gio->soMonChoXacNhan() ?></span>
          <?php endif; ?>
        </a>

        <a href="<?= $goc ?>gio-hang.php#da-xac-nhan" class="nut-yeu-thich-header" title="Danh sách sự kiện đã đăng ký thành công">
          <span>Đã đăng ký</span>
          <span class="huy-hieu-dem" id="dem-da-dang-ky" style="background:#16a34a; color:#ffffff;" aria-live="polite"><?= $gio->soMonDaXacNhan() ?></span>
        </a>

        <a href="<?= $goc ?>danh-sach.php#yeu-thich" class="nut-yeu-thich-header" title="Danh sách sự kiện đã lưu">
          <span>Đã lưu</span>
          <span class="huy-hieu-dem" id="dem-yeu-thich" aria-live="polite">0</span>
        </a>

        <!-- Khối Đăng nhập chung cho Quản trị viên và Sinh viên -->
        <?php 
          $u = $_SESSION['nguoi_dung'] ?? null;
          $daLogin = !empty($u) || (function_exists('daDangNhap') && daDangNhap());
          $isAdmin = ($u['vai_tro'] ?? '') === 'admin' || (function_exists('laAdmin') && laAdmin());
          $tenHienThi = $u['ten'] ?? (function_exists('tenNguoiDung') ? tenNguoiDung() : 'Người dùng');
        ?>
        <?php if ($daLogin): ?>
          <?php if ($isAdmin): ?>
            <!-- Giao diện khi đăng nhập bằng tài khoản Quản trị -->
            <div class="tai-khoan-admin d-flex align-items-center gap-2" role="group" aria-label="Tài khoản Quản trị">
              <span class="fw-bold text-chinh" style="font-size: 0.8125rem;">🛡️ <?= e($tenHienThi) ?></span>
              <a href="<?= $goc ?>quan-tri.php" class="nut-bam nut-phu nut-nho" style="padding: 3px 8px; font-size: 0.75rem;" title="Mở trang Quản trị">Quản trị</a>
              <a href="<?= $goc ?>dang-xuat.php" class="nut-bam nut-canh-bao nut-nho" style="padding: 3px 8px; font-size: 0.75rem;" title="Đăng xuất khỏi hệ thống">Thoát</a>
            </div>
          <?php else: ?>
            <!-- Giao diện khi đăng nhập bằng email Sinh viên -->
            <div class="tai-khoan-sinhvien d-flex align-items-center gap-2" role="group" aria-label="Tài khoản Sinh viên">
              <span class="fw-bold" style="font-size: 0.8125rem; color: var(--mau-chinh);">👤 <?= e($tenHienThi) ?></span>
              <a href="<?= $goc ?>dang-xuat.php" class="nut-bam nut-canh-bao nut-nho" style="padding: 3px 8px; font-size: 0.75rem;" title="Đăng xuất tài khoản">Đăng xuất</a>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <!-- Khi chưa đăng nhập: Duy nhất 1 khung / nút Đăng nhập chung -->
          <div class="tai-khoan" id="khu-vuc-tai-khoan">
            <a href="<?= $goc ?>dang-nhap.php" class="nut-login-ued" style="text-decoration:none; display:inline-block;" title="Đăng nhập dành cho Sinh viên và Quản trị viên">
              Đăng nhập
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Navigation (Vùng "menu") -->
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
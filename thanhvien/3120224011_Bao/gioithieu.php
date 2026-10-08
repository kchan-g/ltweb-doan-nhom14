<?php
/**
 * thanhvien/3120224011_Bao/gioithieu.php
 * Trang giới thiệu cá nhân: Nguyễn Hoài Bảo (MSSV: 3120224011 - Nhóm 14).
 * Chức năng máy chủ:
 * 1. Đặt lịch tư vấn học tập & Kết nối dự án (POST -> Kiểm tra máy chủ -> Lưu storage/3120224011_lienhe.jsonl -> PRG -> Flash Session).
 * 2. Bộ lọc & tìm kiếm dự án từ mảng PHP qua tham số GET (whitelist nhóm, mb_stripos, sắp xếp, chia sẻ URL).
 * Cách thử:
 * - Điền biểu mẫu đặt lịch tư vấn/kết nối (họ tên, email, chủ đề, nội dung), bấm gửi kiểm tra PRG, thông báo Flash và dữ liệu lưu file.
 * - Bấm các nút lọc danh mục (?nhom=frontend, backend, fullstack) hoặc nhập từ khóa tìm kiếm để xem máy chủ lọc danh sách dự án.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/config.php';

// Đường dẫn tương đối về thư mục gốc
$goc = '../../';
$tieuDe = 'Profile cá nhân - Nguyễn Hoài Bảo | UniEvent';
$trang = 'gioi-thieu';

// ============================================================================
// CHỨC NĂNG 1: ĐẶT LỊCH TƯ VẤN HỌC TẬP & KẾT NỐI DỰ ÁN (POST -> VALIDATION -> JSONL -> PRG)
// ============================================================================
$tepLienHeBao = __DIR__ . '/../../storage/3120224011_lienhe.jsonl';

// Khởi tạo thư mục storage nếu chưa có
if (!is_dir(dirname($tepLienHeBao))) {
    mkdir(dirname($tepLienHeBao), 0777, true);
}

$loiTuVan = $_SESSION['bao_tuvan_loi'] ?? [];
$duLieuTuVanCu = $_SESSION['bao_tuvan_cu'] ?? [
    'ho_ten' => '',
    'email' => '',
    'chu_de' => 'Học tập & Đồ án',
    'noi_dung' => '',
];
$thongBaoTuVan = $_SESSION['bao_tuvan_thanhcong'] ?? null;
unset($_SESSION['bao_tuvan_loi'], $_SESSION['bao_tuvan_cu'], $_SESSION['bao_tuvan_thanhcong']);

// Xử lý demo fallback nếu có tham số GET demo
if (isset($_GET['demo_tuvan']) && $thongBaoTuVan === null) {
    $thongBaoTuVan = 'Yêu cầu kết nối của bạn đã được tiếp nhận thành công vào sổ hẹn!';
}

// Xử lý gửi biểu mẫu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gui_tuvan'])) {
    $hoTen = trim((string)($_POST['ho_ten'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $chuDe = trim((string)($_POST['chu_de'] ?? ''));
    $noiDung = trim((string)($_POST['noi_dung'] ?? ''));

    $loi = [];

    // Kiểm tra họ tên
    if ($hoTen === '') {
        $loi['ho_ten'] = 'Vui lòng nhập họ và tên của bạn.';
    } elseif (mb_strlen($hoTen) < 2 || mb_strlen($hoTen) > 60) {
        $loi['ho_ten'] = 'Họ tên phải từ 2 đến 60 ký tự.';
    }

    // Kiểm tra email
    if ($email === '') {
        $loi['email'] = 'Vui lòng cung cấp địa chỉ email liên hệ.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loi['email'] = 'Địa chỉ email không đúng định dạng (ví dụ: student@ued.udn.vn).';
    }

    // Kiểm tra chủ đề theo whitelist
    $dsChuDeHopLe = ['Học tập & Đồ án', 'Dự án UniEvent', 'Cơ hội Thực tập / Hợp tác', 'Trao đổi Kỹ thuật Web'];
    if (!in_array($chuDe, $dsChuDeHopLe, true)) {
        $loi['chu_de'] = 'Chủ đề tư vấn không hợp lệ.';
    }

    // Kiểm tra nội dung
    if ($noiDung === '') {
        $loi['noi_dung'] = 'Vui lòng nhập nội dung chi tiết cần tư vấn hoặc kết nối.';
    } elseif (mb_strlen($noiDung) < 10 || mb_strlen($noiDung) > 800) {
        $loi['noi_dung'] = 'Nội dung phải từ 10 đến 800 ký tự.';
    }

    if (!empty($loi)) {
        $_SESSION['bao_tuvan_loi'] = $loi;
        $_SESSION['bao_tuvan_cu'] = [
            'ho_ten' => $hoTen,
            'email' => $email,
            'chu_de' => $chuDe,
            'noi_dung' => $noiDung,
        ];
        header('Location: gioithieu.php#tu-van');
        exit;
    }

    // Dữ liệu hợp lệ -> Lưu vào tệp storage/3120224011_lienhe.jsonl
    $banGhi = [
        'id' => 'LH' . date('YmdHis') . rand(100, 999),
        'ho_ten' => $hoTen,
        'email' => $email,
        'chu_de' => $chuDe,
        'noi_dung' => $noiDung,
        'ngay_gui' => date('d/m/Y H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
    ];

    $dongJson = json_encode($banGhi, JSON_UNESCAPED_UNICODE) . "\n";
    file_put_contents($tepLienHeBao, $dongJson, FILE_APPEND | LOCK_EX);

    $_SESSION['bao_tuvan_thanhcong'] = 'Yêu cầu kết nối của bạn đã được gửi thành công đến Nguyễn Hoài Bảo! Tôi sẽ phản hồi sớm nhất qua email ' . $email . '.';
    header('Location: gioithieu.php#tu-van');
    exit;
}

// Đọc danh sách 5 yêu cầu liên hệ / tư vấn gần nhất để hiển thị
$danhSachLienHeBao = [];
if (file_exists($tepLienHeBao) && is_readable($tepLienHeBao)) {
    $cacDong = file($tepLienHeBao, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($cacDong !== false) {
        $cacDong = array_reverse($cacDong);
        $cacDong = array_slice($cacDong, 0, 5);
        foreach ($cacDong as $dong) {
            $item = json_decode($dong, true);
            if (is_array($item)) {
                $danhSachLienHeBao[] = $item;
            }
        }
    }
}

// ============================================================================
// CHỨC NĂNG 2: DANH SÁCH DỰ ÁN & KỸ NĂNG TỪ MẢNG PHP (LỌC THEO THAM SỐ GET)
// ============================================================================
$danhSachDuAnGoc = [
    [
        'id' => 1,
        'ten' => 'UniEvent — Quản lý & Đặt vé Sự kiện ĐHSP',
        'nhom' => 'fullstack',
        'nhom_nhan' => 'Fullstack Web',
        'vai_tro' => 'Xây dựng trang Sự kiện, Bộ lọc GET, Trang cá nhân Bảo',
        'cong_nghe' => ['PHP 8.3', 'HTML5 Semantic', 'CSS Grid', 'JSON'],
        'nam' => 2026,
        'mo_ta' => 'Hệ thống nền tảng trực tuyến quản lý sự kiện và đặt vé dành cho sinh viên Trường Đại học Sư phạm - ĐHĐN.',
        'trang_thai' => 'Đã hoàn thành',
    ],
    [
        'id' => 2,
        'ten' => 'EduQuiz — Hệ thống Trắc nghiệm Tin học Trực tuyến',
        'nhom' => 'backend',
        'nhom_nhan' => 'Backend PHP',
        'vai_tro' => 'Thiết kế cơ sở dữ liệu, API chấm điểm tự động',
        'cong_nghe' => ['PHP', 'MySQL', 'PDO', 'Session Security'],
        'nam' => 2025,
        'mo_ta' => 'Nền tảng hỗ trợ giáo viên tin học tạo ngân hàng đề thi trắc nghiệm và thống kê kết quả học tập của học sinh.',
        'trang_thai' => 'Đã hoàn thành',
    ],
    [
        'id' => 3,
        'ten' => 'DesignSystem — Bộ UI Tokens & Thư viện Giao diện',
        'nhom' => 'frontend',
        'nhom_nhan' => 'Frontend / UI',
        'vai_tro' => 'Xây dựng biến CSS Tokens, tối ưu chuẩn a11y WCAG',
        'cong_nghe' => ['Modern CSS', 'CSS Variables', 'a11y', 'Figma'],
        'nam' => 2025,
        'mo_ta' => 'Bộ thư viện thành phần giao diện mẫu đáp ứng chuẩn trợ năng WCAG 2.1 AA, hỗ trợ Dark Mode và Mobile First.',
        'trang_thai' => 'Đã hoàn thành',
    ],
    [
        'id' => 4,
        'ten' => 'StudentSchedule — Ứng dụng Thời khóa biểu Sinh viên',
        'nhom' => 'frontend',
        'nhom_nhan' => 'Frontend / UI',
        'vai_tro' => 'Xây dựng bảng lịch cuộn ngang chống tràn trang',
        'cong_nghe' => ['HTML5 Table', 'Responsive CSS', 'Flexbox'],
        'nam' => 2025,
        'mo_ta' => 'Giao diện thời khóa biểu học tập 12 tiết trực quan, tối ưu trải nghiệm chạm vuốt trên mọi thiết bị di động.',
        'trang_thai' => 'Đã hoàn thành',
    ],
    [
        'id' => 5,
        'ten' => 'UED DataMiner — Khai phá Dữ liệu Điểm rèn luyện',
        'nhom' => 'backend',
        'nhom_nhan' => 'Backend & Data',
        'vai_tro' => 'Xử lý tiền dữ liệu, phân cụm sinh viên tích cực',
        'cong_nghe' => ['Python', 'PHP', 'Pandas', 'JSON'],
        'nam' => 2026,
        'mo_ta' => 'Dự án phân tích dữ liệu tham gia hoạt động Đoàn - Hội để tự động phân hạng điểm rèn luyện theo học kỳ.',
        'trang_thai' => 'Đang phát triển',
    ],
    [
        'id' => 6,
        'ten' => 'MobileCampus — Ứng dụng Sổ tay Sinh viên Di động',
        'nhom' => 'mobile',
        'nhom_nhan' => 'Mobile Web / PWA',
        'vai_tro' => 'Thiết kế giao diện PWA, tích hợp offline cache',
        'cong_nghe' => ['PWA', 'JavaScript ES6', 'Service Worker'],
        'nam' => 2026,
        'mo_ta' => 'Sổ tay hướng dẫn tân sinh viên tra cứu sơ đồ phòng học, quy chế đào tạo tín chỉ và lịch thi học kỳ.',
        'trang_thai' => 'Đang phát triển',
    ],
];

// Xử lý tham số GET
$nhomChon = trim((string)($_GET['nhom'] ?? 'all'));
$tuKhoa = trim((string)($_GET['tu_khoa'] ?? ''));
$sapXep = trim((string)($_GET['sap_xep'] ?? 'moi-nhat'));

$nhomHopLe = ['all', 'frontend', 'backend', 'fullstack', 'mobile'];
if (!in_array($nhomChon, $nhomHopLe, true)) {
    $nhomChon = 'all';
}

// Lọc dữ liệu bằng array_filter
$danhSachDuAn = array_filter($danhSachDuAnGoc, function (array $da) use ($nhomChon, $tuKhoa): bool {
    // 1. Lọc theo nhóm
    if ($nhomChon !== 'all' && $da['nhom'] !== $nhomChon) {
        return false;
    }
    // 2. Lọc theo từ khóa
    if ($tuKhoa !== '') {
        $khopTen = mb_stripos($da['ten'], $tuKhoa) !== false;
        $khopMoTa = mb_stripos($da['mo_ta'], $tuKhoa) !== false;
        $khopCongNghe = false;
        foreach ($da['cong_nghe'] as $cn) {
            if (mb_stripos($cn, $tuKhoa) !== false) {
                $khopCongNghe = true;
                break;
            }
        }
        if (!$khopTen && !$khopMoTa && !$khopCongNghe) {
            return false;
        }
    }
    return true;
});

// Sắp xếp dữ liệu
if ($sapXep === 'ten-az') {
    usort($danhSachDuAn, fn(array $a, array $b): int => strcmp($a['ten'], $b['ten']));
} else {
    // Mặc định mới nhất theo ID giảm dần
    usort($danhSachDuAn, fn(array $a, array $b): int => $b['id'] <=> $a['id']);
}

require __DIR__ . '/../../inc/header.php';
?>

<link rel="stylesheet" href="css/style.css">

<!-- HEADER PROFILE -->
<header class="profile-header">
  <div class="container">
    <span class="profile-header__badge">Thành viên Ban Nội dung &bull; UniEvent</span>
    <h1>Profile cá nhân</h1>
    <p>Chào mừng bạn đến trang giới thiệu của Nguyễn Hoài Bảo</p>
  </div>
</header>

<!-- NAVIGATION BAR -->
<nav class="profile-nav" aria-label="Điều hướng trang cá nhân">
  <div class="container">
    <ul>
      <li><a href="#about">Giới thiệu</a></li>
      <li><a href="#projects">Dự án &amp; Sở thích</a></li>
      <li><a href="#education">Học vấn</a></li>
      <li><a href="#skills">Kỹ năng</a></li>
      <li><a href="#schedule">Thời khóa biểu</a></li>
      <li><a href="#tinh-diem">Tính điểm HP</a></li>
      <li><a href="#contact">Liên hệ</a></li>
    </ul>
  </div>
</nav>

<!-- MAIN CONTENT -->
<main class="profile-main container">
  <!-- KHỐI 1: GIỚI THIỆU VÀ DỰ ÁN (BỐ CỤC GRID 2 CỘT TRÊN DESKTOP) -->
  <div class="profile-grid-top">
    <!-- Phân đoạn Giới thiệu bản thân và Ảnh đại diện -->
    <section
      id="about"
      class="profile-card about-section"
      aria-labelledby="about-heading"
    >
      <div class="avatar-wrapper">
        <img
          src="avatar.jpg"
          alt="Ảnh chân dung đại diện của Nguyễn Hoài Bảo"
          width="180"
          height="180"
          loading="lazy"
        />
      </div>
      <div class="about-info">
        <h2 id="about-heading">Giới Thiệu Bản Thân</h2>
        <span class="meta-pill">MSSV: 3120224011 &bull; Nhóm 14</span>
        <p>
          Xin chào! Mình là Nguyễn Hoài Bảo, sinh viên chuyên ngành Sư phạm
          Tin học / CNTT tại Trường Đại học Sư phạm - Đại học Đà Nẵng. Đam
          mê công nghệ, phát triển phần mềm, xây dựng các ứng dụng web và di
          động tối ưu trải nghiệm người dùng.
        </p>
      </div>
    </section>

    <!-- Phân đoạn Dự án và Sở thích -->
    <article
      id="projects-intro"
      class="profile-card"
      aria-labelledby="projects-intro-heading"
    >
      <h2 id="projects-intro-heading">Mục Tiêu &amp; Sở Thích</h2>
      <div class="projects-list">
        <div class="project-item">
          <p>
            <strong>Dự án tiêu biểu:</strong> Hệ thống website "UniEvent -
            Quản lý Sự kiện ĐHSP Đà Nẵng" (Phụ trách xây dựng và hoàn thiện
            trang Danh sách sự kiện, bộ lọc GET).
          </p>
        </div>
        <div class="project-item">
          <p>
            <strong>Sở thích:</strong> Lập trình web, nghiên cứu kiến trúc
            hệ thống máy chủ, tìm hiểu thuật toán và chơi thể thao.
          </p>
        </div>
        <div class="project-item">
          <p>
            <strong>Mục tiêu:</strong> Trở thành Fullstack / Backend
            Developer chuyên nghiệp, phát triển các giải pháp số ứng dụng
            trong giáo dục.
          </p>
        </div>
      </div>
    </article>
  </div>

  <!-- KHỐI 2: DỰ ÁN NĂNG LỰC TỪ MẢNG PHP (LỌC THEO GET) -->
  <section
    id="projects"
    class="profile-card projects-filter-section"
    aria-labelledby="projects-heading"
  >
    <div class="projects-card-header">
      <div>
        <span class="grade-badge">
          <span aria-hidden="true">📂</span>
          <span>Xử lý máy chủ (Server-side) &bull; Mảng PHP &amp; GET Filter</span>
        </span>
        <h2 id="projects-heading">Hồ Sơ Năng Lực &amp; Dự Án Tiêu Biểu</h2>
        <p class="projects-desc">
          Danh mục dự án được quản lý từ mảng PHP trên máy chủ. Bạn có thể chọn nhóm danh mục hoặc tìm kiếm theo từ khóa thông qua tham số GET chia sẻ được.
        </p>
      </div>
      <span class="project-count-pill">
        Tìm thấy: <strong><?= e(count($danhSachDuAn)) ?></strong> / <?= e(count($danhSachDuAnGoc)) ?> dự án
      </span>
    </div>

    <!-- Biểu mẫu lọc và tìm kiếm GET -->
    <form action="gioithieu.php" method="get" class="projects-filter-bar mb-4">
      <div class="filter-categories-list" role="tablist" aria-label="Lọc theo nhóm dự án">
        <?php
        $cacNhomTab = [
            'all' => 'Tất cả dự án',
            'fullstack' => 'Fullstack Web',
            'frontend' => 'Frontend / UI',
            'backend' => 'Backend & Data',
            'mobile' => 'Mobile / PWA',
        ];
        foreach ($cacNhomTab as $key => $label):
            $isActive = ($nhomChon === $key);
            $filterUrl = 'gioithieu.php?nhom=' . urlencode($key);
            if ($tuKhoa !== '') {
                $filterUrl .= '&tu_khoa=' . urlencode($tuKhoa);
            }
            if ($sapXep !== 'moi-nhat') {
                $filterUrl .= '&sap_xep=' . urlencode($sapXep);
            }
            $filterUrl .= '#projects';
        ?>
          <a
            href="<?= e($filterUrl) ?>"
            class="filter-tab-btn <?= $isActive ? 'active' : '' ?>"
            role="tab"
            aria-selected="<?= $isActive ? 'true' : 'false' ?>"
          >
            <?= e($label) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="filter-controls-row mt-3">
        <input type="hidden" name="nhom" value="<?= e($nhomChon) ?>">
        <div class="filter-search-box">
          <label for="tu_khoa" class="visually-hidden sr-only">Từ khóa tìm kiếm</label>
          <input
            type="text"
            id="tu_khoa"
            name="tu_khoa"
            class="form-control"
            placeholder="Tìm theo tên hoặc công nghệ (PHP, CSS, MySQL...)..."
            value="<?= e($tuKhoa) ?>"
          >
        </div>
        <div class="filter-sort-box">
          <label for="sap_xep" class="visually-hidden sr-only">Sắp xếp</label>
          <select id="sap_xep" name="sap_xep" class="form-control" onchange="this.form.submit()">
            <option value="moi-nhat" <?= $sapXep === 'moi-nhat' ? 'selected' : '' ?>>Mới nhất trước</option>
            <option value="ten-az" <?= $sapXep === 'ten-az' ? 'selected' : '' ?>>Tên dự án A - Z</option>
          </select>
        </div>
        <button type="submit" class="btn-action btn-action--compact">
          <span>Tìm kiếm</span>
        </button>
        <?php if ($nhomChon !== 'all' || $tuKhoa !== '' || $sapXep !== 'moi-nhat'): ?>
          <a href="gioithieu.php#projects" class="btn-reset-filter" title="Xóa bộ lọc">
            ✕ Đặt lại
          </a>
        <?php endif; ?>
      </div>
    </form>

    <!-- Danh sách thẻ dự án -->
    <?php if (empty($danhSachDuAn)): ?>
      <div class="filter-empty-state">
        <span class="empty-icon" aria-hidden="true">🔍</span>
        <p>Không tìm thấy dự án nào phù hợp với bộ lọc hiện tại.</p>
        <a href="gioithieu.php#projects" class="btn-action btn-action--secondary mt-2">Xem tất cả dự án</a>
      </div>
    <?php else: ?>
      <div class="projects-cards-grid">
        <?php foreach ($danhSachDuAn as $da): ?>
          <article class="project-card-item">
            <div class="project-card-item__top">
              <span class="project-category-badge project-category-badge--<?= e($da['nhom']) ?>">
                <?= e($da['nhom_nhan']) ?>
              </span>
              <span class="project-year-badge"><?= e($da['nam']) ?></span>
            </div>
            <h3 class="project-card-item__title"><?= e($da['ten']) ?></h3>
            <p class="project-card-item__desc"><?= e($da['mo_ta']) ?></p>
            <div class="project-card-item__meta">
              <p class="project-role-text"><strong>Vai trò:</strong> <?= e($da['vai_tro']) ?></p>
              <div class="project-tech-tags">
                <?php foreach ($da['cong_nghe'] as $tech): ?>
                  <span class="tech-chip"><?= e($tech) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- KHỐI 3: KỸ NĂNG VÀ THÔNG TIN BỔ SUNG (BỐ CỤC GRID 2 CỘT TRÊN DESKTOP) -->
  <div class="profile-grid-mid">
    <!-- Danh sách Kỹ năng (Flexbox / Grid dạng Chip Badge) -->
    <section
      id="skills"
      class="profile-card"
      aria-labelledby="skills-heading"
    >
      <h2 id="skills-heading">Kỹ Năng</h2>
      <ul class="skills-list">
        <li>HTML5 Semantic Tags &amp; a11y</li>
        <li>Modern CSS (CSS Grid &amp; Flexbox)</li>
        <li>Responsive Web Design (Mobile-First)</li>
        <li>Làm việc nhóm &amp; Giao tiếp hiệu quả</li>
        <li>Quản lý thời gian &amp; Giải quyết vấn đề</li>
        <li>Sử dụng Git &amp; GitHub Version Control</li>
      </ul>
    </section>

    <!-- Thông tin liên hệ bổ sung -->
    <aside
      id="contact"
      class="profile-card profile-aside"
      aria-labelledby="contact-heading"
    >
      <h3 id="contact-heading">Thông Tin Bổ Sung</h3>
      <ul>
        <li>
          <span>📧</span>
          <div>
            <strong>Email:</strong><br />
            <a href="mailto:nguyenhoaibao282006@gmail.com"
              >nguyenhoaibao282006@gmail.com</a
            >
          </div>
        </li>
        <li>
          <span>📞</span>
          <div>
            <strong>Số điện thoại:</strong><br />
            <a href="tel:0799860146">0799 860 146</a>
          </div>
        </li>
        <li>
          <span>🏫</span>
          <div>
            <strong>Đơn vị:</strong><br />
            <span>Khoa Toán - Tin học, Trường ĐHSP - ĐHĐN</span>
          </div>
        </li>
      </ul>
    </aside>
  </div>

  <!-- KHỐI 4: HỌC VẤN VÀ MỤC TIÊU (TƯƠNG TÁC ACCORDION HỌC VẤN) -->
  <section
    id="education"
    class="profile-card education-section"
    aria-labelledby="education-heading"
  >
    <h2 id="education-heading">Quá Trình Học Tập &amp; Bằng Cấp</h2>
    <p class="education-lead mb-4">
      Nhấp vào từng mục bên dưới để xem chi tiết quá trình đào tạo và mục tiêu phát triển:
    </p>

    <div class="education-accordion">
      <!-- Mục 1: Đại học -->
      <div class="education-accordion__item">
        <button
          type="button"
          class="education-accordion__btn"
          id="btn-edu-1"
          aria-expanded="false"
          aria-controls="panel-edu-1"
        >
          <span class="education-accordion__title">
            🎓 Trường Đại học Sư phạm – Đại học Đà Nẵng (2022 – Hiện tại)
          </span>
          <span class="education-accordion__meta">
            <span class="education-accordion__hint">Bấm để mở rộng</span>
            <span class="education-accordion__icon" aria-hidden="true">▾</span>
          </span>
        </button>
        <div
          id="panel-edu-1"
          class="education-accordion__panel"
          role="region"
          aria-labelledby="btn-edu-1"
          hidden
        >
          <div class="education-accordion__content">
            <p>
              <strong>Chuyên ngành đào tạo:</strong> Sư phạm Tin học / Công nghệ thông tin (Khoa Toán - Tin học).
            </p>
            <p>
              <strong>Quá trình tích lũy:</strong> Đạt kết quả học tập tốt, hoàn thành các học phần nền tảng vững chắc: Thiết kế &amp; Lập trình Web, Cơ sở dữ liệu, Công nghệ phần mềm, An toàn thông tin.
            </p>
            <p>
              <strong>Hoạt động:</strong> Tham gia tích cực các hoạt động phong trào Đoàn - Hội và dự án công nghệ sinh viên UniEvent.
            </p>
          </div>
        </div>
      </div>

      <!-- Mục 2: Chứng chỉ và Kỹ năng -->
      <div class="education-accordion__item">
        <button
          type="button"
          class="education-accordion__btn"
          id="btn-edu-2"
          aria-expanded="false"
          aria-controls="panel-edu-2"
        >
          <span class="education-accordion__title">
            💻 Khóa Huấn Luyện &amp; Kỹ Năng Số Chuyên Sâu
          </span>
          <span class="education-accordion__meta">
            <span class="education-accordion__hint">Bấm để mở rộng</span>
            <span class="education-accordion__icon" aria-hidden="true">▾</span>
          </span>
        </button>
        <div
          id="panel-edu-2"
          class="education-accordion__panel"
          role="region"
          aria-labelledby="btn-edu-2"
          hidden
        >
          <div class="education-accordion__content">
            <p>
              <strong>Phát triển Giao diện Web:</strong> Thành thạo chuẩn ngữ nghĩa HTML5 Semantic, Modern CSS (Flexbox, CSS Grid), JavaScript ES6+ và tiêu chuẩn trợ năng Accessibility (a11y WCAG 2.1).
            </p>
            <p>
              <strong>Quản trị Dữ liệu:</strong> Thiết kế CSDL quan hệ, tối ưu truy vấn SQL trên MySQL / SQL Server.
            </p>
            <p>
              <strong>Quy trình &amp; Công cụ:</strong> Sử dụng thành thạo Git/GitHub cho làm việc nhóm, Figma để thiết kế giao diện UI/UX.
            </p>
          </div>
        </div>
      </div>

      <!-- Mục 3: Mục tiêu nghề nghiệp -->
      <div class="education-accordion__item">
        <button
          type="button"
          class="education-accordion__btn"
          id="btn-edu-3"
          aria-expanded="false"
          aria-controls="panel-edu-3"
        >
          <span class="education-accordion__title">
            🎯 Kế Hoạch &amp; Mục Tiêu Nghề Nghiệp Tương Lai
          </span>
          <span class="education-accordion__meta">
            <span class="education-accordion__hint">Bấm để mở rộng</span>
            <span class="education-accordion__icon" aria-hidden="true">▾</span>
          </span>
        </button>
        <div
          id="panel-edu-3"
          class="education-accordion__panel"
          role="region"
          aria-labelledby="btn-edu-3"
          hidden
        >
          <div class="education-accordion__content">
            <p>
              <strong>Mục tiêu ngắn hạn:</strong> Hoàn thành xuất sắc đồ án học phần Thiết kế &amp; Lập trình Web với điểm số tối đa, làm chủ các kỹ thuật lập trình web hiện đại.
            </p>
            <p>
              <strong>Mục tiêu dài hạn:</strong> Tốt nghiệp loại Giỏi chuyên ngành Sư phạm Tin học, trở thành Kỹ sư phần mềm Full-stack Web/Mobile và tham gia giảng dạy, phát triển các giải pháp phần mềm chuyển đổi số trong giáo dục.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- KHỐI 5: THỜI KHÓA BIỂU HỌC TẬP -->
  <section
    id="schedule"
    class="profile-card schedule-section"
    aria-labelledby="schedule-heading"
  >
    <h2 id="schedule-heading">Thời Khóa Biểu Tuần</h2>
    <p class="schedule-note">
      <span>💡</span>
      <em
        >Mẹo: Trên điện thoại, bạn có thể vuốt sang trái/phải khung bảng bên
        dưới để xem toàn bộ các ngày trong tuần.</em
      >
    </p>

    <!-- Khung cuộn ngang độc lập chống tràn trang -->
    <div
      class="bang-wrapper"
      tabindex="0"
      role="region"
      aria-label="Bảng thời khóa biểu học tập trong tuần"
    >
      <table class="schedule-table">
        <caption>
          Thời Khóa Biểu Học Tập Trong Tuần - Học kỳ 2 Năm học 2024-2025
        </caption>
        <thead>
          <tr>
            <th scope="col">Tiết</th>
            <th scope="col">Thứ 2</th>
            <th scope="col">Thứ 3</th>
            <th scope="col">Thứ 4</th>
            <th scope="col">Thứ 5</th>
            <th scope="col">Thứ 6</th>
            <th scope="col">Thứ 7</th>
            <th scope="col">Chủ nhật</th>
          </tr>
        </thead>
        <tbody>
          <!-- Tiết 1 -->
          <tr>
            <th scope="row">1</th>
            <td></td>
            <td></td>
            <td rowspan="3">
              <strong>Hệ quản trị CSDL</strong>
              31241283 - 24-0102<br />
              <span class="room-badge">Phòng: A5-404B</span>
            </td>
            <td></td>
            <td></td>
            <td rowspan="3">
              <strong>Thiết kế &amp; Lập trình Web</strong>
              31231755 - 24-0102<br />
              <span class="room-badge">Phòng: B3-303</span>
            </td>
            <td></td>
          </tr>

          <!-- Tiết 2 -->
          <tr>
            <th scope="row">2</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 3 -->
          <tr>
            <th scope="row">3</th>
            <td></td>
            <td rowspan="2">
              <strong>Lịch sử Đảng CSVN</strong>
              21221904 - 24-0311<br />
              <span class="room-badge">Phòng: A6-502</span>
            </td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 4 -->
          <tr>
            <th scope="row">4</th>
            <td></td>
            <td></td>
            <td rowspan="3">
              <strong>Khai phá dữ liệu</strong>
              31231330 - 24-0102<br />
              <span class="room-badge">Phòng: A5-404B</span>
            </td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 5 -->
          <tr>
            <th scope="row">5</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 6 -->
          <tr>
            <th scope="row">6</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 7 -->
          <tr>
            <th scope="row">7</th>
            <td rowspan="3">
              <strong>An toàn thông tin</strong>
              31221010 - 24-0102<br />
              <span class="room-badge">Phòng: A6-401</span>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 8 -->
          <tr>
            <th scope="row">8</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 9 -->
          <tr>
            <th scope="row">9</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 10 -->
          <tr>
            <th scope="row">10</th>
            <td></td>
            <td></td>
            <td></td>
            <td rowspan="3">
              <strong>Công nghệ phần mềm</strong>
              31231016 - 24-0103<br />
              <span class="room-badge">Phòng: B3-304</span>
            </td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 11 -->
          <tr>
            <th scope="row">11</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 12 -->
          <tr>
            <th scope="row">12</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- KHỐI 6: MÁY TÍNH ĐIỂM HỌC PHẦN (XỬ LÝ MÁY CHỦ POST - PRG PATTERN) -->
  <section
    id="tinh-diem"
    class="profile-card grade-calc-section"
    aria-labelledby="tinh-diem-heading"
  >
  <!-- KHỐI 6: ĐẶT LỊCH TƯ VẤN & KẾT NỐI DỰ ÁN (XỬ LÝ MÁY CHỦ POST - JSONL - PRG) -->
  <section
    id="tu-van"
    class="profile-card tuvan-section"
    aria-labelledby="tuvan-heading"
  >
    <div class="tuvan-header">
      <div class="tuvan-badge">
        <span aria-hidden="true">📅</span>
        <span>Xử lý máy chủ (Server-side) &bull; Lưu trữ JSONL &bull; Mô hình PRG</span>
      </div>
      <h2 id="tuvan-heading">Đặt Lịch Tư Vấn Học Tập &amp; Kết Nối Dự Án</h2>
      <p class="tuvan-desc">
        Bạn muốn trao đổi về đồ án học phần, hợp tác xây dựng tính năng cho hệ thống <strong>UniEvent</strong> hoặc chia sẻ công nghệ web? Hãy gửi thông tin hẹn lịch bên dưới. Dữ liệu được máy chủ kiểm tra an toàn, lưu trữ vào tệp <code>storage/3120224011_lienhe.jsonl</code> và phản hồi tức thì qua mô hình PRG.
      </p>
    </div>

    <!-- Thông báo kết quả sau khi gửi biểu mẫu thành công (Flash Session) -->
    <?php if ($thongBaoTuVan !== null): ?>
      <div class="tuvan-alert-success" role="alert" aria-live="polite">
        <span class="tuvan-alert-icon" aria-hidden="true">✅</span>
        <div>
          <strong>Gửi yêu cầu thành công!</strong>
          <p class="mb-0"><?= e($thongBaoTuVan) ?></p>
        </div>
      </div>
    <?php endif; ?>

    <div class="tuvan-layout-grid">
      <!-- Biểu mẫu gửi yêu cầu -->
      <div class="tuvan-form-card">
        <h3 id="form-tuvan-title">Gửi phiếu đặt lịch / liên hệ</h3>
        <form
          action="gioithieu.php#tu-van"
          method="post"
          class="tuvan-form"
          novalidate
          aria-labelledby="form-tuvan-title"
        >
          <div class="form-group mb-3">
            <label for="ho_ten" class="form-label">
              Họ và tên của bạn <span class="bat-buoc" aria-hidden="true">*</span>
            </label>
            <input
              type="text"
              id="ho_ten"
              name="ho_ten"
              class="form-control <?= isset($loiTuVan['ho_ten']) ? 'is-invalid' : '' ?>"
              placeholder="Ví dụ: Trần Minh Hoàng"
              value="<?= e($duLieuTuVanCu['ho_ten'] ?? '') ?>"
              required
              <?= isset($loiTuVan['ho_ten']) ? 'aria-invalid="true" aria-describedby="loi-ho-ten"' : '' ?>
            >
            <?php if (isset($loiTuVan['ho_ten'])): ?>
              <p class="thong-bao-loi" id="loi-ho-ten" role="alert"><?= e($loiTuVan['ho_ten']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="email" class="form-label">
              Địa chỉ Email <span class="bat-buoc" aria-hidden="true">*</span>
            </label>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control <?= isset($loiTuVan['email']) ? 'is-invalid' : '' ?>"
              placeholder="name@example.com hoặc student@ued.udn.vn"
              value="<?= e($duLieuTuVanCu['email'] ?? '') ?>"
              required
              <?= isset($loiTuVan['email']) ? 'aria-invalid="true" aria-describedby="loi-email"' : '' ?>
            >
            <?php if (isset($loiTuVan['email'])): ?>
              <p class="thong-bao-loi" id="loi-email" role="alert"><?= e($loiTuVan['email']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="chu_de" class="form-label">
              Chủ đề trao đổi / Đặt lịch <span class="bat-buoc" aria-hidden="true">*</span>
            </label>
            <select
              id="chu_de"
              name="chu_de"
              class="form-control <?= isset($loiTuVan['chu_de']) ? 'is-invalid' : '' ?>"
              required
              <?= isset($loiTuVan['chu_de']) ? 'aria-invalid="true" aria-describedby="loi-chu-de"' : '' ?>
            >
              <?php
              $dsChuDe = [
                  'Học tập & Đồ án' => 'Học tập & Hướng dẫn Đồ án CNTT / Sư phạm Tin',
                  'Dự án UniEvent' => 'Đóng góp ý tưởng & Hợp tác phát triển UniEvent',
                  'Cơ hội Thực tập / Hợp tác' => 'Cơ hội Thực tập & Tuyển dụng lập trình viên',
                  'Trao đổi Kỹ thuật Web' => 'Giao lưu kiến thức PHP, Database & Accessibility',
              ];
              $chuDeChon = $duLieuTuVanCu['chu_de'] ?? 'Học tập & Đồ án';
              foreach ($dsChuDe as $val => $nhan):
              ?>
                <option value="<?= e($val) ?>" <?= $chuDeChon === $val ? 'selected' : '' ?>>
                  <?= e($nhan) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($loiTuVan['chu_de'])): ?>
              <p class="thong-bao-loi" id="loi-chu-de" role="alert"><?= e($loiTuVan['chu_de']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="noi_dung" class="form-label">
              Nội dung chi tiết lời nhắn / Hẹn lịch <span class="bat-buoc" aria-hidden="true">*</span>
            </label>
            <textarea
              id="noi_dung"
              name="noi_dung"
              rows="4"
              class="form-control <?= isset($loiTuVan['noi_dung']) ? 'is-invalid' : '' ?>"
              placeholder="Nhập nội dung bạn muốn trao đổi (từ 10 đến 800 ký tự)..."
              required
              <?= isset($loiTuVan['noi_dung']) ? 'aria-invalid="true" aria-describedby="loi-noi-dung"' : '' ?>
            ><?= e($duLieuTuVanCu['noi_dung'] ?? '') ?></textarea>
            <?php if (isset($loiTuVan['noi_dung'])): ?>
              <p class="thong-bao-loi" id="loi-noi-dung" role="alert"><?= e($loiTuVan['noi_dung']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-actions mt-3">
            <button type="submit" name="gui_tuvan" value="1" class="btn-action">
              <span aria-hidden="true">📤</span>
              <span>Gửi phiếu kết nối (POST - PRG)</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Danh sách các yêu cầu / lịch hẹn gần nhất được đọc từ tệp máy chủ -->
      <div class="tuvan-history-card">
        <h3>Lịch hẹn &amp; Yêu cầu tư vấn gần đây</h3>
        <p class="tuvan-history-sub">
          Đọc trực tiếp từ tệp <code>storage/3120224011_lienhe.jsonl</code> trên máy chủ:
        </p>

        <?php if (empty($danhSachLienHeBao)): ?>
          <div class="tuvan-empty-state">
            <span class="tuvan-empty-icon" aria-hidden="true">📭</span>
            <p>Chưa có yêu cầu đặt lịch nào trong sổ lưu trữ.</p>
            <p class="small text-muted">Hãy là người đầu tiên gửi thông tin kết nối cùng Nguyễn Hoài Bảo!</p>
          </div>
        <?php else: ?>
          <ul class="tuvan-history-list">
            <?php foreach ($danhSachLienHeBao as $lh): ?>
              <li class="tuvan-history-item">
                <div class="tuvan-history-header">
                  <strong><?= e($lh['ho_ten'] ?? 'Ẩn danh') ?></strong>
                  <span class="tuvan-history-badge"><?= e($lh['chu_de'] ?? 'Tư vấn') ?></span>
                </div>
                <p class="tuvan-history-msg">&ldquo;<?= e($lh['noi_dung'] ?? '') ?>&rdquo;</p>
                <div class="tuvan-history-meta">
                  <span>⏱️ <?= e($lh['ngay_gui'] ?? '') ?></span>
                  <span>📧 <?= e(substr((string)($lh['email'] ?? ''), 0, 3) . '***@' . (explode('@', (string)($lh['email'] ?? ''))[1] ?? '***')) ?></span>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<!-- THANH TIẾN ĐỘ CUỘN TRANG (SCROLL INDICATOR) -->
<aside class="scroll-tracker" aria-label="Tiến độ cuộn trang">
  <div class="scroll-tracker__track">
    <div
      id="scrollProgressBar"
      class="scroll-tracker__bar"
      role="progressbar"
      aria-label="Tiến độ cuộn trang cá nhân"
      aria-valuemin="0"
      aria-valuemax="100"
      aria-valuenow="0"
    ></div>
  </div>
  <span id="scrollPercentText" class="scroll-tracker__text" aria-live="polite">0%</span>
</aside>

<script src="js/canhan.js"></script>

<?php require __DIR__ . '/../../inc/footer.php'; ?>

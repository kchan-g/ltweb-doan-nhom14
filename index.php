<?php
/**
 * index.php
 * Trang chủ hệ thống UniEvent.
 * Sinh giao diện động từ PHP và dữ liệu data/su-kien.json,
 * hiển thị khối "Sự kiện đã xem gần đây" từ cookie da_xem được kiểm tra an toàn ở máy chủ.
 * Giữ nguyên 100% cấu trúc HTML Semantic & CSS Design System gốc.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSuKien;
use App\Services\GioHang;

$kho = new KhoSuKien(__DIR__ . '/data/su-kien.json');
$gio = new GioHang();
$tatCa = $kho->tatCa();

// 3 sự kiện tiêu biểu cho phần nổi bật
$noiBat = array_slice($tatCa, 0, 3);

// Đọc cookie 'da_xem' (tối đa 4 ID xem gần nhất)
// Tuyệt đối không tin giá trị cookie, kiểm tra từng ID qua FILTER_VALIDATE_INT và đối chiếu trong kho
$daXem = [];
$cookieDaXem = $_COOKIE['da_xem'] ?? '';
if ($cookieDaXem !== '') {
    $cacId = explode(',', $cookieDaXem);
    foreach ($cacId as $idStr) {
        $idInt = filter_var($idStr, FILTER_VALIDATE_INT);
        if ($idInt !== false && $idInt > 0) {
            $sk = $kho->timTheoId($idInt);
            if ($sk !== null) {
                $daXem[] = $sk;
            }
        }
    }
}

$tieuDe    = 'Trang chủ - Cổng thông tin sự kiện sinh viên UED';
$trang     = 'index';
$bodyClass = 'khung-trang-grid';

require __DIR__ . '/inc/header.php';
?>

<!-- Main (Vùng "chinh") -->
<main class="vung-chinh noi-dung-chinh">
  <!-- 1. Hero Section Nổi Bật -->
  <section class="hero">
    <div class="hero__noi-dung">
      <span class="hero__badge">Cổng sự kiện sinh viên UED</span>
      <h1>
        Khám phá &amp; Khởi tạo
        <span class="text-highlight">Sự kiện Đại học</span> cùng UniEvent
      </h1>
      <p class="hero__mota">
        Trải nghiệm cuộc sống sinh viên sôi động, kết nối mạng lưới bạn bè
        và phát triển kỹ năng toàn diện thông qua hàng trăm hoạt động học
        thuật, thể thao và văn hóa đặc sắc tại Trường ĐH Sư Phạm – Đại học
        Đà Nẵng.
      </p>
      <div class="hero__cta">
        <a href="danh-sach.php" class="nut-bam nut-nhan">
          Khám phá sự kiện ngay →
        </a>
        <a href="gioi-thieu.php" class="nut-bam"> Tìm hiểu thêm </a>
      </div>

      <!-- Khối thống kê nổi bật trên Hero -->
      <div class="hero__thong-ke">
        <div class="hero__thong-ke-item">
          <span class="hero__thong-ke-so">50+</span>
          <span class="hero__thong-ke-chu">Sự kiện mỗi tháng</span>
        </div>
        <div class="hero__thong-ke-item">
          <span class="hero__thong-ke-so">10.000+</span>
          <span class="hero__thong-ke-chu">Sinh viên tham gia</span>
        </div>
        <div class="hero__thong-ke-item">
          <span class="hero__thong-ke-so">100%</span>
          <span class="hero__thong-ke-chu">Cộng điểm rèn luyện</span>
        </div>
      </div>
    </div>

    <div class="hero__hinh-anh">
      <img
        src="images/hero-image.jpg"
        alt="Sinh viên năng động tham gia hoạt động tại trường Đại học Sư phạm Đà Nẵng"
        width="560"
        height="420"
        fetchpriority="high"
        decoding="async"
      />
      <div class="hero__the-noi">
        <div>
          <strong>Tiêu điểm tuần này</strong>
          <span>Đang mở cổng đăng ký trực tuyến</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Khối dữ liệu từ REST API công khai: Thời tiết khuôn viên ĐH Sư Phạm Đà Nẵng (Open-Meteo) -->
  <section class="thoi-tiet-section" aria-labelledby="tieu-de-thoi-tiet">
    <div class="section-head mb-3">
      <div>
        <h2 id="tieu-de-thoi-tiet" class="tieu-de-muc">
          Thời Tiết &amp; Hoạt Động Hôm Nay
        </h2>
        <p class="section-head__mota">
          Dữ liệu quan trắc thời gian thực tại khuôn viên Trường ĐH Sư Phạm – Đại học Đà Nẵng từ Open-Meteo REST API.
        </p>
      </div>
    </div>
    <div id="khoi-thoi-tiet-widget">
      <noscript>
        <div class="thoi-tiet-card" style="padding: 1.25rem; border: 1px dashed var(--border-nhat, #cbd5e1); border-radius: var(--radius-vua, 8px); background: var(--nen-surface, #ffffff);">
          <p class="mb-1" style="color: var(--chu-chinh, #0f172a); font-weight: 600;">
            ⚠️ Tiện ích thời tiết yêu cầu JavaScript để nạp dữ liệu động:
          </p>
          <p class="mb-0" style="color: var(--chu-phu, #475569); font-size: var(--co-chu-nho, 0.875rem);">
            Vui lòng bật JavaScript trên trình duyệt của bạn để xem nhiệt độ và gợi ý hoạt động ngoài trời theo thời gian thực tại khuôn viên Trường ĐH Sư phạm Đà Nẵng (Open-Meteo REST API).
          </p>
        </div>
      </noscript>
    </div>
  </section>

  <!-- 2. Featured Events (Lưới 3 thẻ chuẩn CSS UniEvent) -->
  <section>
    <div class="section-head">
      <div>
        <h2 class="tieu-de-muc">Sự kiện nổi bật</h2>
        <p class="section-head__mota">
          3 hoạt động tiêu biểu được sinh viên quan tâm nhiều nhất tuần này — Lưới thẻ tự động co giãn và xuống dòng linh hoạt.
        </p>
      </div>
      <a href="danh-sach.php" class="nut-bam nut-nho">Xem tất cả sự kiện →</a>
    </div>

    <div class="luoi-3-the-tu-dong">
      <?php foreach ($noiBat as $sp): ?>
        <article class="the-tin">
          <div class="the-tin__media">
            <span class="<?= e($sp->badgeClass ?: 'badge') ?>"><?= e($sp->tenDanhMuc) ?></span>
            <img
              src="<?= e($sp->hinhAnh) ?>"
              alt="<?= e($sp->ten) ?>"
              width="360"
              height="225"
              loading="lazy"
            />
          </div>
          <div class="the-tin__body">
            <h3><?= e($sp->ten) ?></h3>
            <p class="event-meta">Thời gian: <?= e($sp->thoiGian) ?>, <?= e($sp->ngay) ?></p>
            <p class="event-meta">Địa điểm: <?= e($sp->diaDiem) ?></p>
            <p class="event-meta">Quyền lợi: +<?= (int)$sp->diemRenLuyen ?> điểm rèn luyện</p>
            <div class="the-tin__actions mt-3">
              <a href="chi-tiet.php?id=<?= $sp->id ?>" class="nut-bam nut-nhan nut-nho">
                Xem chi tiết &amp; Đăng ký
              </a>
              <?php if ($gio->daXacNhan($sp->id)): ?>
                <a href="gio-hang.php#da-xac-nhan" class="nut-bam nut-phu nut-nho" style="color:#15803d; background:#f0fdf4; border-color:#86efac; font-weight:700;" title="Bạn đã đăng ký thành công sự kiện này (1 vé)">
                  ✓ Đã đăng ký
                </a>
              <?php elseif ($gio->dangChoXacNhan($sp->id)): ?>
                <a href="gio-hang.php" class="nut-bam nut-phu nut-nho" style="color:#b45309; background:#fffbeb; border-color:#fde68a; font-weight:700;" title="Sự kiện đang chờ xác nhận - Bấm để xác nhận">
                  ⏳ Chờ xác nhận
                </a>
              <?php else: ?>
                <form action="gio-hang.php" method="POST" style="margin:0; display:inline;">
                  <input type="hidden" name="hanh_dong" value="them">
                  <input type="hidden" name="id" value="<?= $sp->id ?>">
                  <input type="hidden" name="so_luong" value="1">
                  <button type="submit" class="nut-bam nut-nho" title="Đăng ký tham gia sự kiện (1 vé)" style="cursor:pointer;">
                    + Đăng ký
                  </button>
                </form>
              <?php endif; ?>
              <button
                type="button"
                class="nut-yeu-thich"
                data-id="<?= $sp->id ?>"
                aria-pressed="false"
                aria-label="Lưu tin - Lưu sự kiện <?= e($sp->ten) ?>"
                title="Lưu sự kiện vào danh sách yêu thích"
              >
                ♡ Lưu tin
              </button>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if (!empty($daXem)): ?>
    <!-- Khối sự kiện đã xem gần đây (từ Cookie da_xem) -->
    <section class="mt-5">
      <div class="section-head mb-3">
        <div>
          <h2 class="tieu-de-muc">Sự kiện đã xem gần đây</h2>
          <p class="section-head__mota">
            Các sự kiện bạn đã quan tâm và truy cập trong các phiên duyệt web vừa qua (lưu bằng Cookie an toàn).
          </p>
        </div>
      </div>
      <div class="luoi-3-the-tu-dong">
        <?php foreach ($daXem as $sp): ?>
          <article class="the-tin">
            <div class="the-tin__media">
              <span class="<?= e($sp->badgeClass ?: 'badge') ?>"><?= e($sp->tenDanhMuc) ?></span>
              <img
                src="<?= e($sp->hinhAnh) ?>"
                alt="<?= e($sp->ten) ?>"
                width="360"
                height="225"
                loading="lazy"
              />
            </div>
            <div class="the-tin__body">
              <h3><?= e($sp->ten) ?></h3>
              <p class="event-meta">Thời gian: <?= e($sp->thoiGian) ?>, <?= e($sp->ngay) ?></p>
              <p class="event-meta">Địa điểm: <?= e($sp->diaDiem) ?></p>
              <p class="event-meta">Quyền lợi: +<?= (int)$sp->diemRenLuyen ?> điểm rèn luyện</p>
              <div class="the-tin__actions mt-3">
                <a href="chi-tiet.php?id=<?= $sp->id ?>" class="nut-bam nut-nhan nut-nho">
                  Xem chi tiết &amp; Đăng ký
                </a>
                <?php if ($gio->daXacNhan($sp->id)): ?>
                  <a href="gio-hang.php#da-xac-nhan" class="nut-bam nut-phu nut-nho" style="color:#15803d; background:#f0fdf4; border-color:#86efac; font-weight:700;" title="Bạn đã đăng ký thành công sự kiện này (1 vé)">
                    ✓ Đã đăng ký
                  </a>
                <?php elseif ($gio->dangChoXacNhan($sp->id)): ?>
                  <a href="gio-hang.php" class="nut-bam nut-phu nut-nho" style="color:#b45309; background:#fffbeb; border-color:#fde68a; font-weight:700;" title="Sự kiện đang chờ xác nhận - Bấm để xác nhận">
                    ⏳ Chờ xác nhận
                  </a>
                <?php else: ?>
                  <form action="gio-hang.php" method="POST" style="margin:0; display:inline;">
                    <input type="hidden" name="hanh_dong" value="them">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="so_luong" value="1">
                    <button type="submit" class="nut-bam nut-nho" title="Đăng ký tham gia sự kiện (1 vé)" style="cursor:pointer;">
                      + Đăng ký
                    </button>
                  </form>
                <?php endif; ?>
                <button
                  type="button"
                  class="nut-yeu-thich"
                  data-id="<?= $sp->id ?>"
                  aria-pressed="false"
                  aria-label="Lưu tin - Lưu sự kiện <?= e($sp->ten) ?>"
                  title="Lưu sự kiện vào danh sách yêu thích"
                >
                  ♡ Lưu tin
                </button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- 3. Khối giới thiệu / Kêu gọi hành động -->
  <section class="khung-gioi-thieu">
    <h2>Giới thiệu UniEvent</h2>
    <p>
      UniEvent là nền tảng quản lý sự kiện toàn diện cho sinh viên và cán bộ
      giảng viên Trường Đại học Sư phạm – Đại học Đà Nẵng. Giúp bạn dễ dàng
      khám phá, tham gia và lưu giữ những kỷ niệm thời thanh xuân ý nghĩa
      nhất.
    </p>
    <a href="lien-he.php" class="nut-bam nut-vang">
      Tham gia cùng chúng tôi
    </a>
  </section>
</main>

<!-- Aside (Vùng "ben" - Cột tiện ích phụ hiển thị trên màn hình máy tính) -->
<aside class="vung-ben chi-hien-tren-may-tinh">
  <div class="widget">
    <h3>Thông báo mới</h3>
    <ul>
      <li>Hạn đăng ký giải bóng đá sinh viên kết thúc vào 20/10.</li>
      <li>
        Hội trường A1 đang hoàn tất bảo trì hệ thống âm thanh, ánh sáng.
      </li>
      <li>Mở cổng đăng ký tình nguyện viên tiếp sức mùa thi 2026.</li>
    </ul>
  </div>

  <div class="widget">
    <h3>Lưu ý sinh viên</h3>
    <p>
      Mỗi sinh viên cần tham gia tối thiểu 3 hoạt động/học kỳ để đủ điều kiện
      xét điểm rèn luyện loại Khá trở lên.
    </p>
  </div>

  <div class="widget">
    <h3>Lịch tuần này</h3>
    <ol>
      <li>22/10: Bóng đá nam</li>
      <li>28/10: Hội thảo AI</li>
      <li>30/10: Nhạc hội</li>
    </ol>
  </div>
</aside>

<!-- Nạp JS thời tiết cho trang chủ -->
<script type="module" src="js/trang-chu.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>

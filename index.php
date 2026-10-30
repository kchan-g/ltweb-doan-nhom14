<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSuKien;
use App\Services\GioHang;

$kho = new KhoSuKien(db());
$gio = new GioHang();
$tatCa = $kho->tatCa(6);
$noiBat = array_slice($tatCa, 0, 3);

$daXem = [];
$cookieDaXem = $_COOKIE['da_xem'] ?? '';
if ($cookieDaXem !== '') {
    $cacId = explode(',', $cookieDaXem);
    $idsHopLe = [];
    foreach ($cacId as $idStr) {
        $idInt = filter_var($idStr, FILTER_VALIDATE_INT);
        if ($idInt !== false && $idInt > 0) {
            $idsHopLe[] = $idInt;
        }
    }
    if (!empty($idsHopLe)) {
        $daXem = $kho->layTheoIds(array_slice($idsHopLe, 0, 4));
    }
}

$tieuDe    = 'Trang chủ - Cổng thông tin sự kiện sinh viên UED';
$trang     = 'index';
$bodyClass = 'khung-trang-grid';

require __DIR__ . '/inc/header.php';
?>

<main class="vung-chinh noi-dung-chinh">
  <section class="hero">
    <div class="hero__noi-dung">
      <div class="hero__co-quan">
        <span class="hero__badge">ĐOÀN TRƯỜNG – HỘI SINH VIÊN ĐHSP ĐÀ NẴNG</span>
        <span class="hero__nam-hoc">Năm học 2026 – 2027</span>
      </div>
      <h1>
        Cổng Đăng Ký Hoạt Động &amp;
        <span class="text-highlight">Tích Lũy Điểm Rèn Luyện</span>
      </h1>
      <p class="hero__mota">
        Hệ thống chính thức ghi nhận, cấp vé điện tử E-Ticket và đồng bộ Điểm rèn luyện tự động cho sinh viên tham gia các hoạt động phong trào, hội thảo học thuật, văn hóa nghệ thuật và thể dục thể thao tại Trường ĐH Sư Phạm – Đại học Đà Nẵng.
      </p>
      <div class="hero__cta">
        <a href="danh-sach.php" class="nut-bam nut-nhan">
          <?= uiIcon('calendar', '', 15) ?> Khám phá các sự kiện đang mở →
        </a>
      </div>

      <div class="hero__quy-chuan-grid">
        <div class="hero__quy-chuan-item">
          <span class="icon-hop"><?= uiIcon("pin", "icon-brand", 18) ?></span>
          <div>
            <strong>Địa điểm tập trung</strong>
            <small>Hội trường A, A3 &amp; Nhà đa năng</small>
          </div>
        </div>
        <div class="hero__quy-chuan-item">
          <span class="icon-hop icon-hop--gold"><?= uiIcon("star", "icon-gold", 18) ?></span>
          <div>
            <strong>Điểm rèn luyện</strong>
            <small>Tích lũy +3 đến +5 ĐRL/sự kiện</small>
          </div>
        </div>
        <div class="hero__quy-chuan-item">
          <span class="icon-hop"><?= uiIcon("ticket", "icon-brand", 18) ?></span>
          <div>
            <strong>Vé E-Ticket</strong>
            <small>Mã QR điểm danh tại sự kiện</small>
          </div>
        </div>
        <div class="hero__quy-chuan-item">
          <span class="icon-hop"><?= uiIcon("users", "icon-brand", 18) ?></span>
          <div>
            <strong>Đối tượng</strong>
            <small>Toàn thể sinh viên chính quy UED</small>
          </div>
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
          <strong>Cổng trực tuyến đang mở</strong>
          <span>Đăng ký giữ chỗ &amp; nhận vé tức thì</span>
        </div>
      </div>
    </div>
  </section>

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
                Xem chi tiết
              </a>
              <?php if ($gio->daXacNhan($sp->id)): ?>
                <a href="gio-hang.php#da-xac-nhan" class="nut-bam nut-phu nut-nho" style="color:#16a34a; background:rgba(34,197,94,0.12); border-color:rgba(34,197,94,0.3); font-weight:700;" title="Bạn đã đăng ký thành công sự kiện này (1 vé)">
                  ✓ Đã đăng ký
                </a>
              <?php elseif ($gio->dangChoXacNhan($sp->id)): ?>
                <a href="gio-hang.php" class="nut-bam nut-phu nut-nho" style="color:#d97706; background:rgba(245,158,11,0.12); border-color:rgba(245,158,11,0.3); font-weight:700;" title="Sự kiện đang chờ xác nhận - Bấm để xác nhận">
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
                <?= uiIcon("heart", "", 13) ?> Lưu
              </button>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if (!empty($daXem)): ?>
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
                  Xem chi tiết
                </a>
                <?php if ($gio->daXacNhan($sp->id)): ?>
                  <a href="gio-hang.php#da-xac-nhan" class="nut-bam nut-phu nut-nho" style="color:#16a34a; background:rgba(34,197,94,0.12); border-color:rgba(34,197,94,0.3); font-weight:700;" title="Bạn đã đăng ký thành công sự kiện này (1 vé)">
                    ✓ Đã đăng ký
                  </a>
                <?php elseif ($gio->dangChoXacNhan($sp->id)): ?>
                  <a href="gio-hang.php" class="nut-bam nut-phu nut-nho" style="color:#d97706; background:rgba(245,158,11,0.12); border-color:rgba(245,158,11,0.3); font-weight:700;" title="Sự kiện đang chờ xác nhận - Bấm để xác nhận">
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
                  <?= uiIcon("heart", "", 13) ?> Lưu
                </button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

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

<aside class="vung-ben chi-hien-tren-may-tinh">
  <div class="widget">
    <h3>Thông báo mới</h3>
    <ul>
      <li>Hạn đăng ký giải bóng đá sinh viên kết thúc vào 20/10.</li>
      <li>Hội trường A1 đang hoàn tất bảo trì hệ thống âm thanh, ánh sáng.</li>
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

<script type="module" src="js/trang-chu.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>

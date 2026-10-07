<?php
/**
 * chi-tiet.php
 * Trang chi tiết sự kiện do máy chủ PHP xử lý:
 * - Kiểm tra tham số ?id= bằng filter_var (FILTER_VALIDATE_INT)
 * - Nếu id thiếu hoặc không có trong kho: trả mã HTTP 404 và nạp 404.php
 * - Ghi cookie da_xem (tối đa 4 ID gần nhất, httponly, samesite) TRƯỚC mọi output
 * - Cung cấp biểu mẫu POST thêm vé vào giỏ hàng (gio-hang.php)
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSuKien;
use App\Services\GioHang;

$kho = new KhoSuKien(__DIR__ . '/data/su-kien.json');
$gio = new GioHang();

// 1. Kiểm tra an toàn mã ID trên URL
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$sk = ($id !== false && $id > 0) ? $kho->timTheoId($id) : null;

// Nếu ID sai kiểu, không tồn tại hoặc rỗng -> Trả về HTTP 404 Not Found
if ($sk === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// 2. Ghi nhận sự kiện đã xem gần đây vào Cookie (Tối đa 4 ID, mới nhất đứng đầu)
// Phải gọi setcookie() TRƯỚC bất kỳ output HTML nào
$cu = array_map('intval', explode(',', $_COOKIE['da_xem'] ?? ''));
$moi = array_unique([$sk->id, ...array_filter($cu, fn($val) => $val > 0)]);
setcookie('da_xem', implode(',', array_slice($moi, 0, 4)), [
    'expires'  => time() + 30 * 24 * 3600, // Lưu 30 ngày
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

// 3. Sự kiện liên quan cùng danh mục (loại trừ sự kiện hiện tại)
$lienQuan = array_filter(
    $kho->tatCa(),
    fn($item) => $item->danhMuc === $sk->danhMuc && $item->id !== $sk->id
);
$lienQuan = array_slice($lienQuan, 0, 3);

$tieuDe = $sk->ten;
$trang  = 'danh-sach';

require __DIR__ . '/inc/header.php';
?>

<main>
  <div class="container">
    <!-- Breadcrumb phân cấp -->
    <nav class="breadcrumb mb-3" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <a href="danh-sach.php">Danh sách sự kiện</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai"><?= e($sk->ten) ?></span>
    </nav>

    <!-- Khung 2 cột màn hình rộng (Grid), dồn 1 cột trên Mobile -->
    <div class="chi-tiet-layout">
      <!-- Cột nội dung chi tiết (Article) -->
      <article>
        <span class="<?= e($sk->badgeClass) ?> mb-2"><?= e($sk->tenDanhMuc) ?></span>

        <!-- Đúng duy nhất 1 thẻ H1 trên trang -->
        <h1 class="mb-4">
          <?= e($sk->ten) ?>
        </h1>

        <!-- Section 1: Giới thiệu tổng quan -->
        <section class="mb-5">
          <h2 class="h3 mb-3">1. Giới thiệu tổng quan về sự kiện</h2>
          <p class="do-dai-chuan text-phu mb-3">
            <?= e($sk->moTaNgan) ?>
          </p>
          <p class="do-dai-chuan text-phu mb-4">
            <?= e($sk->moTaChiTiet) ?>
          </p>

          <!-- Figure + Figcaption chuẩn Semantic HTML -->
          <figure class="mb-4">
            <img
              src="<?= e($sk->hinhAnh) ?>"
              alt="Poster chính thức của sự kiện <?= e($sk->ten) ?>"
              width="720"
              height="450"
              fetchpriority="high"
              decoding="async"
              class="rounded-vua shadow-sm w-full img-cover"
            />
            <figcaption class="mt-2 text-phu fs-meta text-center">
              Hình 1: Poster truyền thông chính thức của sự kiện <?= e($sk->ten) ?> tại <?= e($sk->diaDiem) ?>.
            </figcaption>
          </figure>
        </section>

        <!-- Section 2: Bảng thông tin chi tiết -->
        <section class="mb-5">
          <h2 class="h3 mb-3">2. Thông số và kế hoạch tổ chức chi tiết</h2>
          <p class="do-dai-chuan text-phu mb-3">
            Dưới đây là bảng thông số chính thức về thời gian, địa điểm, diễn giả khách mời và quyền lợi sinh viên khi tham gia:
          </p>

          <!-- Thẻ Table đúng chuẩn nằm trong div.bang-wrapper -->
          <div class="bang-wrapper mb-4">
            <table>
              <caption>
                Bảng 1: Lịch trình chi tiết và thông tin ban tổ chức sự kiện <?= e($sk->ten) ?>
              </caption>
              <thead>
                <tr>
                  <th scope="col" class="col-width-35">Hạng mục thông tin</th>
                  <th scope="col">Nội dung chi tiết</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th scope="row">Thời gian diễn ra</th>
                  <td><?= e($sk->thoiGian) ?></td>
                </tr>
                <tr>
                  <th scope="row">Địa điểm tổ chức</th>
                  <td><?= e($sk->diaDiem) ?></td>
                </tr>
                <tr>
                  <th scope="row">Diễn giả / Khách mời</th>
                  <td><?= e($sk->dienGia) ?></td>
                </tr>
                <tr>
                  <th scope="row">Quyền lợi sinh viên</th>
                  <td>
                    Được cộng <strong>+<?= $sk->diemRenLuyen ?> điểm rèn luyện</strong> cấp Trường và nhận Chứng nhận số
                  </td>
                </tr>
                <tr>
                  <th scope="row">Quy mô số lượng</th>
                  <td><?= $sk->soLuongVe ?> chỗ ngồi chính thức</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Section 3: Video giới thiệu chủ đề (nếu có) -->
        <?php if (!empty($sk->video)): ?>
          <section class="mb-4">
            <h2 class="h3 mb-3">3. Video trailer giới thiệu sự kiện</h2>
            <p class="do-dai-chuan text-phu mb-3">
              Mời các bạn sinh viên theo dõi video ngắn giới thiệu sơ lược về sự kiện:
            </p>

            <div class="video-wrapper shadow-sm">
              <iframe
                width="560"
                height="315"
                src="<?= e($sk->video) ?>"
                title="Video giới thiệu sự kiện <?= e($sk->ten) ?>"
                loading="lazy"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
              ></iframe>
            </div>
          </section>
        <?php endif; ?>
      </article>

      <!-- Cột phụ Sidebar thông tin nổi bật (Aside) -->
      <aside class="chi-tiet-sidebar" aria-label="Thông tin nhanh và đăng ký">
        <div class="chi-tiet-highlight">
          <h3 class="h4 mb-2 text-chinh">Tóm Tắt Sự Kiện</h3>

          <div class="chi-tiet-highlight__dong">
            <div class="chi-tiet-highlight__icon" aria-hidden="true">📅</div>
            <div>
              <div class="chi-tiet-highlight__nhan">Thời gian tổ chức</div>
              <div class="chi-tiet-highlight__gia-tri"><?= e($sk->thoiGian) ?></div>
            </div>
          </div>

          <div class="chi-tiet-highlight__dong">
            <div class="chi-tiet-highlight__icon" aria-hidden="true">📍</div>
            <div>
              <div class="chi-tiet-highlight__nhan">Địa điểm hội trường</div>
              <div class="chi-tiet-highlight__gia-tri"><?= e($sk->diaDiem) ?></div>
            </div>
          </div>

          <div class="chi-tiet-highlight__dong">
            <div class="chi-tiet-highlight__icon" aria-hidden="true">⭐</div>
            <div>
              <div class="chi-tiet-highlight__nhan">Quyền lợi rèn luyện</div>
              <div class="chi-tiet-highlight__gia-tri">+<?= $sk->diemRenLuyen ?> Điểm rèn luyện</div>
            </div>
          </div>

          <div class="chi-tiet-highlight__dong">
            <div class="chi-tiet-highlight__icon" aria-hidden="true">👥</div>
            <div>
              <div class="chi-tiet-highlight__nhan">Quy mô chỗ ngồi</div>
              <div class="chi-tiet-highlight__gia-tri">
                <?= $sk->soLuongVe ?> sinh viên
              </div>
            </div>
          </div>

          <?php if ($gio->daXacNhan($sk->id)): ?>
            <div class="mt-3 p-3 text-center" style="background:#f0fdf4; border:1px solid #86efac; border-radius:var(--radius-vua);">
              <div style="color:#15803d; font-weight:700; margin-bottom:0.25rem;">
                ✓ Bạn đã đăng ký thành công sự kiện này
              </div>
              <p class="fs-meta text-phu mb-2" style="font-size:0.85rem; margin:0 0 8px 0;">
                Mã vé: <strong><?= e($gio->layMaVe($sk->id) ?? 'Đã xác nhận') ?></strong> (1 vé/sinh viên)
              </p>
              <a href="gio-hang.php#da-xac-nhan" class="nut-bam nut-phu nut-full" style="display:block; text-align:center;">
                Xem vé đã đăng ký →
              </a>
            </div>
          <?php elseif ($gio->dangChoXacNhan($sk->id)): ?>
            <div class="mt-3 p-3 text-center" style="background:#fffbeb; border:1px solid #fde68a; border-radius:var(--radius-vua);">
              <div style="color:#b45309; font-weight:700; margin-bottom:0.25rem;">
                ⏳ Chờ xác nhận đăng ký (1 vé)
              </div>
              <p class="fs-meta text-phu mb-2" style="font-size:0.85rem; margin:0 0 8px 0;">
                Sự kiện đã có trong phiếu. Vui lòng bấm xác nhận để được ghi nhận vào hệ thống (+1 vé).
              </p>
              <a href="gio-hang.php" class="nut-bam nut-nhan nut-full" style="display:block; text-align:center;">
                Đến phiếu xác nhận ngay →
              </a>
            </div>
          <?php else: ?>
            <!-- Biểu mẫu POST thêm vào danh sách đăng ký giữ chỗ (hỗ trợ Progressive Enhancement) -->
            <form action="gio-hang.php" method="POST" class="mt-3">
              <input type="hidden" name="hanh_dong" value="them">
              <input type="hidden" name="id" value="<?= $sk->id ?>">
              <input type="hidden" name="so_luong" value="1">
              <div class="mb-2 fs-meta text-phu" style="font-style: italic;">
                * Tiêu chuẩn: 1 vé/sinh viên (miễn phí)
              </div>
              <button type="submit" class="nut-bam nut-nhan nut-full">
                📝 Đăng Ký Giữ Chỗ (1 vé)
              </button>
            </form>
          <?php endif; ?>

          <!-- Nút Lưu tin sự kiện (Bookmark qua localStorage) -->
          <button
            type="button"
            class="nut-yeu-thich nut-full mt-3"
            data-id="<?= $sk->id ?>"
            aria-pressed="false"
            aria-label="Lưu tin - Lưu sự kiện <?= e($sk->ten) ?>"
            title="Lưu sự kiện vào danh sách yêu thích"
            style="width:100%; justify-content:center; padding:9px 16px; font-weight:600;"
          >
            ♡ Lưu tin sự kiện
          </button>
        </div>

        <div class="widget mt-4">
          <h3 class="h5">Lưu ý khi tham gia</h3>
          <ul>
            <li>Sinh viên mang theo thẻ sinh viên hoặc mã QR vé để quét điểm danh.</li>
            <li>Vui lòng có mặt trước giờ khai mạc 15 phút để ổn định chỗ ngồi.</li>
            <li>Trang phục lịch sự, tác phong văn minh học đường.</li>
          </ul>
        </div>
      </aside>
    </div>

    <!-- Mục Sự Kiện Cùng Chuyên Mục -->
    <?php if (!empty($lienQuan)): ?>
      <section class="su-kien-lien-quan mt-5" aria-labelledby="tieu-de-lien-quan">
        <div class="section-head mb-3">
          <div>
            <h2 id="tieu-de-lien-quan" class="tieu-de-muc">
              Sự Kiện Cùng Chuyên Mục
            </h2>
            <p class="section-head__mota">
              Khám phá các hoạt động hấp dẫn khác đang diễn ra tại Trường ĐH Sư Phạm.
            </p>
          </div>
        </div>
        <div class="luoi-3-the-tu-dong">
          <?php foreach ($lienQuan as $lq): ?>
            <article class="the-tin the-tin--doc">
              <div class="the-tin__media">
                <img
                  src="<?= e($lq->hinhAnh) ?>"
                  alt="<?= e($lq->ten) ?>"
                  width="360"
                  height="200"
                  loading="lazy"
                />
                <span class="<?= e($lq->badgeClass) ?> the-tin__badge">
                  <?= e($lq->tenDanhMuc) ?>
                </span>
              </div>
              <div class="the-tin__body">
                <div class="the-tin__meta">
                  <span>📅 <?= e($lq->thoiGian) ?></span>
                </div>
                <h3 class="the-tin__tieu-de">
                  <a href="chi-tiet.php?id=<?= $lq->id ?>"><?= e($lq->ten) ?></a>
                </h3>
                <p class="the-tin__tom-tat"><?= e($lq->moTaNgan) ?></p>
                <div class="the-tin__actions mt-3">
                  <a href="chi-tiet.php?id=<?= $lq->id ?>" class="nut-bam nut-nhan nut-nho">Xem chi tiết</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>

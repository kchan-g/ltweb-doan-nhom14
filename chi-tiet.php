<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSuKien;
use App\Services\GioHang;

$kho = new KhoSuKien(db());
$gio = new GioHang();

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$sk = ($id !== false && $id > 0) ? $kho->timTheoId($id) : null;

if ($sk === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$cu = array_map('intval', explode(',', $_COOKIE['da_xem'] ?? ''));
$moi = array_unique([$sk->id, ...array_filter($cu, fn($val) => $val > 0)]);
setcookie('da_xem', implode(',', array_slice($moi, 0, 4)), [
    'expires'  => time() + 30 * 24 * 3600,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

$lienQuan = $kho->layCungDanhMuc($sk->danhMucId, $sk->id, 4);

$tieuDe = $sk->ten;
$trang  = 'danh-sach';

require __DIR__ . '/inc/header.php';
?>

<main>
  <div class="container">
    <nav class="breadcrumb mb-4" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <a href="danh-sach.php">Danh sách sự kiện</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai"><?= e($sk->ten) ?></span>
    </nav>

    <div class="chi-tiet-layout">
      <article>
        <span class="<?= e($sk->badgeClass) ?> mb-3"><?= e($sk->tenDanhMuc) ?></span>

        <h1 class="mb-4 font-hien-thi fw-800 text-chinh" style="line-height: 1.25;">
          <?= e($sk->ten) ?>
        </h1>

        <section class="mb-5">
          <h2 class="h3 mb-3 text-chinh">1. Giới thiệu tổng quan về sự kiện</h2>
          <p class="do-dai-chuan text-phu mb-3" style="font-size: 1.05rem; line-height: 1.7;">
            <?= e($sk->moTaNgan) ?>
          </p>
          <p class="do-dai-chuan text-phu mb-4" style="line-height: 1.7;">
            <?= e($sk->moTaChiTiet) ?>
          </p>

          <figure class="mb-4">
            <img
              src="<?= e($sk->hinhAnh) ?>"
              alt="Poster chính thức của sự kiện <?= e($sk->ten) ?>"
              width="720"
              height="450"
              fetchpriority="high"
              decoding="async"
              class="w-full img-cover"
              style="border-radius: 16px; box-shadow: var(--shadow-card);"
            />
            <figcaption class="mt-2 text-phu fs-meta text-center">
              Hình 1: Poster truyền thông chính thức của sự kiện <?= e($sk->ten) ?> tại <?= e($sk->diaDiem) ?>.
            </figcaption>
          </figure>
        </section>

        <section class="mb-5">
          <h2 class="h3 mb-3 text-chinh">2. Thông số và kế hoạch tổ chức chi tiết</h2>
          <p class="do-dai-chuan text-phu mb-3">
            Dưới đây là bảng thông số chính thức về thời gian, địa điểm, diễn giả khách mời và quyền lợi sinh viên khi tham gia:
          </p>

          <div class="bang-card mb-4">
            <div class="bang-card__header">
              <h3 class="bang-card__tieu-de">
                <?= uiIcon('calendar', 'icon-brand', 18) ?>
                <span>Lịch trình chi tiết &amp; Ban tổ chức sự kiện</span>
              </h3>
              <span class="bang-card__dem">Thông số chính thức</span>
            </div>
            <div class="bang-wrapper">
              <table style="width:100%; min-width: 450px;">
              <thead>
                <tr>
                  <th scope="col" style="width: 35%;">Hạng mục thông tin</th>
                  <th scope="col">Nội dung chi tiết</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th scope="row">Thời gian diễn ra</th>
                  <td><strong><?= e($sk->thoiGian) ?></strong></td>
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
                    Được cộng <strong style="color: #15803d;">+<?= $sk->diemRenLuyen ?> điểm rèn luyện</strong> cấp Trường và nhận Chứng nhận số
                  </td>
                </tr>
                <tr>
                  <th scope="row">Quy mô số lượng</th>
                  <td><?= $sk->soLuongVe ?> chỗ ngồi chính thức</td>
                </tr>
              </tbody>
            </table>
            </div>
          </div>
        </section>

        <?php if (!empty($sk->video)): ?>
          <section class="mb-4">
            <h2 class="h3 mb-3 text-chinh">3. Video trailer giới thiệu sự kiện</h2>
            <p class="do-dai-chuan text-phu mb-3">
              Mời các bạn sinh viên theo dõi video ngắn giới thiệu sơ lược về sự kiện:
            </p>

            <div class="video-wrapper shadow-sm" style="border-radius: 16px; overflow: hidden;">
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

      <aside class="chi-tiet-sidebar" aria-label="Thông tin nhanh và đăng ký">
        <div class="chi-tiet-highlight">
          <h3 class="h4 mb-3 text-chinh font-hien-thi fw-700">Tóm Tắt Sự Kiện</h3>

          <div class="chi-tiet-highlight__dong">
            <div class="chi-tiet-highlight__icon" aria-hidden="true"><?= uiIcon("calendar", "icon-brand", 18) ?></div>
            <div>
              <div class="chi-tiet-highlight__nhan">Thời gian tổ chức</div>
              <div class="chi-tiet-highlight__gia-tri"><?= e($sk->thoiGian) ?></div>
            </div>
          </div>

          <div class="chi-tiet-highlight__dong">
            <div class="chi-tiet-highlight__icon" aria-hidden="true"><?= uiIcon("pin", "icon-brand", 18) ?></div>
            <div>
              <div class="chi-tiet-highlight__nhan">Địa điểm hội trường</div>
              <div class="chi-tiet-highlight__gia-tri"><?= e($sk->diaDiem) ?></div>
            </div>
          </div>

          <div class="chi-tiet-highlight__dong">
            <div class="chi-tiet-highlight__icon" aria-hidden="true"><?= uiIcon("star", "icon-gold", 18) ?></div>
            <div>
              <div class="chi-tiet-highlight__nhan">Quyền lợi rèn luyện</div>
              <div class="chi-tiet-highlight__gia-tri">+<?= $sk->diemRenLuyen ?> Điểm rèn luyện</div>
            </div>
          </div>

          <div class="chi-tiet-highlight__dong">
            <div class="chi-tiet-highlight__icon" aria-hidden="true"><?= uiIcon("users", "icon-brand", 18) ?></div>
            <div>
              <div class="chi-tiet-highlight__nhan">Quy mô chỗ ngồi</div>
              <div class="chi-tiet-highlight__gia-tri">
                <?= $sk->soLuongVe ?> sinh viên
              </div>
            </div>
          </div>

          <?php if ($gio->daXacNhan($sk->id)): ?>
            <div class="mt-4 p-3 text-center" style="background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.3); border-radius:12px;">
              <div style="color:#16a34a; font-weight:700; margin-bottom:0.25rem;">
                <?= uiIcon('check', 'icon-success', 16) ?> Bạn đã đăng ký thành công sự kiện này
              </div>
              <p class="fs-meta text-phu mb-2" style="font-size:0.85rem; margin:0 0 8px 0;">
                Mã vé: <strong><?= e($gio->layMaVe($sk->id) ?? 'Đã xác nhận') ?></strong> (1 vé/sinh viên)
              </p>
              <a href="gio-hang.php#da-xac-nhan" class="nut-bam nut-phu nut-full" style="display:block; text-align:center;">
                Xem vé đã đăng ký →
              </a>
            </div>
          <?php elseif ($gio->dangChoXacNhan($sk->id)): ?>
            <div class="mt-4 p-3 text-center" style="background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.3); border-radius:12px;">
              <div style="color:#d97706; font-weight:700; margin-bottom:0.25rem;">
                <?= uiIcon('clock', 'icon-gold', 16) ?> Chờ xác nhận đăng ký (1 vé)
              </div>
              <p class="fs-meta text-phu mb-3" style="font-size:0.85rem;">
                Sự kiện đã có trong phiếu. Vui lòng bấm xác nhận để được ghi nhận vào hệ thống (+1 vé).
              </p>
              <a href="gio-hang.php" class="nut-bam nut-nhan nut-full" style="display:block; text-align:center;">
                Đi đến phiếu đăng ký →
              </a>
            </div>
          <?php elseif ($sk->ton_kho <= 0): ?>
            <div class="mt-4 p-3 text-center" style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); border-radius:12px;">
              <div style="color:#ef4444; font-weight:700; margin-bottom:0.25rem;">
                ❌ Đã hết vé đăng ký (Tồn kho: 0)
              </div>
              <p class="fs-meta text-phu" style="font-size:0.85rem; margin:0;">
                Sự kiện này đã đủ số lượng đăng ký tham gia. Vui lòng theo dõi các đợt mở đăng ký bổ sung.
              </p>
            </div>
          <?php else: ?>
            <form action="gio-hang.php" method="POST" class="mt-4" id="form-them-gio">
              <input type="hidden" name="hanh_dong" value="them">
              <input type="hidden" name="id" value="<?= $sk->id ?>">
              <input type="hidden" name="so_luong" value="1">
              <div class="mb-3 fs-meta text-phu" style="font-style: italic;">
                * Tiêu chuẩn: 1 vé/sinh viên (Giá vé: <strong><?= vnd($sk->gia) ?></strong> - Còn lại: <?= $sk->ton_kho ?> vé)
              </div>
              <button type="submit" class="nut-bam nut-nhan nut-full" id="btn-them-gio" style="padding: 12px 20px; font-size: 1rem;">
                <?= uiIcon('ticket', '', 16) ?> Đăng Ký Giữ Chỗ (1 vé)
              </button>
            </form>
          <?php endif; ?>

          <button
            type="button"
            class="nut-yeu-thich nut-full mt-3"
            data-id="<?= $sk->id ?>"
            aria-pressed="false"
            aria-label="Lưu tin - Lưu sự kiện <?= e($sk->ten) ?>"
            title="Lưu sự kiện vào danh sách yêu thích"
            style="width:100%; justify-content:center; padding:10px 16px; font-weight:600;"
          >
            <?= uiIcon('heart', '', 16) ?> Lưu tin sự kiện
          </button>
        </div>

        <div class="widget mt-4">
          <h3 class="h5 mb-3 text-chinh font-hien-thi fw-700">Lưu ý khi tham gia</h3>
          <ul style="padding-left: 1.25rem; margin: 0; line-height: 1.7; color: var(--chu-phu); font-size: 0.9rem;">
            <li>Sinh viên mang theo thẻ sinh viên hoặc mã QR vé để quét điểm danh.</li>
            <li>Vui lòng có mặt trước giờ khai mạc 15 phút để ổn định chỗ ngồi.</li>
            <li>Trang phục lịch sự, tác phong văn minh học đường.</li>
          </ul>
        </div>
      </aside>
    </div>

    <?php if (!empty($lienQuan)): ?>
      <section class="su-kien-lien-quan mt-5" aria-labelledby="tieu-de-lien-quan">
        <div class="section-head mb-4">
          <div>
            <h2 id="tieu-de-lien-quan" class="tieu-de-muc">
              Sự Kiện Tương Tự
            </h2>
            <p class="section-head__mota mt-1">
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
                <div class="the-tin__meta mb-1" style="font-size: 0.8125rem; color: var(--chu-phu);">
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

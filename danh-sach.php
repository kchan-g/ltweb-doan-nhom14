<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSuKien;
use App\Services\GioHang;

$kho = new KhoSuKien(db());
$gio = new GioHang();

$duong = ['options' => ['min_range' => 1]];
$qRaw = trim((string)($_GET['q'] ?? ''));

$dmId = filter_var($_GET['dm'] ?? '', FILTER_VALIDATE_INT, $duong);
$giaToiDa = filter_var($_GET['gia'] ?? '', FILTER_VALIDATE_INT, $duong);

$loc = [
    'q'        => $qRaw,
    'dm'       => ($dmId !== false && $dmId > 0) ? $dmId : null,
    'giaToiDa' => ($giaToiDa !== false && $giaToiDa > 0) ? $giaToiDa : null,
];

$sx = (string)($_GET['sx'] ?? 'moi');
$kieuSx = [
    'moi'      => 'Mới nhất',
    'gia-tang' => 'Giá vé tăng dần',
    'gia-giam' => 'Giá vé giảm dần',
    'ten'      => 'Tên sự kiện (A – Z)',
];
if (!array_key_exists($sx, $kieuSx)) {
    $sx = 'moi';
}

$moiTrang = 8;
$tong = $kho->dem($loc);
$soTrang = max(1, (int)ceil($tong / $moiTrang));

$hienTai = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'default' => 1],
]);
if ($hienTai === false || $hienTai < 1) {
    $hienTai = 1;
}
$hienTai = min($hienTai, $soTrang);

$danhSach = $kho->trang($loc, $sx, $moiTrang, ($hienTai - 1) * $moiTrang);

$dsDanhMuc = db()->query('SELECT id, ten FROM danh_muc ORDER BY id')->fetchAll();

function lienKetTrang(int $so): string
{
    return '?' . http_build_query(array_merge($_GET, ['trang' => $so]));
}

$tieuDe = 'Danh sách sự kiện sinh viên - UniEvent';
$trang  = 'danh-sach';

require __DIR__ . '/inc/header.php';
?>

<main>
  <div class="container">
    <nav class="breadcrumb mb-4" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai">Danh sách sự kiện</span>
    </nav>

    <div class="trang-tieu-de mb-4">
      <h1 class="font-hien-thi fw-800 text-chinh mb-2">Danh Sách Sự Kiện Đang Diễn Ra</h1>
      <p class="do-dai-chuan text-phu" style="font-size: 1.05rem;">
        Khám phá và đăng ký các hoạt động học thuật, văn hóa, thể thao đang diễn ra tại Trường Đại học Sư phạm – Đại học Đà Nẵng.
      </p>
    </div>

    <section class="bo-loc mb-5" aria-labelledby="tieu-de-bo-loc">
      <h2 id="tieu-de-bo-loc" class="chi-danh-cho-sr">
        Bộ lọc tìm kiếm sự kiện
      </h2>

      <form action="danh-sach.php" method="GET" id="form-loc-su-kien">
        <div class="form-nhom">
          <label for="tim-kiem-danh-sach">Từ khóa tìm kiếm</label>
          <input
            type="search"
            id="tim-kiem-danh-sach"
            name="q"
            value="<?= e($loc['q']) ?>"
            placeholder="Tìm theo tên sự kiện, hội thảo..."
            autocomplete="off"
          />
        </div>

        <div class="form-nhom">
          <label for="danh-muc-select">Phân loại danh mục</label>
          <select id="danh-muc-select" name="dm">
            <option value="">Tất cả danh mục sự kiện</option>
            <?php foreach ($dsDanhMuc as $dm): ?>
              <option value="<?= $dm['id'] ?>" <?= $loc['dm'] === (int)$dm['id'] ? 'selected' : '' ?>>
                <?= e($dm['ten']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-nhom">
          <label for="gia-select">Mức giá vé</label>
          <select id="gia-select" name="gia">
            <option value="">Mọi mức giá</option>
            <option value="1" <?= $loc['giaToiDa'] === 1 ? 'selected' : '' ?>>Miễn phí (0 đ)</option>
            <option value="20000" <?= $loc['giaToiDa'] === 20000 ? 'selected' : '' ?>>Dưới 20.000 đ</option>
            <option value="50000" <?= $loc['giaToiDa'] === 50000 ? 'selected' : '' ?>>Dưới 50.000 đ</option>
            <option value="100000" <?= $loc['giaToiDa'] === 100000 ? 'selected' : '' ?>>Dưới 100.000 đ</option>
          </select>
        </div>

        <div class="form-nhom">
          <label for="sap-xep-select">Sắp xếp theo</label>
          <select id="sap-xep-select" name="sx">
            <?php foreach ($kieuSx as $key => $nhan): ?>
              <option value="<?= $key ?>" <?= $sx === $key ? 'selected' : '' ?>>
                <?= e($nhan) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="d-flex gap-2 align-items-end" style="height: 44px;">
          <button type="submit" class="nut-bam nut-nhan h-100" style="padding: 0 1.5rem;">
            Lọc sự kiện
          </button>
          <?php if ($loc['q'] !== '' || $loc['dm'] !== null || $loc['giaToiDa'] !== null || $sx !== 'moi'): ?>
            <a href="danh-sach.php" class="nut-bam nut-phu h-100 d-inline-flex align-items-center" style="padding: 0 1.25rem;">
              Đặt lại
            </a>
          <?php endif; ?>
        </div>
      </form>
    </section>

    <section aria-labelledby="tieu-de-tat-ca-su-kien">
      <div class="section-head mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2 id="tieu-de-tat-ca-su-kien" class="tieu-de-muc">
            Tất Cả Sự Kiện
          </h2>
          <p class="section-head__mota mt-1">
            Tìm thấy <strong><?= $tong ?></strong> sự kiện phù hợp
            <?php if ($loc['q'] !== ''): ?>
              — Từ khóa: "<strong><?= e($loc['q']) ?></strong>"
            <?php endif; ?>
            (Trang <?= $hienTai ?> / <?= $soTrang ?>)
          </p>
        </div>
      </div>

      <?php if ($tong === 0): ?>
        <div class="p-5 text-center my-4" style="background:var(--nen-surface); border-radius:16px; border:1px dashed var(--border-dam); box-shadow:var(--shadow-sm);">
          <div style="font-size:3.5rem; margin-bottom:1rem;">🔎</div>
          <h3 class="h4" style="color:var(--mau-chinh-toi); font-weight:700;">Không tìm thấy sự kiện phù hợp</h3>
          <p class="text-phu mb-4" style="max-width:500px; margin-left:auto; margin-right:auto;">
            Rất tiếc, không có sự kiện nào khớp với tiêu chí bạn đã chọn. Vui lòng thử tìm kiếm với từ khóa khác hoặc đặt lại bộ lọc.
          </p>
          <a href="danh-sach.php" class="nut-bam nut-nhan" style="padding: 10px 24px;">Xem toàn bộ sự kiện</a>
        </div>
      <?php else: ?>
        <ul class="danh-sach-su-kien" id="khung-danh-sach-su-kien">
          <?php foreach ($danhSach as $sk): ?>
            <li>
              <article class="the-tin">
                <div class="the-tin__media">
                  <span class="<?= e($sk->badgeClass ?: 'badge') ?>"><?= e($sk->tenDanhMuc) ?></span>
                  <img
                    src="<?= e($sk->hinhAnh) ?>"
                    alt="<?= e($sk->ten) ?>"
                    loading="lazy"
                    width="360"
                    height="225"
                  />
                </div>
                <div class="the-tin__body">
                  <h3><?= e($sk->ten) ?></h3>
                  <p class="event-meta">Thời gian: <?= e($sk->thoiGian) ?></p>
                  <p class="event-meta">Địa điểm: <?= e($sk->diaDiem) ?></p>
                  <p class="event-meta">Giá vé: <strong><?= vnd($sk->gia) ?></strong> - Còn lại: <?= $sk->ton_kho ?> vé</p>
                  <div class="the-tin__actions mt-3">
                    <a href="chi-tiet.php?id=<?= $sk->id ?>" class="nut-bam nut-nhan nut-nho">
                      Xem chi tiết
                    </a>
                    <?php if ($sk->ton_kho <= 0): ?>
                      <span class="nut-bam nut-phu nut-nho" style="color:#ef4444; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3);">
                        Hết vé
                      </span>
                    <?php elseif ($gio->daXacNhan($sk->id)): ?>
                      <a href="gio-hang.php#da-xac-nhan" class="nut-bam nut-phu nut-nho" style="color:#16a34a; background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.3); font-weight:700;" title="Bạn đã đăng ký thành công sự kiện này">
                        ✓ Đã đăng ký
                      </a>
                    <?php else: ?>
                      <form action="gio-hang.php" method="POST" style="margin:0; display:inline;" class="form-them-gio-nhanh">
                        <input type="hidden" name="hanh_dong" value="them">
                        <input type="hidden" name="id" value="<?= $sk->id ?>">
                        <input type="hidden" name="so_luong" value="1">
                        <button type="submit" class="nut-bam nut-nho" title="Đăng ký giữ chỗ sự kiện">
                          + Đăng ký
                        </button>
                      </form>
                    <?php endif; ?>
                    <button
                      type="button"
                      class="nut-yeu-thich"
                      data-id="<?= $sk->id ?>"
                      aria-pressed="false"
                      aria-label="Lưu tin - Lưu sự kiện <?= e($sk->ten) ?>"
                      title="Lưu sự kiện vào danh sách yêu thích"
                    >
                      <?= uiIcon('heart', '', 13) ?> Lưu
                    </button>
                  </div>
                </div>
              </article>
            </li>
          <?php endforeach; ?>
        </ul>

        <?php if ($soTrang > 1): ?>
          <nav aria-label="Phân trang sự kiện" class="vung-phan-trang mt-5 d-flex justify-content-center">
            <ul class="phan-trang" style="display:flex; list-style:none; gap:8px; padding:0; align-items:center;">
              <?php if ($hienTai > 1): ?>
                <li>
                  <a href="<?= e(lienKetTrang($hienTai - 1)) ?>" class="nut-bam nut-phu nut-nho" aria-label="Trang trước">
                    &laquo; Trước
                  </a>
                </li>
              <?php endif; ?>

              <?php for ($p = 1; $p <= $soTrang; $p++): ?>
                <li>
                  <a href="<?= e(lienKetTrang($p)) ?>"
                     class="nut-bam <?= $p === $hienTai ? 'nut-nhan' : 'nut-phu' ?> nut-nho"
                     <?= $p === $hienTai ? 'aria-current="page" style="font-weight:700;"' : '' ?>>
                    <?= $p ?>
                  </a>
                </li>
              <?php endfor; ?>

              <?php if ($hienTai < $soTrang): ?>
                <li>
                  <a href="<?= e(lienKetTrang($hienTai + 1)) ?>" class="nut-bam nut-phu nut-nho" aria-label="Trang sau">
                    Sau &raquo;
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </div>
</main>

<script src="js/tim-kiem.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>

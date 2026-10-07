<?php
/**
 * danh-sach.php
 * Trang danh sách sự kiện do PHP sinh từ tệp data/su-kien.json.
 * Xử lý biểu mẫu GET phía máy chủ: tìm kiếm từ khóa (?q=...), lọc danh mục (?dm=...),
 * sắp xếp (?sx=...), giữ lại lựa chọn và thông báo trạng thái rỗng khi không có kết quả.
 * Hoạt động 100% khi tắt JavaScript (Progressive Enhancement).
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSuKien;
use App\Services\GioHang;

$kho = new KhoSuKien(__DIR__ . '/data/su-kien.json');
$gio = new GioHang();

// 1. Nhận và lọc an toàn các tham số GET
$tuKhoaRaw = $_GET['q'] ?? ($_GET['timkiem'] ?? null);
$tuKhoa = ($tuKhoaRaw !== null && trim($tuKhoaRaw) !== '') ? trim($tuKhoaRaw) : null;

$danhMucRaw = $_GET['dm'] ?? ($_GET['danh-muc'] ?? null);
$danhMucHopLe = ['hoi-thao', 'viec-lam', 'van-hoa', 'the-thao', 'hoc-thuat', 'cong-nghe'];
$danhMuc = in_array($danhMucRaw, $danhMucHopLe, true) ? $danhMucRaw : null;

$sapXepRaw = $_GET['sx'] ?? ($_GET['sap-xep'] ?? null);
$sapXepHopLe = ['moi-nhat', 'sap-dien-ra', 'gia-tang', 'gia-giam', 'ten-az', 'diem-cao'];
$sapXep = in_array($sapXepRaw, $sapXepHopLe, true) ? $sapXepRaw : 'moi-nhat';

// 2. Thực hiện truy vấn danh sách từ máy chủ
$danhSach = $kho->timKiem($tuKhoa, $danhMuc, $sapXep);
$tongSo = count($danhSach);

$tieuDe = 'Danh sách sự kiện sinh viên - UniEvent';
$trang  = 'danh-sach';

require __DIR__ . '/inc/header.php';
?>

<main>
  <div class="container">
    <!-- Tiêu đề trang & Thanh điều hướng Breadcrumb -->
    <nav class="breadcrumb mb-3" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai">Danh sách sự kiện</span>
    </nav>

    <div class="trang-tieu-de mb-4">
      <h1>Danh Sách Các Sự Kiện Đang Diễn Ra</h1>
      <p class="do-dai-chuan text-phu">
        Khám phá và đăng ký các hoạt động học thuật, văn hóa, thể thao đang diễn ra tại Trường Đại học Sư phạm – Đại học Đà Nẵng.
      </p>
    </div>

    <!-- BIỂU MẪU LỌC & TÌM KIẾM PHÍA MÁY CHỦ (GET FORM - URL chia sẻ được) -->
    <section class="bo-loc mb-5" aria-labelledby="tieu-de-bo-loc">
      <h2 id="tieu-de-bo-loc" class="chi-danh-cho-sr">
        Bộ lọc tìm kiếm sự kiện
      </h2>

      <form action="danh-sach.php" method="GET">
        <!-- 1. Ô tìm kiếm từ khóa -->
        <div class="form-nhom">
          <label for="tim-kiem-danh-sach">Tìm kiếm sự kiện:</label>
          <input
            type="search"
            id="tim-kiem-danh-sach"
            name="q"
            value="<?= e($tuKhoa ?? '') ?>"
            placeholder="Tìm kiếm sự kiện, địa điểm, diễn giả..."
            autocomplete="off"
          />
        </div>

        <!-- 2. Lọc theo danh mục -->
        <div class="form-nhom">
          <label for="danh-muc-select">Chọn phân loại danh mục:</label>
          <select id="danh-muc-select" name="dm">
            <option value="">Tất cả các danh mục sự kiện</option>
            <option value="hoi-thao" <?= $danhMuc === 'hoi-thao' ? 'selected' : '' ?>>Hội thảo khoa học</option>
            <option value="viec-lam" <?= $danhMuc === 'viec-lam' ? 'selected' : '' ?>>Việc làm &amp; Tuyển dụng</option>
            <option value="van-hoa" <?= $danhMuc === 'van-hoa' ? 'selected' : '' ?>>Văn hóa &amp; Nghệ thuật</option>
            <option value="the-thao" <?= $danhMuc === 'the-thao' ? 'selected' : '' ?>>Thể dục - Thể thao</option>
            <option value="hoc-thuat" <?= $danhMuc === 'hoc-thuat' ? 'selected' : '' ?>>Học thuật &amp; Kỹ năng</option>
            <option value="cong-nghe" <?= $danhMuc === 'cong-nghe' ? 'selected' : '' ?>>Công nghệ &amp; Đổi mới</option>
          </select>
        </div>

        <!-- 3. Sắp xếp -->
        <div class="form-nhom">
          <label for="sap-xep-select">Sắp xếp theo tiêu chí:</label>
          <select id="sap-xep-select" name="sx">
            <option value="moi-nhat" <?= $sapXep === 'moi-nhat' ? 'selected' : '' ?>>Mới nhất tuần này</option>
            <option value="sap-dien-ra" <?= $sapXep === 'sap-dien-ra' ? 'selected' : '' ?>>Sắp diễn ra gần nhất</option>
            <option value="diem-cao" <?= $sapXep === 'diem-cao' ? 'selected' : '' ?>>Điểm rèn luyện cao nhất</option>
            <option value="ten-az" <?= $sapXep === 'ten-az' ? 'selected' : '' ?>>Tên sự kiện (A - Z)</option>
          </select>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="nut-bam nut-nhan">
            Lọc sự kiện
          </button>
          <?php if ($tuKhoa !== null || $danhMuc !== null || $sapXep !== 'moi-nhat'): ?>
            <a href="danh-sach.php" class="nut-bam nut-phu">
              Đặt lại
            </a>
          <?php endif; ?>
        </div>
      </form>
    </section>

    <!-- KẾT QUẢ TÌM KIẾM / DANH SÁCH SỰ KIỆN DO PHP SINH -->
    <section aria-labelledby="tieu-de-tat-ca-su-kien">
      <div class="section-head mb-4">
        <div>
          <h2 id="tieu-de-tat-ca-su-kien" class="tieu-de-muc">
            Tất Cả Sự Kiện Mới Nhất
          </h2>
          <p class="section-head__mota">
            Kết quả: <strong><?= $tongSo ?></strong> sự kiện phù hợp
            <?php if ($tuKhoa !== null): ?>
              — Từ khóa: "<strong><?= e($tuKhoa) ?></strong>"
            <?php endif; ?>
          </p>
        </div>
      </div>

      <?php if ($tongSo === 0): ?>
        <!-- TRẠNG THÁI RỖNG (Không tìm thấy kết quả) -->
        <div class="p-5 text-center my-4" style="background:var(--nen-surface); border-radius:var(--radius-vua); border:1px dashed var(--border-nhat);">
          <div style="font-size:3rem; margin-bottom:1rem;">🔎</div>
          <h3 class="h4" style="color:var(--mau-chinh-toi);">Không tìm thấy sự kiện phù hợp</h3>
          <p class="text-phu mb-3" style="max-width:500px; margin:0 auto;">
            Rất tiếc, không có sự kiện nào khớp với từ khóa hoặc bộ lọc bạn đã chọn. Vui lòng thử tìm kiếm bằng từ khóa ngắn hơn hoặc chọn lại tất cả danh mục.
          </p>
          <a href="danh-sach.php" class="nut-bam nut-chinh">Xem toàn bộ sự kiện</a>
        </div>
      <?php else: ?>
        <!-- DANH SÁCH SỰ KIỆN CHUẨN DESIGN SYSTEM UNIEVENT -->
        <ul class="danh-sach-su-kien">
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
                  <p class="event-meta">Thời gian: <?= e($sk->thoiGian) ?>, <?= e($sk->ngay) ?></p>
                  <p class="event-meta">Địa điểm: <?= e($sk->diaDiem) ?></p>
                  <p class="event-meta">Quyền lợi: +<?= $sk->diemRenLuyen ?> Điểm rèn luyện</p>
                  <div class="the-tin__actions mt-3">
                    <a href="chi-tiet.php?id=<?= $sk->id ?>" class="nut-bam nut-nhan nut-nho">
                      Xem chi tiết
                    </a>
                    <?php if ($gio->daXacNhan($sk->id)): ?>
                      <a href="gio-hang.php#da-xac-nhan" class="nut-bam nut-phu nut-nho" style="color:#15803d; background:#f0fdf4; border-color:#86efac; font-weight:700;" title="Bạn đã đăng ký thành công sự kiện này (1 vé)">
                        ✓ Đã đăng ký
                      </a>
                    <?php elseif ($gio->dangChoXacNhan($sk->id)): ?>
                      <a href="gio-hang.php" class="nut-bam nut-phu nut-nho" style="color:#b45309; background:#fffbeb; border-color:#fde68a; font-weight:700;" title="Sự kiện đang chờ xác nhận - Bấm để xác nhận">
                        ⏳ Chờ xác nhận
                      </a>
                    <?php else: ?>
                      <form action="gio-hang.php" method="POST" style="margin:0; display:inline;">
                        <input type="hidden" name="hanh_dong" value="them">
                        <input type="hidden" name="id" value="<?= $sk->id ?>">
                        <input type="hidden" name="so_luong" value="1">
                        <button type="submit" class="nut-bam nut-nho" title="Đăng ký giữ chỗ sự kiện (1 vé)">
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
                      ♡ Lưu tin
                    </button>
                  </div>
                </div>
              </article>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>

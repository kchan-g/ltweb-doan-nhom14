<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/kiem-tra.php';

use App\Data\KhoSuKien;

yeuCauQuyen('quan_tri');

$kho = new KhoSuKien(db());

$dsDanhMuc = db()->query('SELECT id, ten FROM danh_muc ORDER BY id')->fetchAll();
$idDanhMuc = array_map(fn($dm) => (int)$dm['id'], $dsDanhMuc);

$du = [
    'ten'            => '',
    'danh_muc_id'    => '',
    'gia'            => '0',
    'ton_kho'        => '100',
    'dia_diem'       => '',
    'thoi_gian'      => '',
    'diem_ren_luyen' => '5',
    'dien_gia'       => '',
    'mo_ta_ngan'     => '',
    'mo_ta_chi_tiet' => '',
    'video'          => '',
];
$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$duKiemTra, $loi] = kiemTraSanPham($_POST, $idDanhMuc);
    $du = array_merge($du, $_POST);

    $tenAnhLuu = 'images/AI.jpg';

    if (isset($_FILES['anh']) && $_FILES['anh']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['anh'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $loi['anh'] = 'Có lỗi xảy ra khi tải ảnh lên máy chủ.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $loi['anh'] = 'Dung lượng ảnh vượt quá giới hạn 2 MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $duoiHopLe = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
            ];
            if (!isset($duoiHopLe[$mime])) {
                $loi['anh'] = 'Tệp tải lên không phải ảnh hợp lệ (chỉ nhận JPG, PNG, WebP).';
            } else {
                $tenNgauNhien = 'event_' . bin2hex(random_bytes(10)) . '.' . $duoiHopLe[$mime];
                $duongDanDich = __DIR__ . '/uploads/' . $tenNgauNhien;
                if (move_uploaded_file($file['tmp_name'], $duongDanDich)) {
                    $tenAnhLuu = 'uploads/' . $tenNgauNhien;
                } else {
                    $loi['anh'] = 'Không thể lưu ảnh vào thư mục máy chủ.';
                }
            }
        }
    }

    if (empty($loi)) {
        try {
            $duKiemTra['anh'] = $tenAnhLuu;
            $moiId = $kho->them($duKiemTra);

            flash("🎉 Đã thêm mới sự kiện \"{$duKiemTra['ten']}\" thành công (Mã #{$moiId})!");
            header('Location: quan-tri.php');
            exit;
        } catch (Throwable $e) {
            error_log('Lỗi thêm sự kiện: ' . $e->getMessage());
            $loi['he_thong'] = 'Không thể thêm sự kiện do lỗi máy chủ: ' . $e->getMessage();
        }
    }
}

$tieuDe = 'Thêm sự kiện mới - UniEvent Quản trị';
$trang  = 'quan-tri';

require __DIR__ . '/inc/header.php';
?>

<main class="vung-chinh noi-dung-chinh container py-4">
  <div style="max-width: 860px; margin: 20px auto; background: var(--nen-surface); border: 1px solid var(--border-nhat); border-radius: 16px; padding: 36px; box-shadow: var(--shadow-card);">
    
    <nav class="breadcrumb mb-4" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <a href="quan-tri.php">Quản trị</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai">Thêm sự kiện mới</span>
    </nav>

    <div class="trang-tieu-de mb-4">
      <h1 class="h3 mb-2 font-hien-thi fw-800 text-chinh">Thêm Mới Sự Kiện Sinh Viên</h1>
      <p class="text-phu fs-meta" style="font-size:0.95rem;">
        Nhập thông tin sự kiện, hội thảo hoặc giải đấu để hiển thị trên cổng thông tin UniEvent.
      </p>
    </div>

    <?php if (isset($loi['he_thong'])): ?>
      <div class="hop-thong-bao mb-4" role="alert" style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); color:#ef4444; padding:14px 20px; border-radius:12px;">
        <?= e($loi['he_thong']) ?>
      </div>
    <?php endif; ?>

    <form action="them-san-pham.php" method="POST" enctype="multipart/form-data" novalidate>
      <div class="form-group mb-4">
        <label for="ten" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
          Tên sự kiện <span style="color:#ef4444;">*</span>
        </label>
        <input
          type="text"
          id="ten"
          name="ten"
          class="form-control <?= isset($loi['ten']) ? 'is-invalid' : '' ?>"
          placeholder="Ví dụ: Workshop IT: Khám phá AI và Cloud 2026"
          value="<?= e($du['ten']) ?>"
          style="width:100%; height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
          required
        />
        <?php if (isset($loi['ten'])): ?>
          <div style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;"><?= e($loi['ten']) ?></div>
        <?php endif; ?>
      </div>

      <div class="row g-3 mb-4" style="display:flex; flex-wrap:wrap; gap:16px;">
        <div style="flex:1 1 240px;">
          <label for="danh_muc_id" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
            Danh mục sự kiện <span style="color:#ef4444;">*</span>
          </label>
          <select id="danh_muc_id" name="danh_muc_id" class="form-control <?= isset($loi['danh_muc_id']) ? 'is-invalid' : '' ?>" style="width:100%; height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;" required>
            <option value="">-- Chọn danh mục --</option>
            <?php foreach ($dsDanhMuc as $dm): ?>
              <option value="<?= $dm['id'] ?>" <?= ((string)$du['danh_muc_id']) === ((string)$dm['id']) ? 'selected' : '' ?>>
                <?= e($dm['ten']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($loi['danh_muc_id'])): ?>
            <div style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;"><?= e($loi['danh_muc_id']) ?></div>
          <?php endif; ?>
        </div>

        <div style="flex:1 1 240px;">
          <label for="gia" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
            Giá vé (VNĐ, 0 đ = Miễn phí) <span style="color:#ef4444;">*</span>
          </label>
          <input
            type="number"
            id="gia"
            name="gia"
            min="0"
            step="1000"
            class="form-control <?= isset($loi['gia']) ? 'is-invalid' : '' ?>"
            value="<?= e($du['gia']) ?>"
            style="width:100%; height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
            required
          />
          <?php if (isset($loi['gia'])): ?>
            <div style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;"><?= e($loi['gia']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="row g-3 mb-4" style="display:flex; flex-wrap:wrap; gap:16px;">
        <div style="flex:1 1 240px;">
          <label for="ton_kho" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
            Số lượng vé phát hành (Tồn kho) <span style="color:#ef4444;">*</span>
          </label>
          <input
            type="number"
            id="ton_kho"
            name="ton_kho"
            min="0"
            max="99999"
            class="form-control <?= isset($loi['ton_kho']) ? 'is-invalid' : '' ?>"
            value="<?= e($du['ton_kho']) ?>"
            style="width:100%; height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
            required
          />
          <?php if (isset($loi['ton_kho'])): ?>
            <div style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;"><?= e($loi['ton_kho']) ?></div>
          <?php endif; ?>
        </div>

        <div style="flex:1 1 240px;">
          <label for="diem_ren_luyen" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
            Điểm rèn luyện tích lũy (+Điểm)
          </label>
          <input
            type="number"
            id="diem_ren_luyen"
            name="diem_ren_luyen"
            min="0"
            max="30"
            class="form-control"
            value="<?= e($du['diem_ren_luyen']) ?>"
            style="width:100%; height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
          />
        </div>
      </div>

      <div class="row g-3 mb-4" style="display:flex; flex-wrap:wrap; gap:16px;">
        <div style="flex:1 1 240px;">
          <label for="dia_diem" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
            Địa điểm tổ chức <span style="color:#ef4444;">*</span>
          </label>
          <input
            type="text"
            id="dia_diem"
            name="dia_diem"
            class="form-control <?= isset($loi['dia_diem']) ? 'is-invalid' : '' ?>"
            placeholder="Ví dụ: Hội trường A, Khu Hiệu bộ"
            value="<?= e($du['dia_diem']) ?>"
            style="width:100%; height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
            required
          />
          <?php if (isset($loi['dia_diem'])): ?>
            <div style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;"><?= e($loi['dia_diem']) ?></div>
          <?php endif; ?>
        </div>

        <div style="flex:1 1 240px;">
          <label for="thoi_gian" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
            Thời gian tổ chức (YYYY-MM-DD HH:MM:SS) <span style="color:#ef4444;">*</span>
          </label>
          <input
            type="text"
            id="thoi_gian"
            name="thoi_gian"
            class="form-control <?= isset($loi['thoi_gian']) ? 'is-invalid' : '' ?>"
            placeholder="2026-11-20 08:00:00"
            value="<?= e($du['thoi_gian']) ?>"
            style="width:100%; height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
            required
          />
          <?php if (isset($loi['thoi_gian'])): ?>
            <div style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;"><?= e($loi['thoi_gian']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="row g-3 mb-4" style="display:flex; flex-wrap:wrap; gap:16px;">
        <div style="flex:1 1 240px;">
          <label for="dien_gia" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
            Diễn giả / Khách mời
          </label>
          <input
            type="text"
            id="dien_gia"
            name="dien_gia"
            class="form-control"
            placeholder="Ví dụ: TS. Nguyễn Văn A"
            value="<?= e($du['dien_gia']) ?>"
            style="width:100%; height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
          />
        </div>

        <div style="flex:1 1 240px;">
          <label for="anh" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
            Hình ảnh sự kiện (JPG, PNG, WebP &le; 2MB)
          </label>
          <input type="file" id="anh" name="anh" class="form-control" accept="image/*" style="width:100%; padding:0.4rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.85rem;" />
          <?php if (isset($loi['anh'])): ?>
            <div style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;"><?= e($loi['anh']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="form-group mb-4">
        <label for="mo_ta_ngan" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
          Mô tả tóm tắt sự kiện
        </label>
        <textarea
          id="mo_ta_ngan"
          name="mo_ta_ngan"
          rows="2"
          class="form-control"
          placeholder="Tóm tắt ngắn gọn nội dung sự kiện..."
          style="width:100%; padding:0.75rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem; font-family:inherit;"
        ><?= e($du['mo_ta_ngan']) ?></textarea>
      </div>

      <div class="form-group mb-4">
        <label for="mo_ta_chi_tiet" class="form-label fw-bold mb-2" style="font-size:0.875rem; color:var(--chu-chinh);">
          Nội dung mô tả chi tiết
        </label>
        <textarea
          id="mo_ta_chi_tiet"
          name="mo_ta_chi_tiet"
          rows="5"
          class="form-control"
          placeholder="Giới thiệu đầy đủ chương trình, quyền lợi tham gia..."
          style="width:100%; padding:0.75rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem; font-family:inherit;"
        ><?= e($du['mo_ta_chi_tiet']) ?></textarea>
      </div>

      <div class="d-flex justify-content-between align-items-center pt-2">
        <a href="quan-tri.php" class="nut-bam nut-phu" style="padding: 10px 20px;">← Hủy bỏ</a>
        <button type="submit" class="nut-bam nut-nhan py-2 px-4" style="font-weight:700; padding: 12px 28px;">
          + Lưu sự kiện mới
        </button>
      </div>
    </form>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>

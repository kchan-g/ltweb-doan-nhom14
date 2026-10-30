<?php
/**
 * src/Data/KhoSuKien.php
 * Tầng truy cập dữ liệu (Data Access Layer - DAL) cho thực thể Sự Kiện (su_kien).
 * Quản lý toàn bộ câu lệnh SQL và tương tác với CSDL MySQL thông qua thư viện PDO.
 * Mọi tham số từ người dùng đều sử dụng Prepared Statement để phòng chống SQL Injection tuyệt đối.
 */

declare(strict_types=1);

namespace App\Data;

use App\Models\SuKien;
use PDO;

class KhoSuKien
{
    private PDO $db;

    /**
     * @param PDO|string|null $db Kết nối PDO (hoặc đường dẫn chuỗi tương thích ngược)
     */
    public function __construct(PDO|string|null $db = null)
    {
        if ($db instanceof PDO) {
            $this->db = $db;
        } else {
            // Tự động sử dụng hàm db() từ inc/db.php
            $this->db = db();
        }
    }

    /**
     * Xây dựng mệnh đề WHERE và danh sách tham số an toàn từ mảng bộ lọc.
     *
     * @param array{q?: string, dm?: int|null, giaToiDa?: int|null} $loc Mảng điều kiện lọc
     * @return array{0: string, 1: list<mixed>} Mảng gồm [$whereClause, $params]
     */
    private function dieuKien(array $loc): array
    {
        $dk = [];
        $ts = [];

        // 1. Tìm kiếm theo từ khóa (LIKE an toàn với addcslashes)
        if (!empty($loc['q'])) {
            $tuKhoa = trim((string)$loc['q']);
            $dk[] = 's.ten LIKE ?';
            $ts[] = '%' . addcslashes($tuKhoa, '%_\\') . '%';
        }

        // 2. Lọc theo danh mục
        if (!empty($loc['dm'])) {
            $dk[] = 's.danh_muc_id = ?';
            $ts[] = (int)$loc['dm'];
        }

        // 3. Lọc theo mức giá tối đa
        if (isset($loc['giaToiDa']) && $loc['giaToiDa'] !== null && $loc['giaToiDa'] !== '') {
            $dk[] = 's.gia <= ?';
            $ts[] = (int)$loc['giaToiDa'];
        }

        $where = $dk ? 'WHERE ' . implode(' AND ', $dk) : '';
        return [$where, $ts];
    }

    /**
     * Đếm tổng số dòng sự kiện khớp với bộ lọc (phục vụ phân trang).
     */
    public function dem(array $loc = []): int
    {
        [$where, $ts] = $this->dieuKien($loc);
        $st = $this->db->prepare("SELECT COUNT(*) FROM su_kien s $where");
        $st->execute($ts);
        return (int)$st->fetchColumn();
    }

    /**
     * Lấy một trang danh sách sự kiện theo bộ lọc, sắp xếp và phân trang an toàn.
     *
     * @param array $loc Bộ lọc
     * @param string $sx Tiêu chí sắp xếp
     * @param int $soDong Số dòng mỗi trang
     * @param int $boQua Số dòng bỏ qua (OFFSET)
     * @return SuKien[] Danh sách đối tượng sự kiện
     */
    public function trang(array $loc, string $sx, int $soDong, int $boQua): array
    {
        [$where, $ts] = $this->dieuKien($loc);

        // Danh sách tiêu chí sắp xếp cho phép (Whitelist an toàn)
        $orderBy = match ($sx) {
            'gia-tang' => 's.gia ASC, s.id ASC',
            'gia-giam' => 's.gia DESC, s.id DESC',
            'ten'      => 's.ten ASC, s.id ASC',
            default    => 's.id DESC', // Mặc định: mới nhất
        };

        $sql = "SELECT s.*, d.ten AS ten_danh_muc, d.slug AS slug_danh_muc
                FROM su_kien s
                JOIN danh_muc d ON s.danh_muc_id = d.id
                $where
                ORDER BY $orderBy
                LIMIT ? OFFSET ?";

        $st = $this->db->prepare($sql);
        $st->execute([...$ts, $soDong, $boQua]);

        $ketQua = [];
        while ($dong = $st->fetch()) {
            $ketQua[] = SuKien::tuMang($dong);
        }

        return $ketQua;
    }

    /**
     * Lấy toàn bộ sự kiện mới nhất.
     *
     * @param int $gioiHan Số lượng giới hạn
     * @return SuKien[]
     */
    public function tatCa(int $gioiHan = 100): array
    {
        $st = $this->db->prepare(
            'SELECT s.*, d.ten AS ten_danh_muc, d.slug AS slug_danh_muc
             FROM su_kien s
             JOIN danh_muc d ON s.danh_muc_id = d.id
             ORDER BY s.id DESC
             LIMIT ?'
        );
        $st->execute([$gioiHan]);

        $ketQua = [];
        while ($dong = $st->fetch()) {
            $ketQua[] = SuKien::tuMang($dong);
        }

        return $ketQua;
    }

    /**
     * Tìm một sự kiện theo ID duy nhất.
     */
    public function timTheoId(int $id): ?SuKien
    {
        if ($id <= 0) {
            return null;
        }

        $st = $this->db->prepare(
            'SELECT s.*, d.ten AS ten_danh_muc, d.slug AS slug_danh_muc
             FROM su_kien s
             JOIN danh_muc d ON s.danh_muc_id = d.id
             WHERE s.id = ?'
        );
        $st->execute([$id]);
        $dong = $st->fetch();

        return $dong ? SuKien::tuMang($dong) : null;
    }

    /**
     * Lấy danh sách sự kiện theo mảng các ID trong duy nhất MỘT câu truy vấn SQL WHERE id IN (...).
     * Loại bỏ hoàn toàn lỗi truy vấn trong vòng lặp N+1, giữ đúng thứ tự mảng $ids truyền vào.
     *
     * @param int[] $ids Mảng danh sách ID cần lấy
     * @return SuKien[]
     */
    public function layTheoIds(array $ids): array
    {
        $idsHopLe = array_values(array_filter(
            array_unique(array_map('intval', $ids)),
            fn(int $id) => $id > 0
        ));

        if (empty($idsHopLe)) {
            return [];
        }

        // Tạo chuỗi dấu hỏi ?, ?, ? tương ứng số phần tử
        $placeholders = implode(',', array_fill(0, count($idsHopLe), '?'));

        $sql = "SELECT s.*, d.ten AS ten_danh_muc, d.slug AS slug_danh_muc
                FROM su_kien s
                JOIN danh_muc d ON s.danh_muc_id = d.id
                WHERE s.id IN ($placeholders)";

        $st = $this->db->prepare($sql);
        $st->execute($idsHopLe);

        $map = [];
        while ($dong = $st->fetch()) {
            $sk = SuKien::tuMang($dong);
            $map[$sk->id] = $sk;
        }

        // Trả về đúng thứ tự ban đầu của $idsHopLe
        $ketQua = [];
        foreach ($idsHopLe as $id) {
            if (isset($map[$id])) {
                $ketQua[] = $map[$id];
            }
        }

        return $ketQua;
    }

    /**
     * Lấy danh sách 4 sự kiện cùng danh mục (loại trừ sự kiện hiện tại).
     */
    public function layCungDanhMuc(int $danhMucId, int $truId, int $gioiHan = 4): array
    {
        $st = $this->db->prepare(
            'SELECT s.*, d.ten AS ten_danh_muc, d.slug AS slug_danh_muc
             FROM su_kien s
             JOIN danh_muc d ON s.danh_muc_id = d.id
             WHERE s.danh_muc_id = ? AND s.id != ?
             ORDER BY s.id DESC
             LIMIT ?'
        );
        $st->execute([$danhMucId, $truId, $gioiHan]);

        $ketQua = [];
        while ($dong = $st->fetch()) {
            $ketQua[] = SuKien::tuMang($dong);
        }

        return $ketQua;
    }

    /**
     * Thêm mới một sự kiện vào CSDL.
     *
     * @param array<string, mixed> $d Dữ liệu sự kiện đã validate
     * @return int Mã ID sự kiện vừa tạo (lastInsertId)
     */
    public function them(array $d): int
    {
        $sql = 'INSERT INTO su_kien (
                    danh_muc_id, ten, gia, ton_kho, dia_diem, thoi_gian,
                    diem_ren_luyen, dien_gia, anh, mo_ta_ngan, mo_ta_chi_tiet, video
                ) VALUES (
                    :danh_muc_id, :ten, :gia, :ton_kho, :dia_diem, :thoi_gian,
                    :diem_ren_luyen, :dien_gia, :anh, :mo_ta_ngan, :mo_ta_chi_tiet, :video
                )';

        $st = $this->db->prepare($sql);
        $st->execute([
            ':danh_muc_id'   => $d['danh_muc_id'],
            ':ten'           => $d['ten'],
            ':gia'           => $d['gia'],
            ':ton_kho'       => $d['ton_kho'],
            ':dia_diem'      => $d['dia_diem'],
            ':thoi_gian'     => $d['thoi_gian'],
            ':diem_ren_luyen'=> $d['diem_ren_luyen'] ?? 0,
            ':dien_gia'      => $d['dien_gia'] ?? '',
            ':anh'           => $d['anh'] ?? 'images/AI.jpg',
            ':mo_ta_ngan'    => $d['mo_ta_ngan'] ?? '',
            ':mo_ta_chi_tiet'=> $d['mo_ta_chi_tiet'] ?? '',
            ':video'         => $d['video'] ?? '',
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Cập nhật thông tin sự kiện.
     */
    public function capNhat(int $id, array $d): bool
    {
        $sql = 'UPDATE su_kien SET
                    danh_muc_id = :danh_muc_id,
                    ten = :ten,
                    gia = :gia,
                    ton_kho = :ton_kho,
                    dia_diem = :dia_diem,
                    thoi_gian = :thoi_gian,
                    diem_ren_luyen = :diem_ren_luyen,
                    dien_gia = :dien_gia,
                    anh = :anh,
                    mo_ta_ngan = :mo_ta_ngan,
                    mo_ta_chi_tiet = :mo_ta_chi_tiet,
                    video = :video
                WHERE id = :id';

        $st = $this->db->prepare($sql);
        return $st->execute([
            ':id'            => $id,
            ':danh_muc_id'   => $d['danh_muc_id'],
            ':ten'           => $d['ten'],
            ':gia'           => $d['gia'],
            ':ton_kho'       => $d['ton_kho'],
            ':dia_diem'      => $d['dia_diem'],
            ':thoi_gian'     => $d['thoi_gian'],
            ':diem_ren_luyen'=> $d['diem_ren_luyen'] ?? 0,
            ':dien_gia'      => $d['dien_gia'] ?? '',
            ':anh'           => $d['anh'] ?? 'images/AI.jpg',
            ':mo_ta_ngan'    => $d['mo_ta_ngan'] ?? '',
            ':mo_ta_chi_tiet'=> $d['mo_ta_chi_tiet'] ?? '',
            ':video'         => $d['video'] ?? '',
        ]);
    }

    /**
     * Xóa một sự kiện theo ID.
     */
    public function xoa(int $id): bool
    {
        $st = $this->db->prepare('DELETE FROM su_kien WHERE id = ?');
        return $st->execute([$id]);
    }

    /**
     * Tìm kiếm nhanh cho REST API.
     */
    public function timNhanh(string $tuKhoa, int $gioiHan = 10): array
    {
        $tuKhoa = trim($tuKhoa);
        if ($tuKhoa === '') {
            return $this->tatCa($gioiHan);
        }

        $sql = 'SELECT s.*, d.ten AS ten_danh_muc, d.slug AS slug_danh_muc
                FROM su_kien s
                JOIN danh_muc d ON s.danh_muc_id = d.id
                WHERE s.ten LIKE ? OR s.dia_diem LIKE ? OR s.dien_gia LIKE ?
                ORDER BY s.id DESC
                LIMIT ?';

        $ts = '%' . addcslashes($tuKhoa, '%_\\') . '%';
        $st = $this->db->prepare($sql);
        $st->execute([$ts, $ts, $ts, $gioiHan]);

        $ketQua = [];
        while ($dong = $st->fetch()) {
            $ketQua[] = SuKien::tuMang($dong);
        }

        return $ketQua;
    }
}

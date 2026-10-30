<?php
/**
 * src/Data/KhoLienHe.php
 * Tầng truy cập dữ liệu (DAL) cho thực thể Liên hệ & Phản hồi (lien_he).
 * Thay thế việc đọc/ghi tệp storage/lien-he.jsonl bằng bảng lien_he trong MySQL qua PDO.
 */

declare(strict_types=1);

namespace App\Data;

use PDO;

class KhoLienHe
{
    private PDO $db;

    public function __construct(PDO|string|null $db = null)
    {
        if ($db instanceof PDO) {
            $this->db = $db;
        } else {
            $this->db = db();
        }
    }

    /**
     * Thêm một liên hệ mới vào bảng lien_he.
     *
     * @param array{
     *   hoTen: string,
     *   email: string,
     *   sdt?: string,
     *   soDienThoai?: string,
     *   khoaVien?: string,
     *   noiDung: string,
     *   anh?: string|null
     * } $lh Dữ liệu liên hệ đã validate
     */
    public function them(array $lh): void
    {
        $hoTen    = trim((string)($lh['hoTen'] ?? ($lh['ho_ten'] ?? '')));
        $email    = trim((string)($lh['email'] ?? ''));
        $sdt      = trim((string)($lh['soDienThoai'] ?? ($lh['sdt'] ?? ($lh['so_dien_thoai'] ?? ''))));
        $khoaVien = trim((string)($lh['khoaVien'] ?? ($lh['khoa_vien'] ?? '')));
        $noiDung  = trim((string)($lh['noiDung'] ?? ($lh['noi_dung'] ?? '')));
        $anh      = !empty($lh['anh']) ? (string)$lh['anh'] : null;

        $sql = 'INSERT INTO lien_he (ho_ten, email, so_dien_thoai, khoa_vien, noi_dung, anh, ngay_tao)
                VALUES (?, ?, ?, ?, ?, ?, NOW())';

        $st = $this->db->prepare($sql);
        $st->execute([$hoTen, $email, $sdt, $khoaVien, $noiDung, $anh]);
    }

    /**
     * Lấy danh sách liên hệ mới nhất (phục vụ trang quan-tri.php).
     *
     * @param int $gioiHan Số bản ghi tối đa
     * @param int $boQua OFFSET
     * @return array<int, array<string, mixed>> Danh sách phản hồi
     */
    public function tatCa(int $gioiHan = 50, int $boQua = 0): array
    {
        $st = $this->db->prepare(
            'SELECT * FROM lien_he ORDER BY ngay_tao DESC, id DESC LIMIT ? OFFSET ?'
        );
        $st->execute([$gioiHan, $boQua]);
        return $st->fetchAll();
    }

    /**
     * Đếm tổng số lượng phản hồi liên hệ.
     */
    public function dem(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM lien_he')->fetchColumn();
    }
}

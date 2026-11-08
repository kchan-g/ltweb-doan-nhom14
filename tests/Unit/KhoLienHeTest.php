<?php
/**
 * tests/Unit/KhoLienHeTest.php
 * Kiểm thử đơn vị cho tầng truy cập dữ liệu KhoLienHe (App\Data\KhoLienHe)
 * sử dụng SQLite in-memory kết hợp hàm tương thích PDO.
 */

declare(strict_types=1);

namespace Tests\Unit;

use App\Data\KhoLienHe;
use PDO;
use Tests\TestCase;

class KhoLienHeTest extends TestCase
{
    private PDO $db;
    private KhoLienHe $kho;

    public function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Đăng ký hàm NOW() cho SQLite để tương thích với câu lệnh SQL của MySQL
        $this->db->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));

        // Tạo bảng lien_he mô phỏng
        $this->db->exec('
            CREATE TABLE lien_he (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ho_ten TEXT NOT NULL,
                email TEXT NOT NULL,
                so_dien_thoai TEXT NULL,
                khoa_vien TEXT NULL,
                noi_dung TEXT NOT NULL,
                anh TEXT NULL,
                ngay_tao TEXT NOT NULL
            )
        ');

        $this->kho = new KhoLienHe($this->db);
    }

    public function testThemVaLayDanhSachLienHe(): void
    {
        $this->assertEquals(0, $this->kho->dem());

        $this->kho->them([
            'hoTen'       => 'Nguyễn Văn Nam',
            'email'       => 'nam.nguyen@ued.udn.vn',
            'soDienThoai' => '0905123456',
            'khoaVien'    => 'Khoa Toán - Tin học',
            'noiDung'     => 'Em muốn hỏi về quy chế cộng điểm rèn luyện sự kiện...',
            'anh'         => null,
        ]);

        $this->assertEquals(1, $this->kho->dem());

        $ds = $this->kho->tatCa(10);
        $this->assertCount(1, $ds);
        $this->assertEquals('Nguyễn Văn Nam', $ds[0]['ho_ten']);
        $this->assertEquals('Khoa Toán - Tin học', $ds[0]['khoa_vien']);
        $this->assertEquals('0905123456', $ds[0]['so_dien_thoai']);
    }

    public function testTuongThichNhieuKhoaInput(): void
    {
        // Kiểm tra xử lý tương thích cả khóa 'khoa', 'khoa_vien', 'khoaVien'
        $this->kho->them([
            'ho_ten'        => 'Lê Thị Mai',
            'email'         => 'mai.le@ued.udn.vn',
            'sdt'           => '0987654321',
            'khoa'          => 'Khoa Sư phạm Khoa học Tự nhiên',
            'noi_dung'      => 'Đăng ký cộng tác viên sự kiện.',
        ]);

        $this->assertEquals(1, $this->kho->dem());
        $ds = $this->kho->tatCa(10);
        $this->assertEquals('Khoa Sư phạm Khoa học Tự nhiên', $ds[0]['khoa_vien']);
        $this->assertEquals('0987654321', $ds[0]['so_dien_thoai']);
    }
}

<?php
/**
 * src/Data/KhoLienHe.php
 * Lớp lưu trữ và truy xuất các tin nhắn liên hệ từ người dùng.
 * Dữ liệu được lưu dạng JSON Lines (.jsonl) trong thư mục storage/
 * (chuẩn bị để sang Chương 6 chuyển thành bảng lien_he của MySQL).
 */

declare(strict_types=1);

namespace App\Data;

use JsonException;

class KhoLienHe
{
    /**
     * @param string $tep Đường dẫn tới tệp storage/lien-he.jsonl
     */
    public function __construct(private string $tep)
    {
    }

    /**
     * Thêm một liên hệ mới vào tệp JSON Lines kèm khóa tệp an toàn (LOCK_EX).
     *
     * @param array $lh Thông tin liên hệ
     * @throws JsonException Khi không thể mã hóa mảng thành JSON
     */
    public function them(array $lh): void
    {
        $dong = json_encode($lh, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        file_put_contents($this->tep, $dong . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Lấy toàn bộ danh sách liên hệ đã nhận, sắp xếp mới nhất lên đầu.
     *
     * @return array Danh sách các liên hệ
     */
    public function tatCa(): array
    {
        if (!is_file($this->tep)) {
            return [];
        }

        $cacDong = file($this->tep, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($cacDong === false) {
            return [];
        }

        $danhSach = [];
        foreach ($cacDong as $dong) {
            try {
                $item = json_decode($dong, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($item)) {
                    $danhSach[] = $item;
                }
            } catch (JsonException) {
                // Bỏ qua dòng bị lỗi định dạng
                continue;
            }
        }

        return array_reverse($danhSach);
    }
}

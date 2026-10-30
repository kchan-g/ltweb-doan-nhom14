<?php
/**
 * inc/xac-thuc.php
 * Module quản lý xác thực phiên làm việc, phân quyền người dùng và kiểm soát truy cập.
 *
 * Tiêu chuẩn an toàn:
 * - Session chỉ lưu duy nhất $_SESSION['uid'] (User ID), tuyệt đối không lưu vai trò trong Session.
 * - Mọi thông tin người dùng và quyền hạn được truy vấn lại từ CSDL (có bộ đệm tĩnh per-request).
 * - Cung cấp cơ chế phân quyền dựa trên vai trò (RBAC) và bảo vệ trước truy cập trái phép.
 */

declare(strict_types=1);

/**
 * Lấy thông tin tài khoản đang đăng nhập trong phiên làm việc hiện tại.
 *
 * @return array<string, mixed>|null Mảng thông tin người dùng hoặc null nếu chưa đăng nhập
 */
function nguoiDung(): ?array
{
    static $nguoiDungCache = null;
    static $daDoc = false;

    if ($daDoc) {
        return $nguoiDungCache;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $uid = filter_var($_SESSION['uid'] ?? null, FILTER_VALIDATE_INT);
    if (!$uid) {
        $daDoc = true;
        $nguoiDungCache = null;
        return null;
    }

    try {
        $st = db()->prepare('SELECT id, email, ho_ten, vai_tro, so_dien_thoai, ngay_tao FROM nguoi_dung WHERE id = ?');
        $st->execute([$uid]);
        $nd = $st->fetch();

        if ($nd === false) {
            // Tài khoản không còn tồn tại trong CSDL -> Hủy phiên
            unset($_SESSION['uid']);
            $nguoiDungCache = null;
        } else {
            $nguoiDungCache = $nd;
        }
    } catch (Throwable $e) {
        // Ghi log nếu xảy ra lỗi kết nối CSDL khi đọc tài khoản
        error_log('Lỗi đọc tài khoản người dùng: ' . $e->getMessage());
        $nguoiDungCache = null;
    }

    $daDoc = true;
    return $nguoiDungCache;
}

/**
 * Kiểm tra xem người dùng hiện tại đã đăng nhập hay chưa.
 */
function daDangNhap(): bool
{
    return nguoiDung() !== null;
}

/**
 * Lấy ID của người dùng đang đăng nhập hoặc null nếu là khách vãng lai.
 */
function idNguoiDung(): ?int
{
    $nd = nguoiDung();
    return isset($nd['id']) ? (int)$nd['id'] : null;
}

/**
 * Kiểm tra xem người dùng hiện tại có vai trò Quản trị viên (quan_tri) hay không.
 */
function laQuanTri(): bool
{
    $nd = nguoiDung();
    return $nd !== null && ($nd['vai_tro'] ?? '') === 'quan_tri';
}

/**
 * Yêu cầu người dùng phải đăng nhập trước khi tiếp tục thao tác.
 * Nếu chưa đăng nhập, tự động chuyển hướng về trang dang-nhap.php.
 */
function yeuCauDangNhap(): void
{
    if (!daDangNhap()) {
        $_SESSION['flash'] = 'Vui lòng đăng nhập để tiếp tục thao tác này.';
        header('Location: dang-nhap.php');
        exit;
    }
}

/**
 * Yêu cầu người dùng phải có đúng vai trò chỉ định để truy cập.
 * - Nếu chưa đăng nhập -> Chuyển hướng tới dang-nhap.php.
 * - Nếu đã đăng nhập nhưng không đủ quyền -> Trả về mã HTTP 403 Forbidden và hiển thị 403.php.
 *
 * @param string $vaiTro Vai trò yêu cầu (ví dụ: 'quan_tri')
 */
function yeuCauQuyen(string $vaiTro): void
{
    if (!daDangNhap()) {
        $_SESSION['flash'] = 'Bạn cần đăng nhập tài khoản quản trị để truy cập trang này.';
        header('Location: dang-nhap.php');
        exit;
    }

    $nd = nguoiDung();
    if (($nd['vai_tro'] ?? '') !== $vaiTro) {
        http_response_code(403);
        if (file_exists(__DIR__ . '/../403.php')) {
            require __DIR__ . '/../403.php';
        } else {
            echo '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><title>403 Forbidden</title></head><body><h1>403 - Bạn không có quyền truy cập trang này</h1></body></html>';
        }
        exit;
    }
}

#!/usr/bin/env bash
# ============================================================================
# tests/bao-mat.sh
# Kịch bản kiểm thử bảo mật tự động bằng cURL (Chương 7 - Bảng 3)
# Chạy trong Git Bash hoặc Linux: bash tests/bao-mat.sh [URL_GOC]
# Mặc định: URL_GOC=http://localhost:8000
# ============================================================================

GOC="${1:-http://localhost:8000}"

echo "========================================================================"
echo "   CHẠY KIỂM THỬ BẢO MẬT WEBSITE UNIEVENT - ĐH SƯ PHẠM ĐÀ NẴNG (UED)    "
echo "   Mục tiêu kiểm thử: $GOC                                              "
echo "========================================================================"

# CA 1: Kiểm tra Header bảo mật
echo -n "[Ca 1] Header bảo mật: "
HEADER_RES=$(curl -s -I "$GOC/index.php")
if echo "$HEADER_RES" | grep -qi "X-Content-Type-Options: nosniff" && \
   echo "$HEADER_RES" | grep -qi "Content-Security-Policy" && \
   ! echo "$HEADER_RES" | grep -qi "X-Powered-By"; then
    echo "✓ ĐẠT (Có CSP, nosniff, đã ẩn X-Powered-By)"
else
    echo "✗ CHƯA ĐẠT"
fi

# CA 2: POST thiếu mã CSRF -> Kỳ vọng 403
echo -n "[Ca 2] POST thiếu mã CSRF: "
STATUS_CA2=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$GOC/dang-nhap.php" -d "email=test@ued.vn&password=123")
if [ "$STATUS_CA2" -eq 403 ]; then
    echo "✓ ĐẠT (Trả về mã HTTP 403 Forbidden)"
else
    echo "✗ CHƯA ĐẠT (Nhận mã HTTP $STATUS_CA2)"
fi

# CA 3: API giỏ hàng thiếu X-CSRF-Token -> Kỳ vọng 403 JSON
echo -n "[Ca 3] API thiếu X-CSRF-Token: "
STATUS_CA3=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$GOC/api/gio-hang.php" \
    -H "Content-Type: application/json" -H "Accept: application/json" -d '{"id":1,"sl":1}')
if [ "$STATUS_CA3" -eq 403 ]; then
    echo "✓ ĐẠT (Trả về mã HTTP 403 Forbidden)"
else
    echo "✗ CHƯA ĐẠT (Nhận mã HTTP $STATUS_CA3)"
fi

# CA 4: GET trên xoa-san-pham.php và dang-xuat.php -> Kỳ vọng 405
echo -n "[Ca 4] GET trên xoa-san-pham.php: "
STATUS_CA4=$(curl -s -o /dev/null -w "%{http_code}" "$GOC/xoa-san-pham.php?id=1")
if [ "$STATUS_CA4" -eq 405 ]; then
    echo "✓ ĐẠT (Trả về mã HTTP 405 Method Not Allowed)"
else
    echo "✗ CHƯA ĐẠT (Nhận mã HTTP $STATUS_CA4)"
fi

echo -n "[Ca 4b] GET trên dang-xuat.php: "
STATUS_CA4B=$(curl -s -o /dev/null -w "%{http_code}" "$GOC/dang-xuat.php")
if [ "$STATUS_CA4B" -eq 405 ]; then
    echo "✓ ĐẠT (Trả về mã HTTP 405 Method Not Allowed)"
else
    echo "✗ CHƯA ĐẠT (Nhận mã HTTP $STATUS_CA4B)"
fi

# CA 8: Tham số dạng mảng (?q[]=ao) không gây 500 hay TypeError
echo -n "[Ca 8] Tham số dạng mảng: "
STATUS_CA8=$(curl -s -o /dev/null -w "%{http_code}" -g "$GOC/danh-sach.php?q[]=AI&id[]=1")
if [ "$STATUS_CA8" -eq 200 ]; then
    echo "✓ ĐẠT (Xử lý an toàn, không phát sinh lỗi 500 hay Warning)"
else
    echo "✗ CHƯA ĐẠT (Nhận mã HTTP $STATUS_CA8)"
fi

# CA 13 (Đề tài UniEvent): Khách chưa đăng nhập gọi POST đặt vé trực tiếp -> 302 về dang-nhap.php
echo -n "[Ca 13] Đặt vé khi chưa đăng nhập: "
STATUS_CA13=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$GOC/dat-hang.php")
if [ "$STATUS_CA13" -eq 403 ] || [ "$STATUS_CA13" -eq 302 ]; then
    echo "✓ ĐẠT (Bị chặn an toàn: $STATUS_CA13)"
else
    echo "✗ CHƯA ĐẠT (Nhận mã HTTP $STATUS_CA13)"
fi

echo "========================================================================"
echo "   HOÀN THÀNH KỊCH BẢN KIỂM THỬ BẢO MẬT                              "
echo "========================================================================"

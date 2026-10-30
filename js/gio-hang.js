/**
 * js/gio-hang.js
 * Thêm vé vào giỏ hàng bằng AJAX/Fetch qua REST API (api/gio-hang.php):
 * - Bắt sự kiện submit của biểu mẫu thêm vé (ở chi-tiet.php và danh-sach.php).
 * - Gửi dữ liệu JSON lên api/gio-hang.php mà không tải lại toàn trang.
 * - Cập nhật ngay số món hiển thị trên Header (#dem-gio-hang).
 * - Hiển thị thông báo Toast phản hồi tức thời cho người dùng.
 * - Khi tắt JavaScript: Form submit thông thường về gio-hang.php vẫn chạy 100%.
 */

document.addEventListener('DOMContentLoaded', () => {
  // Lắng nghe submit trên toàn bộ biểu mẫu thêm vé
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    
    // Kiểm tra xem form có phải là form thêm giỏ hàng hay không
    if (!form || (form.id !== 'form-them-gio' && !form.classList.contains('form-them-gio-nhanh'))) {
      return;
    }

    const inputId = form.querySelector('input[name="id"]');
    const inputSl = form.querySelector('input[name="so_luong"]') || { value: 1 };

    if (!inputId) {
      return;
    }

    // Ngăn chặn tải lại trang của form submit truyền thống
    e.preventDefault();

    const id = parseInt(inputId.value, 10);
    const sl = parseInt(inputSl.value, 10) || 1;

    const btnSubmit = form.querySelector('button[type="submit"]');
    const textCu = btnSubmit ? btnSubmit.textContent : '';
    if (btnSubmit) {
      btnSubmit.disabled = true;
      btnSubmit.textContent = '⏳ Đang thêm...';
    }

    try {
      const res = await fetch('api/gio-hang.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ id: id, sl: sl })
      });

      const data = await res.json();

      if (res.ok && data.thanh_cong) {
        // Cập nhật số món trên Header
        capNhatSoLuongHeader(data.so_mon);
        hienThiThongBaoToast(data.thong_bao || 'Đã thêm vé vào giỏ thành công!', 'thanh-cong');
      } else {
        hienThiThongBaoToast(data.loi || 'Không thể thêm vé vào giỏ.', 'loi');
      }
    } catch (err) {
      console.error('Lỗi khi gọi API giỏ hàng:', err);
      // Fallback: nếu lỗi mạng, submit form theo cách thông thường
      form.submit();
    } finally {
      if (btnSubmit) {
        btnSubmit.disabled = false;
        btnSubmit.textContent = textCu;
      }
    }
  });

  /**
   * Cập nhật huy hiệu số lượng trên Header
   */
  function capNhatSoLuongHeader(soLuong) {
    let badge = document.getElementById('dem-gio-hang');
    if (!badge && soLuong > 0) {
      const link = document.querySelector('a[href*="gio-hang.php"]');
      if (link) {
        badge = document.createElement('span');
        badge.className = 'huy-hieu-dem';
        badge.id = 'dem-gio-hang';
        badge.style.background = '#f59e0b';
        badge.style.color = '#ffffff';
        link.appendChild(badge);
      }
    }

    if (badge) {
      badge.textContent = soLuong;
      badge.style.display = soLuong > 0 ? 'inline-block' : 'none';
    }
  }

  /**
   * Hiển thị thông báo Toast nổi nhẹ nhàng
   */
  function hienThiThongBaoToast(noiDung, loai = 'thanh-cong') {
    let toast = document.getElementById('toast-thong-bao-api');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'toast-thong-bao-api';
      toast.style.position = 'fixed';
      toast.style.bottom = '24px';
      toast.style.right = '24px';
      toast.style.padding = '12px 20px';
      toast.style.borderRadius = '8px';
      toast.style.fontSize = '0.9rem';
      toast.style.fontWeight = '600';
      toast.style.zIndex = '99999';
      toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
      toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
      document.body.appendChild(toast);
    }

    if (loai === 'thanh-cong') {
      toast.style.background = '#065f46';
      toast.style.color = '#ffffff';
      toast.textContent = '✓ ' + noiDung;
    } else {
      toast.style.background = '#991b1b';
      toast.style.color = '#ffffff';
      toast.textContent = '⚠️ ' + noiDung;
    }

    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';

    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
    }, 3500);
  }
});

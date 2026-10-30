/**
 * js/tim-kiem.js
 * Tìm kiếm tức thời (Instant Search) cho trang danh-sach.php sử dụng REST API:
 * - Lắng nghe sự kiện gõ phím trên ô tìm kiếm.
 * - Áp dụng kỹ thuật Debounce (300ms) để gom các lần gõ phím, tránh spam request lên server.
 * - Gọi fetch('api/san-pham.php?q=...') để lấy dữ liệu JSON.
 * - Cập nhật giao diện an toàn 100% bằng textContent (chống XSS DOM-based).
 * - Hoàn toàn không ảnh hưởng khi tắt JavaScript (Progressive Enhancement).
 */

document.addEventListener('DOMContentLoaded', () => {
  const oTimKiem = document.getElementById('tim-kiem-danh-sach');
  const khungDanhSach = document.getElementById('khung-danh-sach-su-kien');

  if (!oTimKiem || !khungDanhSach) {
    return;
  }

  let timeoutId = null;

  oTimKiem.addEventListener('input', (e) => {
    const tuKhoa = e.target.value.trim();

    // Hủy bỏ lần chờ trước (Debounce 300ms)
    clearTimeout(timeoutId);

    timeoutId = setTimeout(async () => {
      try {
        const url = 'api/san-pham.php?q=' + encodeURIComponent(tuKhoa);
        const res = await fetch(url, {
          headers: {
            'Accept': 'application/json'
          }
        });

        if (!res.ok) {
          return;
        }

        const data = await res.json();
        const danhSach = data.du_lieu || [];

        const vungPhanTrang = document.querySelector('.vung-phan-trang');
        const moTaKetQua = document.querySelector('.section-head__mota');
        if (moTaKetQua && !moTaKetQua.dataset.originalHtml) {
          moTaKetQua.dataset.originalHtml = moTaKetQua.innerHTML;
        }

        if (tuKhoa !== '') {
          if (vungPhanTrang) vungPhanTrang.style.display = 'none';
          if (moTaKetQua) {
            moTaKetQua.innerHTML = `Tìm kiếm tức thời: tìm thấy <strong>${danhSach.length}</strong> sự kiện phù hợp với từ khóa "<strong>${tuKhoa.replace(/</g, '&lt;')}</strong>"`;
          }
        } else {
          if (vungPhanTrang) vungPhanTrang.style.display = '';
          if (moTaKetQua && moTaKetQua.dataset.originalHtml) {
            moTaKetQua.innerHTML = moTaKetQua.dataset.originalHtml;
          }
        }

        renderDanhSach(danhSach);
      } catch (err) {
        console.error('Lỗi tìm kiếm API:', err);
      }
    }, 300);
  });

  /**
   * Tạo DOM an toàn bằng các phương thức chuẩn (createElement, textContent)
   */
  function renderDanhSach(danhSach) {
    khungDanhSach.innerHTML = '';

    if (danhSach.length === 0) {
      const liRong = document.createElement('li');
      liRong.style.gridColumn = '1 / -1';
      liRong.style.maxWidth = '100%';
      liRong.innerHTML = `
        <div style="text-align:center; padding:40px; background:var(--nen-surface); border-radius:8px; border:1px dashed var(--border-nhat);">
          <div style="font-size:2.5rem; margin-bottom:10px;">🔎</div>
          <h3 style="color:var(--mau-chinh-toi); font-size:1.15rem;">Không tìm thấy sự kiện phù hợp qua API</h3>
          <p class="text-phu" style="font-size:0.875rem;">Hãy thử gõ từ khóa khác ngắn gọn hơn.</p>
        </div>
      `;
      khungDanhSach.appendChild(liRong);
      return;
    }

    danhSach.forEach((sk) => {
      const li = document.createElement('li');

      const article = document.createElement('article');
      article.className = 'the-tin';

      // Media
      const mediaDiv = document.createElement('div');
      mediaDiv.className = 'the-tin__media';

      const badge = document.createElement('span');
      badge.className = 'badge';
      badge.textContent = sk.ten_danh_muc || 'Sự kiện';

      const img = document.createElement('img');
      img.src = sk.anh || 'images/AI.jpg';
      img.alt = sk.ten || '';
      img.loading = 'lazy';
      img.width = 360;
      img.height = 225;

      mediaDiv.appendChild(badge);
      mediaDiv.appendChild(img);

      // Body
      const bodyDiv = document.createElement('div');
      bodyDiv.className = 'the-tin__body';

      const h3 = document.createElement('h3');
      h3.textContent = sk.ten || '';

      const pTime = document.createElement('p');
      pTime.className = 'event-meta';
      pTime.textContent = 'Thời gian: ' + (sk.thoi_gian || '');

      const pDiaDiem = document.createElement('p');
      pDiaDiem.className = 'event-meta';
      pDiaDiem.textContent = 'Địa điểm: ' + (sk.dia_diem || '');

      const pGia = document.createElement('p');
      pGia.className = 'event-meta';
      const giaFormatted = (sk.gia > 0) ? new Intl.NumberFormat('vi-VN').format(sk.gia) + ' đ' : 'Miễn phí';
      pGia.innerHTML = 'Giá vé: <strong>' + giaFormatted + '</strong> - Còn lại: ' + sk.ton_kho + ' vé';

      // Actions
      const actionsDiv = document.createElement('div');
      actionsDiv.className = 'the-tin__actions mt-3';

      const aDetail = document.createElement('a');
      aDetail.href = 'chi-tiet.php?id=' + sk.id;
      aDetail.className = 'nut-bam nut-nhan nut-nho';
      aDetail.textContent = 'Xem chi tiết';

      actionsDiv.appendChild(aDetail);

      if (sk.ton_kho <= 0) {
        const spanHet = document.createElement('span');
        spanHet.className = 'nut-bam nut-phu nut-nho';
        spanHet.style.color = '#991b1b';
        spanHet.style.background = '#fee2e2';
        spanHet.textContent = 'Hết vé';
        actionsDiv.appendChild(spanHet);
      } else {
        const formThem = document.createElement('form');
        formThem.action = 'gio-hang.php';
        formThem.method = 'POST';
        formThem.style.margin = '0';
        formThem.style.display = 'inline';
        formThem.innerHTML = `
          <input type="hidden" name="hanh_dong" value="them">
          <input type="hidden" name="id" value="${sk.id}">
          <input type="hidden" name="so_luong" value="1">
          <button type="submit" class="nut-bam nut-nho" title="Đăng ký vé">+ Đăng ký</button>
        `;
        actionsDiv.appendChild(formThem);
      }

      bodyDiv.appendChild(h3);
      bodyDiv.appendChild(pTime);
      bodyDiv.appendChild(pDiaDiem);
      bodyDiv.appendChild(pGia);
      bodyDiv.appendChild(actionsDiv);

      article.appendChild(mediaDiv);
      article.appendChild(bodyDiv);
      li.appendChild(article);

      khungDanhSach.appendChild(li);
    });
  }
});

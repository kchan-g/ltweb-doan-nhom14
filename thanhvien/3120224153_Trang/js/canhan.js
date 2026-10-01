/**
 * canhan.js — Tương tác cho trang cá nhân Nguyễn Thị Kiều Trang (MSSV: 3120224153)
 * Mục đích: 
 *   1. Lọc danh sách dự án tiêu biểu theo tag chuyên môn (Web, IoT, AI).
 *   2. Nút Back-to-top tự động ẩn/hiện và cuộn trang mượt mà lên đầu.
 * Cách thử:
 *   - Nhấp vào các nút tag Web Dev, IoT, AI hoặc bấm Tab + Enter/Space để lọc.
 *   - Cuộn trang xuống quá 250px để thấy nút mũi tên xuất hiện góc dưới phải, nhấp để cuộn lên.
 */

document.addEventListener('DOMContentLoaded', () => {
  // ========================================================
  // TƯƠNG TÁC 1: BỘ LỌC DỰ ÁN THEO TAG CHUYÊN MÔN
  // ========================================================
  const cacNutTag = document.querySelectorAll('.btn-tag');
  const danhSachDuAn = document.querySelectorAll('.project-item');
  const filterStatus = document.querySelector('#filter-status');

  if (cacNutTag.length > 0 && danhSachDuAn.length > 0) {
    cacNutTag.forEach((nut) => {
      nut.addEventListener('click', () => {
        // Cập nhật trạng thái active và thuộc tính aria-pressed
        cacNutTag.forEach((btn) => {
          btn.classList.remove('active');
          btn.setAttribute('aria-pressed', 'false');
        });
        nut.classList.add('active');
        nut.setAttribute('aria-pressed', 'true');

        const tagDuocChon = nut.dataset.tag;
        let soLuongHienThi = 0;

        // Ẩn/hiện thẻ dự án phù hợp
        danhSachDuAn.forEach((the) => {
          const category = the.dataset.category;
          if (tagDuocChon === 'all' || tagDuocChon === category) {
            the.classList.remove('an-du-an');
            soLuongHienThi++;
          } else {
            the.classList.add('an-du-an');
          }
        });

        // Thông báo bằng textContent cho trình đọc màn hình qua aria-live
        if (filterStatus) {
          filterStatus.textContent = `Đang hiển thị ${soLuongHienThi} dự án theo danh mục ${nut.textContent.trim()}`;
        }
      });
    });
  }

  // ========================================================
  // TƯƠNG TÁC 2: NÚT BACK-TO-TOP CUỘN MƯỢT MÀ CÓ ẨN / HIỆN
  // ========================================================
  const nutBackToTop = document.querySelector('#btn-back-to-top');

  if (nutBackToTop) {
    // Ẩn/hiện nút tùy theo vị trí cuộn trang (ngưỡng 250px)
    window.addEventListener('scroll', () => {
      if (window.scrollY > 250) {
        nutBackToTop.classList.add('hien');
      } else {
        nutBackToTop.classList.remove('hien');
      }
    });

    // Cuộn mượt mà lên đầu trang khi bấm
    nutBackToTop.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }
});
/**
 * @file yeu-thich.js
 * @description Quản lý danh sách sự kiện yêu thích (lưu tạm) bằng localStorage.
 * Đồng bộ số đếm trên thanh điều hướng header ở mọi trang và phát sự kiện cập nhật.
 */

const KHOA_LUU_TRU = "unievent_yeu_thich";

/**
 * Lấy danh sách ID các sự kiện đã lưu từ localStorage.
 * @returns {number[]} Mảng chứa ID các sự kiện đã thích
 */
export function layDanhSachYeuThich() {
  try {
    const raw = localStorage.getItem(KHOA_LUU_TRU);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed.map((item) => Number(item)) : [];
  } catch (error) {
    console.warn("Lỗi đọc dữ liệu yêu thích từ localStorage:", error);
    return [];
  }
}

/**
 * Lưu mảng ID sự kiện vào localStorage và phát sự kiện tùy biến.
 * @param {number[]} danhSach - Mảng ID cần lưu
 */
export function luuDanhSachYeuThich(danhSach) {
  try {
    localStorage.setItem(KHOA_LUU_TRU, JSON.stringify(danhSach));
  } catch (error) {
    console.error("Không thể ghi dữ liệu vào localStorage:", error);
  }

  // Phát sự kiện tùy biến để các trang hoặc thành phần khác lắng nghe đồng bộ
  window.dispatchEvent(
    new CustomEvent("yeuThichThayDoi", {
      detail: { danhSach, soLuong: danhSach.length },
    }),
  );

  capNhatSoDemHeader();
}

/**
 * Kiểm tra xem một sự kiện đã được lưu hay chưa.
 * @param {number} id - ID sự kiện
 * @returns {boolean} True nếu đã lưu, ngược lại False
 */
export function kiemTraYeuThich(id) {
  const ds = layDanhSachYeuThich();
  return ds.includes(Number(id));
}

/**
 * Đảo trạng thái yêu thích của sự kiện (thêm nếu chưa có, xóa nếu đã có).
 * @param {number} id - ID sự kiện
 * @returns {boolean} True nếu vừa được thêm, False nếu vừa bị xóa
 */
export function toggleYeuThich(id) {
  const maSo = Number(id);
  const ds = layDanhSachYeuThich();
  const viTri = ds.indexOf(maSo);
  let daThem = false;

  if (viTri >= 0) {
    ds.splice(viTri, 1);
    daThem = false;
  } else {
    ds.push(maSo);
    daThem = true;
  }

  luuDanhSachYeuThich(ds);
  return daThem;
}

/**
 * Cập nhật số đếm sự kiện yêu thích hiển thị trên header.
 */
export function capNhatSoDemHeader() {
  const phanTuDem = document.getElementById("dem-yeu-thich");
  if (phanTuDem) {
    const ds = layDanhSachYeuThich();
    phanTuDem.textContent = String(ds.length);
  }
}

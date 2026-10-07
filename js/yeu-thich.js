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

/**
 * Đồng bộ trạng thái hiển thị của tất cả nút .nut-yeu-thich trên trang theo localStorage.
 */
export function dongBoNutYeuThich() {
  const cacNut = document.querySelectorAll(".nut-yeu-thich");
  const ds = layDanhSachYeuThich();
  cacNut.forEach((nut) => {
    const id = Number(nut.dataset.id);
    if (!id) return;
    const daLuu = ds.includes(id);
    nut.classList.toggle("da-thich", daLuu);
    nut.setAttribute("aria-pressed", String(daLuu));
    nut.textContent = daLuu ? "♥ Đã lưu" : "♡ Lưu tin";
    nut.setAttribute(
      "title",
      daLuu ? "Bỏ lưu sự kiện này" : "Lưu sự kiện vào danh sách yêu thích",
    );
  });
}

/**
 * Lọc danh sách hiển thị sự kiện trên trang danh-sach.php nếu URL chứa hash #yeu-thich.
 */
export function locDanhSachYeuThich() {
  const dsContainer = document.querySelector(".danh-sach-su-kien");
  if (!dsContainer) return;

  const laYeuThich = window.location.hash === "#yeu-thich";
  let thongBao = document.getElementById("thong-bao-loc-yeu-thich");

  if (laYeuThich) {
    const ds = layDanhSachYeuThich();
    if (!thongBao) {
      thongBao = document.createElement("div");
      thongBao.id = "thong-bao-loc-yeu-thich";
      thongBao.className = "hop-thong-bao mb-4";
      thongBao.style.cssText =
        "background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: var(--radius-vua, 8px); display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;";
      dsContainer.parentNode.insertBefore(thongBao, dsContainer);
    }
    thongBao.style.display = "flex";
    thongBao.innerHTML = `
      <span>📌 Đang hiển thị <strong>${ds.length}</strong> sự kiện bạn đã lưu</span>
      <a href="danh-sach.php" class="nut-bam nut-phu nut-nho" style="background: #ffffff; text-decoration: none;">Xem tất cả sự kiện</a>
    `;

    const cacMuc = dsContainer.querySelectorAll("li");
    let demHienThi = 0;
    cacMuc.forEach((muc) => {
      const nut = muc.querySelector(".nut-yeu-thich");
      const id = nut ? Number(nut.dataset.id) : null;
      if (id && ds.includes(id)) {
        muc.style.display = "";
        demHienThi++;
      } else {
        muc.style.display = "none";
      }
    });

    let hopRong = document.getElementById("yeu-thich-rong");
    if (demHienThi === 0) {
      if (!hopRong) {
        hopRong = document.createElement("div");
        hopRong.id = "yeu-thich-rong";
        hopRong.className = "p-5 text-center my-4";
        hopRong.style.cssText =
          "background:var(--nen-surface); border-radius:var(--radius-vua); border:1px dashed var(--border-nhat);";
        hopRong.innerHTML = `
          <div style="font-size:3rem; margin-bottom:1rem;">❤️</div>
          <h3 class="h4" style="color:var(--mau-chinh-toi);">Bạn chưa lưu sự kiện nào</h3>
          <p class="text-phu mb-3" style="max-width:500px; margin:0 auto;">
            Hãy nhấn nút "Lưu tin" ở các sự kiện bạn quan tâm để lưu lại và xem nhanh bất kỳ lúc nào!
          </p>
          <a href="danh-sach.php" class="nut-bam nut-chinh">Khám phá danh sách sự kiện</a>
        `;
        dsContainer.parentNode.insertBefore(hopRong, dsContainer.nextSibling);
      }
      hopRong.style.display = "block";
    } else if (hopRong) {
      hopRong.style.display = "none";
    }
  } else {
    if (thongBao) thongBao.style.display = "none";
    const hopRong = document.getElementById("yeu-thich-rong");
    if (hopRong) hopRong.style.display = "none";
    const cacMuc = dsContainer.querySelectorAll("li");
    cacMuc.forEach((muc) => {
      muc.style.display = "";
    });
  }
}

/**
 * Khởi tạo toàn bộ tương tác Lưu tin (Event Delegation & Đồng bộ trạng thái).
 */
export function khoiTaoYeuThich() {
  capNhatSoDemHeader();
  dongBoNutYeuThich();
  locDanhSachYeuThich();

  // Bắt sự kiện click toàn trang cho bất kỳ nút .nut-yeu-thich nào
  document.addEventListener("click", (suKien) => {
    const nut = suKien.target.closest(".nut-yeu-thich");
    if (!nut) return;

    suKien.preventDefault();
    suKien.stopPropagation();

    const id = Number(nut.dataset.id);
    if (!id) return;

    toggleYeuThich(id);
    dongBoNutYeuThich();

    if (window.location.hash === "#yeu-thich") {
      locDanhSachYeuThich();
    }
  });

  // Lắng nghe hash thay đổi khi người dùng bấm vào liên kết #yeu-thich từ header
  window.addEventListener("hashchange", () => {
    locDanhSachYeuThich();
  });
}

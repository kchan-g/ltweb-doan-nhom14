/**
 * @file yeu-thich.js
 * @description Quản lý danh sách sự kiện yêu thích (lưu tin) phân biệt độc lập theo từng tài khoản.
 * Khách vãng lai dùng kho riêng ('khach'), mỗi người dùng đăng nhập có kho riêng ('user_<id>').
 * Đồng bộ số đếm trên thanh điều hướng header ở mọi trang và phát sự kiện cập nhật.
 */

const TIEN_TO_KHOA = "unievent_yeu_thich";

/**
 * Xác định khóa lưu trữ cục bộ tương ứng với tài khoản đang đăng nhập hoặc khách vãng lai.
 * @returns {string} Khóa localStorage riêng biệt của người dùng
 */
export function layKhoaLuuTru() {
  const userId = document.body ? document.body.dataset.userId : "";
  if (userId && String(userId).trim() !== "") {
    return `${TIEN_TO_KHOA}_user_${String(userId).trim()}`;
  }
  return `${TIEN_TO_KHOA}_khach`;
}

/**
 * Lấy danh sách ID các sự kiện đã lưu của tài khoản hiện tại từ localStorage.
 * @returns {number[]} Mảng chứa ID các sự kiện đã lưu
 */
export function layDanhSachYeuThich() {
  try {
    const khoa = layKhoaLuuTru();
    const raw = localStorage.getItem(khoa);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed.map((item) => Number(item)).filter((id) => id > 0) : [];
  } catch (error) {
    console.warn("Lỗi đọc dữ liệu yêu thích từ localStorage:", error);
    return [];
  }
}

/**
 * Lưu mảng ID sự kiện của tài khoản hiện tại vào localStorage và phát sự kiện tùy biến.
 * @param {number[]} danhSach - Mảng ID cần lưu
 */
export function luuDanhSachYeuThich(danhSach) {
  const khoa = layKhoaLuuTru();
  try {
    localStorage.setItem(khoa, JSON.stringify(danhSach));
  } catch (error) {
    console.error("Không thể ghi dữ liệu vào localStorage:", error);
  }

  window.dispatchEvent(
    new CustomEvent("yeuThichThayDoi", {
      detail: { danhSach, soLuong: danhSach.length, khoa },
    }),
  );

  capNhatSoDemHeader();
}

/**
 * Kiểm tra xem một sự kiện đã được lưu bởi tài khoản hiện tại hay chưa.
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
  if (!maSo) return false;

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
 * Cập nhật số đếm sự kiện yêu thích hiển thị trên header theo tài khoản hiện tại.
 */
export function capNhatSoDemHeader() {
  const phanTuDem = document.getElementById("dem-yeu-thich");
  if (phanTuDem) {
    const ds = layDanhSachYeuThich();
    phanTuDem.textContent = String(ds.length);
  }
}

/**
 * Đồng bộ trạng thái hiển thị của tất cả nút .nut-yeu-thich trên trang theo tài khoản hiện tại.
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
    nut.textContent = daLuu ? "♥ Đã lưu" : "♡ Lưu";
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
  const vungPhanTrang = document.querySelector(".vung-phan-trang");
  const tieuDeMuc = document.getElementById("tieu-de-tat-ca-su-kien");
  const moTaMuc = document.querySelector(".section-head__mota");
  const boLoc = document.querySelector(".bo-loc");

  if (moTaMuc && !moTaMuc.dataset.originalHtml) {
    moTaMuc.dataset.originalHtml = moTaMuc.innerHTML;
  }
  if (tieuDeMuc && !tieuDeMuc.dataset.originalText) {
    tieuDeMuc.dataset.originalText = tieuDeMuc.textContent.trim();
  }

  if (laYeuThich) {
    const ds = layDanhSachYeuThich();

    if (tieuDeMuc) {
      tieuDeMuc.textContent = "Sự Kiện Bạn Đã Lưu";
    }
    if (moTaMuc) {
      moTaMuc.innerHTML = `Đang lọc theo danh sách yêu thích &bull; Tổng cộng <strong>${ds.length}</strong> sự kiện đã lưu`;
    }
    if (boLoc) {
      boLoc.style.display = "none";
    }

    if (!thongBao) {
      thongBao = document.createElement("div");
      thongBao.id = "thong-bao-loc-yeu-thich";
      thongBao.className = "hop-thong-bao mb-4";
      thongBao.style.cssText =
        "background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 18px; border-radius: var(--radius-vua, 8px); display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 24px;";
      dsContainer.parentNode.insertBefore(thongBao, dsContainer);
    }
    thongBao.style.display = "flex";
    thongBao.innerHTML = `
      <span>📌 Đang hiển thị danh sách <strong>${ds.length}</strong> sự kiện bạn đã đánh dấu yêu thích</span>
      <a href="danh-sach.php" class="nut-bam nut-phu nut-nho" style="background: #ffffff; text-decoration: none;">Xem tất cả sự kiện</a>
    `;

    if (vungPhanTrang) {
      vungPhanTrang.style.display = "none";
    }

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
      dsContainer.style.display = "none";
      if (!hopRong) {
        hopRong = document.createElement("div");
        hopRong.id = "yeu-thich-rong";
        hopRong.className = "p-5 text-center my-3";
        hopRong.style.cssText =
          "background:var(--nen-surface, #ffffff); border-radius:var(--radius-vua, 12px); border:1px dashed var(--border-dam, #cbd5e1); box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05)); padding: 48px 24px;";
        hopRong.innerHTML = `
          <div style="font-size:3rem; margin-bottom:1rem;">❤️</div>
          <h3 class="h4" style="color:var(--mau-chinh-toi, #1e293b); font-weight: 700; margin-bottom: 0.5rem;">Bạn chưa lưu sự kiện nào</h3>
          <p class="text-phu mb-4" style="max-width:500px; margin:0 auto 1.5rem auto; color: #64748b; line-height: 1.6;">
            Hãy nhấn vào biểu tượng trái tim ở các sự kiện bạn quan tâm để lưu lại và theo dõi lịch trình dễ dàng nhất!
          </p>
          <a href="danh-sach.php" class="nut-bam nut-chinh" style="padding: 10px 24px; text-decoration: none; border-radius: 6px; font-weight: 600;">Khám phá toàn bộ sự kiện</a>
        `;
        dsContainer.parentNode.insertBefore(hopRong, dsContainer.nextSibling);
      }
      hopRong.style.display = "block";
    } else {
      dsContainer.style.display = "grid";
      if (hopRong) {
        hopRong.style.display = "none";
      }
    }
  } else {
    if (tieuDeMuc && tieuDeMuc.dataset.originalText) {
      tieuDeMuc.textContent = tieuDeMuc.dataset.originalText;
    }
    if (moTaMuc && moTaMuc.dataset.originalHtml) {
      moTaMuc.innerHTML = moTaMuc.dataset.originalHtml;
    }
    if (boLoc) {
      boLoc.style.display = "";
    }
    if (thongBao) {
      thongBao.style.display = "none";
    }
    const hopRong = document.getElementById("yeu-thich-rong");
    if (hopRong) {
      hopRong.style.display = "none";
    }
    dsContainer.style.display = "grid";
    if (vungPhanTrang) {
      vungPhanTrang.style.display = "";
    }
    const cacMuc = dsContainer.querySelectorAll("li");
    cacMuc.forEach((muc) => {
      muc.style.display = "";
    });
  }
}

export function khoiTaoYeuThich() {
  capNhatSoDemHeader();
  dongBoNutYeuThich();
  locDanhSachYeuThich();

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

  window.addEventListener("hashchange", () => {
    locDanhSachYeuThich();
  });
}

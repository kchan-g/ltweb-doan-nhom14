/**
 * @file trang-chu.js
 * @description Tải và hiển thị khối dữ liệu thời tiết Đà Nẵng từ REST API công khai Open-Meteo
 * cho trang chủ index.html. Xử lý đầy đủ 3 trạng thái (đang tải, lỗi kèm thử lại, thành công).
 */

import { taiJSON } from "./api.js";

// Vùng gắn khối widget thời tiết trên trang chủ
const khungThoiTiet = document.getElementById("khoi-thoi-tiet-widget");

/**
 * Diễn giải mã thời tiết WMO (Weather Code) của Open-Meteo sang tiếng Việt và biểu tượng.
 * @param {number} code - Mã thời tiết WMO
 * @returns {{ moTa: string, icon: string, loiKhuyen: string }}
 */
function giaiMaThoiTiet(code) {
  if (code === 0) {
    return {
      moTa: "Trời quang đãng, nắng ấm",
      icon: "☀️",
      loiKhuyen:
        "Thời tiết lý tưởng cho các hoạt động ngoại khóa và sự kiện thể thao ngoài trời!",
    };
  } else if (code >= 1 && code <= 3) {
    return {
      moTa: "Trời có mây nhẹ, râm mát",
      icon: "⛅",
      loiKhuyen:
        "Không khí mát mẻ, rất thuận lợi cho việc tham gia các hội thảo và gian hàng sinh viên.",
    };
  } else if (code === 45 || code === 48) {
    return {
      moTa: "Có sương mù nhẹ",
      icon: "🌫️",
      loiKhuyen:
        "Nhiệt độ dễ chịu, lưu ý tầm nhìn khi di chuyển đến khuôn viên trường.",
    };
  } else if (code >= 51 && code <= 67) {
    return {
      moTa: "Mưa phùn / Mưa rào nhẹ",
      icon: "🌧️",
      loiKhuyen:
        "Nên mang theo ô hoặc áo mưa khi di chuyển giữa các giảng đường.",
    };
  } else if (code >= 80 && code <= 82) {
    return {
      moTa: "Mưa rào từng đợt",
      icon: "🌦️",
      loiKhuyen:
        "Ưu tiên tham gia các hoạt động trong hội trường A1 và nhà đa năng.",
    };
  } else if (code >= 95) {
    return {
      moTa: "Có dông sét",
      icon: "⛈️",
      loiKhuyen:
        "Hạn chế hoạt động ngoài trời, chú ý an toàn trong khuôn viên.",
    };
  }
  return {
    moTa: "Thời tiết ôn hòa",
    icon: "🌤️",
    loiKhuyen:
      "Chúc bạn có một ngày tham gia sự kiện tràn đầy năng lượng tại ĐH Sư Phạm!",
  };
}

/**
 * Hiển thị trạng thái đang tải dữ liệu thời tiết.
 */
function hienThiDangTai() {
  if (!khungThoiTiet) return;
  khungThoiTiet.textContent = "";

  const hop = document.createElement("div");
  hop.className = "trang-thai-hop trang-thai-tai py-4";

  const spinner = document.createElement("span");
  spinner.className = "spinner";
  spinner.setAttribute("aria-hidden", "true");

  const text = document.createElement("span");
  text.textContent = "Đang kết nối trạm thời tiết Đà Nẵng (Open-Meteo API)...";

  hop.appendChild(spinner);
  hop.appendChild(text);
  khungThoiTiet.appendChild(hop);
}

/**
 * Hiển thị trạng thái lỗi kèm nút thử lại.
 * @param {string} thongBao - Nội dung lỗi
 */
function hienThiLoi(thongBao) {
  if (!khungThoiTiet) return;
  khungThoiTiet.textContent = "";

  const hop = document.createElement("div");
  hop.className = "trang-thai-hop trang-thai-loi py-4";

  const p = document.createElement("p");
  p.textContent = thongBao || "Không thể lấy thông tin thời tiết trực tiếp.";

  const nutThuLai = document.createElement("button");
  nutThuLai.type = "button";
  nutThuLai.className = "nut-bam nut-nhan nut-nho mt-2";
  nutThuLai.textContent = "Tải lại thời tiết";
  nutThuLai.addEventListener("click", () => {
    taiThoiTietDaNang();
  });

  hop.appendChild(p);
  hop.appendChild(nutThuLai);
  khungThoiTiet.appendChild(hop);
}

/**
 * Render dữ liệu thời tiết an toàn vào DOM.
 * @param {object} duLieu - Dữ liệu trả về từ Open-Meteo API
 */
function renderThoiTiet(duLieu) {
  if (!khungThoiTiet) return;
  khungThoiTiet.textContent = "";

  const hienTai = duLieu.current || {};
  const nhietDo = hienTai.temperature_2m;
  const doAm = hienTai.relative_humidity_2m;
  const tocDoGio = hienTai.wind_speed_10m;
  const maThoiTiet = hienTai.weather_code ?? 0;
  const thongTin = giaiMaThoiTiet(maThoiTiet);

  const card = document.createElement("div");
  card.className = "card-thoi-tiet shadow-sm";

  // Phần đầu card: Vị trí và thời gian
  const cardDau = document.createElement("div");
  cardDau.className = "card-thoi-tiet__dau";

  const diaDiem = document.createElement("span");
  diaDiem.className = "card-thoi-tiet__diadiem";
  diaDiem.textContent = "Khuôn viên ĐH Sư Phạm – Đại học Đà Nẵng (Liên Chiểu)";

  const thoiGian = document.createElement("span");
  thoiGian.className = "card-thoi-tiet__thoigian fs-meta text-phu ms-auto";
  const bayGio = new Date();
  thoiGian.textContent = `Cập nhật lúc ${bayGio.getHours()}:${String(bayGio.getMinutes()).padStart(2, "0")}`;

  cardDau.appendChild(diaDiem);
  cardDau.appendChild(thoiGian);

  // Phần thân card: Nhiệt độ + Icon + Các chỉ số
  const cardThan = document.createElement("div");
  cardThan.className = "card-thoi-tiet__than mt-3";

  const cotTrai = document.createElement("div");
  cotTrai.className = "card-thoi-tiet__chinh";

  const iconTT = document.createElement("span");
  iconTT.className = "card-thoi-tiet__icon-lon";
  iconTT.setAttribute("aria-hidden", "true");
  iconTT.textContent = thongTin.icon;

  const soNhietDo = document.createElement("div");
  soNhietDo.className = "card-thoi-tiet__nhiet-do";
  soNhietDo.textContent = `${nhietDo}°C`;

  const moTaTT = document.createElement("div");
  moTaTT.className = "card-thoi-tiet__mota";
  moTaTT.textContent = thongTin.moTa;

  cotTrai.appendChild(iconTT);
  cotTrai.appendChild(soNhietDo);
  cotTrai.appendChild(moTaTT);

  const cotPhai = document.createElement("div");
  cotPhai.className = "card-thoi-tiet__chitiet";

  // Chỉ số độ ẩm
  const dongDoAm = document.createElement("div");
  dongDoAm.className = "card-thoi-tiet__thongso";
  const labelDoAm = document.createElement("span");
  labelDoAm.textContent = "Độ ẩm: ";
  const valDoAm = document.createElement("strong");
  valDoAm.textContent = `${doAm}%`;
  dongDoAm.appendChild(labelDoAm);
  dongDoAm.appendChild(valDoAm);

  // Chỉ số tốc độ gió
  const dongGio = document.createElement("div");
  dongGio.className = "card-thoi-tiet__thongso";
  const labelGio = document.createElement("span");
  labelGio.textContent = "Gió: ";
  const valGio = document.createElement("strong");
  valGio.textContent = `${tocDoGio} km/h`;
  dongGio.appendChild(labelGio);
  dongGio.appendChild(valGio);

  cotPhai.appendChild(dongDoAm);
  cotPhai.appendChild(dongGio);

  cardThan.appendChild(cotTrai);
  cardThan.appendChild(cotPhai);

  // Lời khuyên tham gia sự kiện
  const cardLoiKhuyen = document.createElement("div");
  cardLoiKhuyen.className = "card-thoi-tiet__loikhuyen mt-3";
  const badgeTip = document.createElement("span");
  badgeTip.className = "badge badge--vang me-2";
  badgeTip.textContent = "Gợi ý hoạt động";
  const textTip = document.createElement("span");
  textTip.className = "fs-meta";
  textTip.textContent = thongTin.loiKhuyen;

  cardLoiKhuyen.appendChild(badgeTip);
  cardLoiKhuyen.appendChild(textTip);

  card.appendChild(cardDau);
  card.appendChild(cardThan);
  card.appendChild(cardLoiKhuyen);

  khungThoiTiet.appendChild(card);
}

/**
 * Gọi REST API Open-Meteo lấy thông tin thời tiết Đà Nẵng.
 */
async function taiThoiTietDaNang() {
  if (!khungThoiTiet) return;

  hienThiDangTai();

  const thamSo = new URLSearchParams({
    latitude: "16.0544",
    longitude: "108.1717",
    current: "temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m",
    timezone: "Asia/Bangkok",
  });

  const apiUrl = `https://api.open-meteo.com/v1/forecast?${thamSo.toString()}`;

  try {
    const duLieu = await taiJSON(apiUrl);
    renderThoiTiet(duLieu);
  } catch (loi) {
    console.error("Lỗi khi tải thời tiết từ Open-Meteo:", loi);
    hienThiLoi(
      "Không thể kết nối đến máy chủ Open-Meteo. Vui lòng kiểm tra lại mạng.",
    );
  }
}

taiThoiTietDaNang();

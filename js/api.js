/**
 * @file api.js
 * @description Module nạp dữ liệu chung qua Fetch API cho toàn website UniEvent.
 * Kiểm tra res.ok và ném lỗi rõ ràng khi request không thành công.
 */

/**
 * Tải và parse dữ liệu JSON từ đường dẫn URL bất kỳ.
 * @param {string} url - Đường dẫn tới tệp JSON hoặc endpoint REST API
 * @returns {Promise<any>} Dữ liệu JSON đã parse
 * @throws {Error} Khi HTTP status không thành công (res.ok === false)
 */
export async function taiJSON(url) {
  const res = await fetch(url);
  if (!res.ok) {
    throw new Error(`HTTP ${res.status}: Không thể tải dữ liệu từ ${url}`);
  }
  return await res.json();
}

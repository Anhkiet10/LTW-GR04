// orders.js — CHỈ chứa code riêng cho trang đơn hàng.
// Các hàm dùng chung (search, backToTop, addToCart, showToast,
// highlightActiveCategory, menuToggle...) đã có sẵn trong main.js
// (được load qua footer.php ở MỌI trang) nên KHÔNG khai báo lại ở đây,
// tránh lỗi "Identifier ... has already been declared" làm chết main.js.

document.addEventListener("DOMContentLoaded", function () {
  // Click vào dòng đơn hàng -> chuyển tới trang chi tiết
  const rows = document.querySelectorAll(".clickable-row");
  rows.forEach((row) => {
    row.addEventListener("click", function (e) {
      // Nếu click trúng link/button/thẻ con của chúng thì để chúng tự xử lý
      if (
        e.target.tagName === "A" ||
        e.target.closest("a") ||
        e.target.tagName === "BUTTON" ||
        e.target.closest("button")
      ) {
        return;
      }
      const url = this.dataset.href;
      if (url) {
        window.location.href = url;
      }
    });
  });

  // Hiệu ứng xuất hiện lần lượt cho các bước trạng thái đơn hàng
  const steps = document.querySelectorAll(".step-order");
  steps.forEach((step, index) => {
    step.style.opacity = "0";
    step.style.transform = "translateY(10px)";
    step.style.transition = `all 0.4s ease ${index * 0.15}s`;

    setTimeout(() => {
      step.style.opacity = "1";
      step.style.transform = "translateY(0)";
    }, 50);
  });
});
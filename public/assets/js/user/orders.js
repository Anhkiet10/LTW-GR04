// ===== SEARCH REALTIME =====
const searchInput = document.getElementById("searchInput");
const suggestions = document.getElementById("searchSuggestions");
let searchTimer;

// ===== HIGHLIGHT ACTIVE CATEGORY =====
function highlightActiveCategory() {
  const params = new URLSearchParams(window.location.search);
  const categoryId = params.get("category");

  if (categoryId) {
    const links = document.querySelectorAll(".category-link");
    links.forEach((link) => {
      if (link.href.includes("category=" + categoryId)) {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });
  }
}

highlightActiveCategory();

if (searchInput) {
  searchInput.addEventListener("input", function () {
    clearTimeout(searchTimer);
    const q = this.value.trim();

    if (q.length < 1) {
      suggestions.innerHTML = "";
      suggestions.style.display = "none";
      return;
    }

    searchTimer = setTimeout(() => {
      fetch("/WEB_GR4/products/search?q=" + encodeURIComponent(q))
        .then((r) => r.json())
        .then((data) => {
          if (!data.length) {
            suggestions.innerHTML =
              '<div class="sg-empty">Không tìm thấy sản phẩm</div>';
          } else {
            suggestions.innerHTML = data
              .map(
                (item) => `
                            <a class="sg-item" href="/WEB_GR4/products/${item.id}">
                                ${
                                  item.image
                                    ? `<img src="${item.image}" alt="${item.name}">`
                                    : `<div class="sg-noimg">📦</div>`
                                }
                                <div>
                                    <div class="sg-name">${item.name}</div>
                                    <div class="sg-price">${item.price}</div>
                                </div>
                            </a>
                        `,
              )
              .join("");
          }
          suggestions.style.display = "block";
        })
        .catch(() => {
          suggestions.style.display = "none";
        });
    }, 250);
  });

  document.addEventListener("click", function (e) {
    if (!e.target.closest(".search-wrap")) {
      suggestions.style.display = "none";
    }
  });

  searchInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") doSearch();
  });
}

function doSearch() {
  const q = searchInput ? searchInput.value.trim() : "";
  if (q) {
    window.location.href = "/WEB_GR4/products?search=" + encodeURIComponent(q);
  }
}

// ===== BACK TO TOP =====
const backToTop = document.getElementById("backToTop");
if (backToTop) {
  window.addEventListener("scroll", function () {
    backToTop.style.display = window.scrollY > 300 ? "flex" : "none";
  });
}

// ===== ADD TO CART =====
function addToCart(productId, variantId = null) {
  if (!variantId) {
    showToast("Vui lòng chọn phiên bản sản phẩm!", "error");
    return;
  }

  fetch("/WEB_GR4/api/add-to-cart.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      product_id: productId,
      variant_id: variantId,
      quantity: 1,
    }),
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        showToast(" Đã thêm vào giỏ hàng!");
      } else {
        showToast(data.message || "Có lỗi xảy ra!", "error");
      }
    })
    .catch(() => showToast("Lỗi kết nối server!", "error"));
}

// ===== TOAST NOTIFICATION =====
function showToast(msg, type = "success") {
  const toast = document.createElement("div");
  toast.className = "toast toast-" + type;
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(() => toast.classList.add("show"), 10);
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}
// ===== MOBILE MENU TOGGLE =====
document.addEventListener("DOMContentLoaded", function () {
  const menuToggle = document.getElementById("menuToggle");
  const navContainer = document.querySelector(".nav-container");

  if (menuToggle && navContainer) {
    menuToggle.addEventListener("click", function (e) {
      e.stopPropagation();
      navContainer.classList.toggle("active");
    });

    // Đóng menu khi click vào link
    const navLinks = navContainer.querySelectorAll("a");
    navLinks.forEach((link) => {
      link.addEventListener("click", function () {
        navContainer.classList.remove("active");
      });
    });

    // Đóng menu khi click bên ngoài
    document.addEventListener("click", function (e) {
      if (!e.target.closest(".navbar")) {
        navContainer.classList.remove("active");
      }
    });
  }
});
// ... (Các đoạn code cũ giữ nguyên: search, backToTop, addToCart, showToast...)

// ===== MOBILE MENU TOGGLE =====
document.addEventListener("DOMContentLoaded", function () {
  const menuToggle = document.getElementById("menuToggle");
  const navContainer = document.querySelector(".nav-container");

  if (menuToggle && navContainer) {
    menuToggle.addEventListener("click", function (e) {
      e.stopPropagation();
      navContainer.classList.toggle("active");
    });

    // Đóng menu khi click vào link
    const navLinks = navContainer.querySelectorAll("a");
    navLinks.forEach((link) => {
      link.addEventListener("click", function () {
        navContainer.classList.remove("active");
      });
    });

    // Đóng menu khi click bên ngoài
    document.addEventListener("click", function (e) {
      if (!e.target.closest(".navbar")) {
        navContainer.classList.remove("active");
      }
    });
  }

  /* ==========================================================================
     DÁN ĐOẠN CODE DƯỚI ĐÂY VÀO TRƯỚC DẤU ĐÓNG NGOẶC CỦA DOMContentLoaded
     ========================================================================== */

  // ===== XỬ LÝ CLICK DÒNG TRÊN BẢNG ĐƠN HÀNG =====
  const tableRows = document.querySelectorAll(".clickable-row");
  tableRows.forEach((row) => {
    row.addEventListener("click", function (e) {
      // Nếu người dùng không click trúng trực tiếp vào thẻ hoặc nút "Chi tiết"
      if (!e.target.classList.contains("btn")) {
        const targetUrl = this.getAttribute("data-href");
        if (targetUrl) {
          window.location.href = targetUrl;
        }
      }
    });
  });

  // ===== HIỆU ỨNG STEP ĐƠN HÀNG CHẠY ĐỘNG (NẾU CÓ TRANG CHI TIẾT) =====
  const steps = document.querySelectorAll(".step-order");
  steps.forEach((step, index) => {
    // Ẩn tạm thời để tạo hiệu ứng mượt
    step.style.opacity = "0";
    step.style.transform = "translateY(10px)";
    step.style.transition = `all 0.4s ease ${index * 0.15}s`;

    // Tạo độ trễ nhỏ để kích hoạt hiệu ứng mượt mà lần lượt từng bước một
    setTimeout(() => {
      step.style.opacity = "1";
      step.style.transform = "translateY(0)";
    }, 50);
  });
});
document.addEventListener("DOMContentLoaded", function () {
  // Tìm tất cả các dòng có class 'clickable-row'
  const rows = document.querySelectorAll(".clickable-row");

  rows.forEach((row) => {
    row.addEventListener("click", function (e) {
      // Nếu người dùng click trúng nút "Chi tiết" hoặc thẻ link/button bên trong thì để thẻ đó tự xử lý
      if (
        e.target.tagName === "A" ||
        e.target.closest("a") ||
        e.target.tagName === "BUTTON"
      ) {
        return;
      }

      // Lấy đường dẫn từ thuộc tính data-href và chuyển hướng trang
      const url = this.dataset.href;
      if (url) {
        window.location.href = url;
      }
    });
  });
});

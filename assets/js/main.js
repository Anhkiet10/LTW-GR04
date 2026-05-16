// ===== SEARCH REALTIME =====
const searchInput = document.getElementById("searchInput");
const suggestions = document.getElementById("searchSuggestions");
let searchTimer;

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
      fetch("/WEB_GR4/api/search.php?q=" + encodeURIComponent(q))
        .then((r) => r.json())
        .then((data) => {
          if (!data.length) {
            suggestions.innerHTML =
              '<div class="sg-empty">Không tìm thấy sản phẩm</div>';
          } else {
            suggestions.innerHTML = data
              .map(
                (item) => `
                            <a class="sg-item" href="/WEB_GR4/pages/product-detail.php?id=${item.id}">
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
    }, 250); // debounce 250ms
  });

  // Đóng suggestion khi click ra ngoài
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".search-wrap")) {
      suggestions.style.display = "none";
    }
  });

  // Enter để tìm kiếm
  searchInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") doSearch();
  });
}

function doSearch() {
  const q = searchInput ? searchInput.value.trim() : "";
  if (q) {
    window.location.href =
      "/WEB_GR4/pages/products.php?search=" + encodeURIComponent(q);
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
function addToCart(productId) {
  fetch("/WEB_GR4/api/add-to-cart.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ product_id: productId, quantity: 1 }),
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        showToast("✅ Đã thêm vào giỏ hàng!");
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

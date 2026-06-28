// add to cart

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".cart-check").forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      updateTotal();
    });
  });

  // ADD +
  document.querySelectorAll(".plus").forEach((button) => {
    button.addEventListener("click", function () {
      let row = this.closest("tr");
      let quantity = row.querySelector(".quantity");
      let qty = parseInt(quantity.innerText) + 1;
      let cartItemId = this.dataset.id;

      fetch("/WEB_GR4/cart/update", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cart_item_id: cartItemId, quantity: qty }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            quantity.innerText = qty;
            let price = parseInt(
              row.querySelector(".price-cart").dataset.price,
            );
            row.querySelector(".subtotal").innerText =
              (price * qty).toLocaleString("vi-VN") + "đ";
            updateTotal();
          }
        });
    });
  });

  // MIN -
  document.querySelectorAll(".btn-min").forEach((button) => {
    button.addEventListener("click", function () {
      let row = this.closest("tr");
      let quantity = row.querySelector(".quantity");
      let qty = parseInt(quantity.innerText);

      if (qty <= 1) return;
      qty--;

      let cartItemId = this.dataset.id;

      fetch("/WEB_GR4/cart/update", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cart_item_id: cartItemId, quantity: qty }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            quantity.innerText = qty;
            let price = parseInt(
              row.querySelector(".price-cart").dataset.price,
            );
            row.querySelector(".subtotal").innerText =
              (price * qty).toLocaleString("vi-VN") + "đ";
            updateTotal();
          }
        });
    });
  });

  // DELETE
  document.querySelectorAll(".btn-delete").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();

      let cartItemId = this.dataset.id;
      let row = this.closest("tr");

      fetch("/WEB_GR4/cart/delete", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cart_item_id: cartItemId }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            row.remove();
            updateTotal();
            showToast("Đã xóa sản phẩm khỏi giỏ hàng!");
          } else {
            showToast("Có lỗi xảy ra!", "error");
          }
        })
        .catch(() => {
          showToast("Lỗi kết nối Server!", "error");
        });
    });
  });

  updateTotal();
});

function updateTotal() {
  let total = 0;

  document.querySelectorAll("tbody tr").forEach((row) => {
    let check = row.querySelector(".cart-check");
    if (check && check.checked) {
      let subtotal = row.querySelector(".subtotal").innerText;
      subtotal = subtotal.replace(/\./g, "").replace("đ", "").trim();
      total += Number(subtotal);
    }
  });

  document.getElementById("total-price").innerText =
    total.toLocaleString("vi-VN") + "đ";
}

// ===== NÚT ĐẶT HÀNG =====
document
  .getElementById("btnOrder")
  .addEventListener("click", async function () {
    const checked = document.querySelectorAll(".cart-check:checked");

    if (checked.length === 0) {
      alert("Vui lòng chọn đầy đủ phiên bản sản phẩm trước khi mua");
      return;
    }

    const selectedIds = Array.from(checked).map((cb) =>
      parseInt(cb.dataset.id),
    );

    // Kiểm tra thông tin giao hàng trước
    const infoRes = await fetch("/WEB_GR4/cart/checkInfo");
    const infoData = await infoRes.json();

    if (!infoData.complete) {
      document.getElementById("addressModal").style.display = "flex";
      // Lưu selectedIds để dùng sau khi nhập địa chỉ
      window._pendingSelectedIds = selectedIds;
      return;
    }

    // Chuyển sang trang preview - CHƯA tạo đơn
    goToPreview(selectedIds);
  });

function goToPreview(selectedIds) {
  // Truyền selected_ids qua sessionStorage (dùng ở payment.php) VÀ qua URL (dùng ở preview())
  sessionStorage.setItem("pendingCartIds", JSON.stringify(selectedIds));
  window.location.href = "/WEB_GR4/orders/preview?ids=" + selectedIds.join(",");
}

// ===== FORM ĐỊA CHỈ =====
document.getElementById("addressForm").addEventListener("submit", function (e) {
  e.preventDefault();

  let formData = new FormData(this);

  fetch("/WEB_GR4/cart/saveAddress", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        showToast("Thêm thông tin thành công!");
        document.getElementById("addressModal").style.display = "none";

        const selectedIds = window._pendingSelectedIds || [];
        setTimeout(() => goToPreview(selectedIds), 800);
      }
    });
});

// add to cart

// function addToCart(productId, variantId = null) {
//   if (!variantId) {
//     alert("Vui lòng chọn phiên bản");
//     return;
//   }

//   fetch("/WEB_GR4/cart/add", {
//     method: "POST",
//     headers: {
//       "Content-Type": "application/json",
//     },
//     body: JSON.stringify({
//       product_id: productId,
//       variant_id: variantId,
//     }),
//   })
//     .then((res) => res.json())
//     .then((data) => {
//       if (data.login) {
//         window.location.href = "/WEB_GR4/login";
//         return;
//       }

//       if (data.success) {
//         showToast("Đã thêm vào giỏ hàng thành công!");
//       } else {
//         showToast("Có lỗi xảy ra!", "error");
//       }
//     });
// }

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
        body: JSON.stringify({
          cart_item_id: cartItemId,
          quantity: qty,
        }),
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
        body: JSON.stringify({
          cart_item_id: cartItemId,
          quantity: qty,
        }),
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

      // KHÔNG confirm nữa

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

    if (check.checked) {
      let subtotal = row.querySelector(".subtotal").innerText;

      subtotal = subtotal.replace(/\./g, "");
      subtotal = subtotal.replace("đ", "").trim();

      total += Number(subtotal);
    }
  });

  document.getElementById("total-price").innerText =
    total.toLocaleString("vi-VN") + "đ";
}

document
  .getElementById("btnOrder")
  .addEventListener("click", async function () {
    const checked = document.querySelectorAll(".cart-check:checked");

    if (checked.length === 0) {
      alert("Vui lòng chọn ít nhất một sản phẩm!");
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

    // Đặt hàng luôn nếu đã có địa chỉ
    const orderRes = await fetch("/WEB_GR4/cart/placeOrder", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ selected_ids: selectedIds }),
    });
    const order = await orderRes.json();

    if (order.success) {
      window.location.href = "/WEB_GR4/orders/" + order.order_id;
    } else {
      alert(order.message || "Có lỗi xảy ra!");
    }
  });
document.getElementById("addressForm").addEventListener("submit", function (e) {
  e.preventDefault();

  let formData = new FormData(this);

  fetch("/WEB_GR4/cart/saveAddress", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then(async (data) => {
      if (data.success) {
        showToast("Thêm thông tin thành công!");
        document.getElementById("addressModal").style.display = "none";

        // Dùng lại selectedIds đã lưu trước đó
        const selectedIds = window._pendingSelectedIds || [];

        setTimeout(async () => {
          const orderRes = await fetch("/WEB_GR4/cart/placeOrder", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ selected_ids: selectedIds }),
          });
          const order = await orderRes.json();

          if (order.success) {
            window.location.href = "/WEB_GR4/orders/" + order.order_id;
          } else {
            alert(order.message || "Có lỗi xảy ra!");
          }
        }, 1000);
      }
    });
});

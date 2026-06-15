// add to cart
function addToCart(productId, variantId = null) {
  if (!variantId) {
    alert("Vui lòng chọn phiên bản");
    return;
  }

  fetch("/WEB_GR4/cart/add", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      product_id: productId,
      variant_id: variantId,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        showToast("Đã thêm vào giỏ hàng thành công!");
      } else {
        showToast("Có lỗi xảy ra!", "error");
      }
    })
    .catch(() => {
      showToast("Lỗi kết nối Server!", "error");
    });
}

document.addEventListener("DOMContentLoaded", function () {
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
});
function updateTotal() {
  let total = 0;

  document.querySelectorAll(".subtotal").forEach((item) => {
    let value = item.innerText.replaceAll(".", "").replace("đ", "");

    total += parseInt(value);
  });

  document.getElementById("total-price").innerText =
    total.toLocaleString("vi-VN") + "đ";
}

/**
 * Detailbuynow.js
 * Xử lý nút "Mua ngay" trên trang chi tiết sản phẩm.
 * Phối hợp với product_detail.js (dùng .attr-value-btn.selected)
 */

document.addEventListener("DOMContentLoaded", function () {
  const btnBuyNow = document.getElementById("btnBuyNow");
  if (!btnBuyNow) return;

  const wrap = document.querySelector(".detail-wrap");
  if (!wrap) return;

  const PRODUCT_VARIANTS = JSON.parse(wrap.dataset.variants || "[]");
  const HAS_ATTRIBUTES = wrap.dataset.hasAttributes === "1";

  function buildKey(ids) {
    return [...ids]
      .sort(function (a, b) {
        return a - b;
      })
      .join("_");
  }

  function getSelectedValueIds() {
    return Array.from(
      document.querySelectorAll(".attr-value-btn.selected"),
    ).map(function (btn) {
      return parseInt(btn.dataset.valueId, 10);
    });
  }

  function findExactVariant(selectedIds) {
    if (!selectedIds.length) return null;
    var key = buildKey(selectedIds);
    return (
      PRODUCT_VARIANTS.find(function (v) {
        return v.variant_key === key;
      }) || null
    );
  }

  // Lấy tên đầy đủ của các attribute đang chọn
  // Ví dụ: "Bạc, 256GB SSD, 16GB"
  function getVariantLabel() {
    var labels = [];
    document
      .querySelectorAll(".attr-value-btn.selected")
      .forEach(function (btn) {
        // Lấy tên attribute (cha) + tên value
        var row = btn.closest(".attribute-row");
        var attrLabel = row ? row.querySelector(".attribute-label") : null;
        var attrName = attrLabel
          ? attrLabel.textContent.replace(":", "").trim()
          : "";
        var valueName = btn.textContent.trim();
        labels.push(attrName ? attrName + ": " + valueName : valueName);
      });
    return labels.join(", ");
  }

  function getCurrentVariant() {
    if (!HAS_ATTRIBUTES) {
      return (
        PRODUCT_VARIANTS.find(function (v) {
          return v.variant_key === "default";
        }) ||
        PRODUCT_VARIANTS[0] ||
        null
      );
    }
    return findExactVariant(getSelectedValueIds());
  }

  btnBuyNow.addEventListener("click", function () {
    var variant = getCurrentVariant();
    var productId = btnBuyNow.dataset.productId;
    var productName = btnBuyNow.dataset.productName;
    var imageUrl = btnBuyNow.dataset.imageUrl || "";
    var quantity = parseInt(
      (document.getElementById("qtyInput") || {}).value || "1",
      10,
    );

    if (HAS_ATTRIBUTES && !variant) {
      alert("Vui lòng chọn đầy đủ phiên bản sản phẩm trước khi mua");
      return;
    }

    var price = variant ? parseFloat(variant.price) : 0;
    var variantId = variant ? variant.variant_id : null;
    var variantKey = variant ? variant.variant_key : "";
    // Lấy tên hiển thị đẹp, ví dụ "Màu sắc: Bạc, Dung lượng: 256GB SSD, RAM: 16GB"
    var variantLabel = HAS_ATTRIBUTES ? getVariantLabel() : "";

    if (!price || price <= 0) {
      alert("Không xác định được giá sản phẩm, vui lòng thử lại");
      return;
    }

    btnBuyNow.disabled = true;
    btnBuyNow.textContent = "Đang xử lý...";

    fetch("/WEB_GR4/products/buy-now", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({
        product_id: parseInt(productId, 10),
        variant_id: variantId,
        variant_key: variantKey,
        variant_label: variantLabel, // ← thêm mới
        quantity: quantity,
        price: price,
        product_name: productName,
        image_url: imageUrl,
      }),
    })
      .then(function (r) {
        if (!r.ok) throw new Error("HTTP " + r.status);
        return r.json();
      })
      .then(function (data) {
        if (data.success && data.redirect) {
          window.location.href = data.redirect;
        } else {
          alert(data.message || "Có lỗi xảy ra, vui lòng thử lại");
          btnBuyNow.disabled = false;
          btnBuyNow.textContent = "Mua ngay";
        }
      })
      .catch(function () {
        alert("Lỗi kết nối, vui lòng thử lại");
        btnBuyNow.disabled = false;
        btnBuyNow.textContent = "Mua ngay";
      });
  });
});

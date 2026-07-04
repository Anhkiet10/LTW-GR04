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
  const LOGGED_IN = wrap.dataset.loggedIn === "1";

  const addressModal = document.getElementById("addressModal");
  const addressForm = document.getElementById("addressForm");
  const closeBtn = addressModal ? addressModal.querySelector(".close") : null;

  function notify(message, type) {
    if (typeof showToast === "function") {
      showToast(message, type);
    } else {
      alert(message);
    }
  }

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

  function resetButton() {
    btnBuyNow.disabled = false;
    btnBuyNow.textContent = "Mua ngay";
  }

  function submitBuyNow(payload) {
    btnBuyNow.disabled = true;
    btnBuyNow.textContent = "Đang xử lý...";

    fetch("/WEB_GR4/products/buy-now", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(payload),
    })
      .then(function (r) {
        if (!r.ok) throw new Error("HTTP " + r.status);
        return r.json();
      })
      .then(function (data) {
        if (data.success && data.redirect) {
          window.location.href = data.redirect;
        } else {
          notify(data.message || "Có lỗi xảy ra, vui lòng thử lại", "error");
          resetButton();
        }
      })
      .catch(function () {
        notify("Lỗi kết nối, vui lòng thử lại", "error");
        resetButton();
      });
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
      notify("Vui lòng chọn đầy đủ phiên bản sản phẩm trước khi mua", "error");
      return;
    }

    var price = variant ? parseFloat(variant.price) : 0;
    var variantId = variant ? variant.variant_id : null;
    var variantKey = variant ? variant.variant_key : "";
    // Lấy tên hiển thị đẹp, ví dụ "Màu sắc: Bạc, Dung lượng: 256GB SSD, RAM: 16GB"
    var variantLabel = HAS_ATTRIBUTES ? getVariantLabel() : "";

    if (!price || price <= 0) {
      notify("Không xác định được giá sản phẩm, vui lòng thử lại", "error");
      return;
    }

    var payload = {
      product_id: parseInt(productId, 10),
      variant_id: variantId,
      variant_key: variantKey,
      variant_label: variantLabel,
      quantity: quantity,
      price: price,
      product_name: productName,
      image_url: imageUrl,
    };

    // Khách vãng lai: chưa có tài khoản nên chưa có số điện thoại/địa chỉ
    // mặc định để kiểm tra -> cho đi thẳng, thông tin sẽ nhập ở bước checkout khách.
    if (!LOGGED_IN) {
      submitBuyNow(payload);
      return;
    }

    btnBuyNow.disabled = true;
    btnBuyNow.textContent = "Đang kiểm tra...";

    fetch("/WEB_GR4/cart/checkInfo")
      .then(function (r) {
        return r.json();
      })
      .then(function (infoData) {
        if (infoData.complete) {
          submitBuyNow(payload);
        } else {
          resetButton();
          window._pendingBuyNowPayload = payload;
          if (addressModal) {
            addressModal.style.display = "flex";
          } else {
            notify(
              "Vui lòng cập nhật số điện thoại và địa chỉ giao hàng trước khi mua",
              "error",
            );
          }
        }
      })
      .catch(function () {
        resetButton();
        notify("Lỗi kết nối, vui lòng thử lại", "error");
      });
  });

  // ===== FORM ĐỊA CHỈ (dùng chung kiểu với trang giỏ hàng) =====
  if (addressForm) {
    addressForm.addEventListener("submit", function (e) {
      e.preventDefault();

      var formData = new FormData(addressForm);
      var submitBtn = addressForm.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      fetch("/WEB_GR4/cart/saveAddress", {
        method: "POST",
        body: formData,
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          if (submitBtn) submitBtn.disabled = false;
          if (data.success) {
            notify("Thêm thông tin thành công!");
            addressModal.style.display = "none";

            var pending = window._pendingBuyNowPayload;
            window._pendingBuyNowPayload = null;
            if (pending) submitBuyNow(pending);
          } else {
            notify(data.message || "Có lỗi xảy ra, vui lòng thử lại", "error");
          }
        })
        .catch(function () {
          if (submitBtn) submitBtn.disabled = false;
          notify("Lỗi kết nối, vui lòng thử lại", "error");
        });
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      addressModal.style.display = "none";
      window._pendingBuyNowPayload = null;
    });
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const wrap = document.querySelector(".detail-wrap");
  if (!wrap) return;

  // ── Đọc data từ HTML ──────────────────────────────────────────────
  const PRODUCT_VARIANTS = JSON.parse(wrap.dataset.variants || "[]");
  const HAS_ATTRIBUTES = wrap.dataset.hasAttributes === "1";
  const ATTRIBUTE_ROW_COUNT = parseInt(wrap.dataset.attributeRowCount, 10) || 0;
  const DEFAULT_PRICE_TEXT = wrap.dataset.defaultPrice || "—";
  const PRODUCT_ID = parseInt(wrap.dataset.productId, 10);

  // ── Helpers ───────────────────────────────────────────────────────
  function formatPrice(n) {
    return Number(n).toLocaleString("vi-VN") + "đ";
  }

  function buildKey(ids) {
    return [...ids].sort((a, b) => a - b).join("_");
  }

  function getSelectedValueIds() {
    return [...document.querySelectorAll(".attr-value-btn.selected")].map(
      (btn) => parseInt(btn.dataset.valueId, 10),
    );
  }

  function variantContainsAll(keyIds, selectedIds) {
    if (!selectedIds.length) return true;
    return selectedIds.every((id) => keyIds.includes(id));
  }

  function findExactVariant(selectedIds) {
    if (!selectedIds.length) return null;
    const key = buildKey(selectedIds);
    return PRODUCT_VARIANTS.find((v) => v.variant_key === key) || null;
  }

  function allAttributesSelected() {
    if (ATTRIBUTE_ROW_COUNT === 0) return false;
    let filled = 0;
    document.querySelectorAll(".attribute-row").forEach((row) => {
      if (row.querySelector(".attr-value-btn.selected")) filled++;
    });
    return filled === ATTRIBUTE_ROW_COUNT;
  }

  function getSelectedVariantId() {
    if (!HAS_ATTRIBUTES) {
      const v =
        PRODUCT_VARIANTS.find((x) => x.variant_key === "default") ||
        PRODUCT_VARIANTS[0];
      return v ? String(v.variant_id) : "";
    }
    const exact = findExactVariant(getSelectedValueIds());
    return exact ? String(exact.variant_id) : "";
  }

  // ── UI update ─────────────────────────────────────────────────────
  function updatePriceAndStock(variant) {
    const priceEl = document.getElementById("productPrice");
    const stockEl = document.getElementById("variantStockInfo");
    if (!priceEl) return;

    if (variant) {
      priceEl.textContent = formatPrice(variant.price);
      if (stockEl) {
        stockEl.textContent =
          variant.stock > 0 ? "Còn hàng: " + variant.stock : "Hết hàng";
        stockEl.className =
          "variant-stock-info " +
          (variant.stock > 0 ? "in-stock" : "out-of-stock");
      }
      return;
    }

    const selectedIds = getSelectedValueIds();
    if (stockEl) {
      stockEl.textContent = "";
      stockEl.className = "variant-stock-info";
    }

    if (selectedIds.length === 0) {
      priceEl.textContent = DEFAULT_PRICE_TEXT;
    } else if (allAttributesSelected()) {
      priceEl.textContent = "Tổ hợp không khả dụng";
    } else {
      priceEl.textContent = "Vui lòng chọn đủ thuộc tính";
    }
  }

  function updateAttributeAvailability() {
    const selectedByAttr = {};
    document.querySelectorAll(".attr-value-btn.selected").forEach((btn) => {
      selectedByAttr[btn.dataset.attributeId] = parseInt(
        btn.dataset.valueId,
        10,
      );
    });

    document.querySelectorAll(".attr-value-btn").forEach((btn) => {
      const attrId = btn.dataset.attributeId;
      const valueId = parseInt(btn.dataset.valueId, 10);
      const otherSelected = Object.entries(selectedByAttr)
        .filter(([aid]) => aid !== attrId)
        .map(([, vid]) => vid);

      const available = PRODUCT_VARIANTS.some(
        (v) =>
          v.variant_key !== "default" &&
          variantContainsAll(v.key_ids, [...otherSelected, valueId]),
      );

      btn.classList.toggle("disabled", !available);
      if (!available) btn.classList.remove("selected");
    });
  }

  function onSelectionChange() {
    updateAttributeAvailability();
    const exact = findExactVariant(getSelectedValueIds());
    updatePriceAndStock(exact);
  }

  // ── Nút thêm giỏ hàng ────────────────────────────────────────────
  const btnAddCart = document.getElementById("btnAddCart");
  if (btnAddCart) {
    btnAddCart.addEventListener("click", function () {
      const variantId = getSelectedVariantId();
      addToCart(PRODUCT_ID, variantId); // addToCart định nghĩa trong cart.js
    });
  }

  // ── Init ──────────────────────────────────────────────────────────
  if (!HAS_ATTRIBUTES && PRODUCT_VARIANTS.length > 0) {
    const v =
      PRODUCT_VARIANTS.find((x) => x.variant_key === "default") ||
      PRODUCT_VARIANTS[0];
    updatePriceAndStock(v);
  }

  document.querySelectorAll(".attr-value-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      if (this.classList.contains("disabled")) return;

      const attrId = this.dataset.attributeId;
      const wasSelected = this.classList.contains("selected");

      document
        .querySelectorAll('.attr-value-btn[data-attribute-id="' + attrId + '"]')
        .forEach((b) => b.classList.remove("selected"));

      if (!wasSelected) this.classList.add("selected");

      onSelectionChange();
    });
  });

  if (HAS_ATTRIBUTES) {
    updateAttributeAvailability();
  }
});

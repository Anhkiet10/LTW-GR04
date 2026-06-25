// ─── Model ────────────────────────────────────────────────────────────────────

const AddressFormModel = (() => {
  const rules = {
    label(value) {
      if (value.trim().length > 50)
        return {
          valid: false,
          message: "Nhãn địa chỉ không được vượt quá 50 ký tự.",
        };
      return { valid: true };
    },

    full_address(value) {
      if (!value.trim())
        return { valid: false, message: "Vui lòng nhập địa chỉ chi tiết." };
      return { valid: true };
    },

    city(value) {
      if (!value.trim())
        return { valid: false, message: "Vui lòng nhập tỉnh / thành phố." };
      if (value.trim().length > 100)
        return {
          valid: false,
          message: "Tên tỉnh/thành không được vượt quá 100 ký tự.",
        };
      return { valid: true };
    },
  };

  function validate(formData) {
    const errors = {};
    for (const [field, ruleFn] of Object.entries(rules)) {
      const value = formData.get(field) ?? "";
      const result = ruleFn(value, formData);
      if (!result.valid) errors[field] = result.message;
    }
    return { valid: Object.keys(errors).length === 0, errors };
  }

  return { validate };
})();

// ─── View ─────────────────────────────────────────────────────────────────────

const AddressFormView = (() => {
  // Khớp cả form thêm địa chỉ (store-address) lẫn sửa địa chỉ (update-address)
  const form = document.querySelector(
    'form[action$="/store-address"], form[action$="/update-address"]',
  );
  const submitBtn = form?.querySelector('button[type="submit"]');

  // Tắt validation mặc định của trình duyệt để JS tự kiểm soát hiển thị lỗi
  form?.setAttribute("novalidate", "novalidate");

  function showFieldError(fieldName, message) {
    clearFieldError(fieldName);

    const input = form?.querySelector(`[name="${fieldName}"]`);
    if (!input) return;

    input.classList.add("input--error");

    const errorEl = document.createElement("span");
    errorEl.className = "field-error";
    errorEl.textContent = message;
    errorEl.setAttribute("role", "alert");
    input.insertAdjacentElement("afterend", errorEl);
  }

  function clearFieldError(fieldName) {
    const input = form?.querySelector(`[name="${fieldName}"]`);
    if (!input) return;

    input.classList.remove("input--error");
    input.nextElementSibling?.classList.contains("field-error") &&
      input.nextElementSibling.remove();
  }

  function clearAllErrors() {
    form?.querySelectorAll(".field-error").forEach((el) => el.remove());
    form
      ?.querySelectorAll(".input--error")
      .forEach((el) => el.classList.remove("input--error"));
  }

  function renderErrors(errors) {
    clearAllErrors();
    for (const [field, message] of Object.entries(errors)) {
      showFieldError(field, message);
    }
    const firstError = form?.querySelector(".input--error");
    firstError?.scrollIntoView({ behavior: "smooth", block: "center" });
    firstError?.focus();
  }

  function setSubmitting(isSubmitting) {
    if (!submitBtn) return;
    submitBtn.disabled = isSubmitting;
    submitBtn.textContent = isSubmitting ? "Đang lưu…" : "Lưu thay đổi";
  }

  return {
    form,
    showFieldError,
    clearFieldError,
    clearAllErrors,
    renderErrors,
    setSubmitting,
  };
})();

// ─── Controller ───────────────────────────────────────────────────────────────

const AddressFormController = (() => {
  function init() {
    if (!AddressFormView.form) return; // không ở trang thêm/sửa địa chỉ → bỏ qua

    _setupRealtimeValidation();
    _setupFormSubmit();
  }

  function _setupRealtimeValidation() {
    const fields = ["label", "full_address", "city"];

    fields.forEach((field) => {
      const input = AddressFormView.form.querySelector(`[name="${field}"]`);
      if (!input) return;

      input.addEventListener("blur", () => {
        const formData = new FormData(AddressFormView.form);
        const { errors } = AddressFormModel.validate(formData);

        if (errors[field]) {
          AddressFormView.showFieldError(field, errors[field]);
        } else {
          AddressFormView.clearFieldError(field);
        }
      });

      input.addEventListener("input", () => {
        AddressFormView.clearFieldError(field);
      });
    });
  }

  function _setupFormSubmit() {
    AddressFormView.form.addEventListener("submit", (e) => {
      const formData = new FormData(AddressFormView.form);
      const { valid, errors } = AddressFormModel.validate(formData);

      if (!valid) {
        e.preventDefault();
        AddressFormView.renderErrors(errors);
        return;
      }

      AddressFormView.setSubmitting(true);
    });
  }

  return { init };
})();

// ─── Boot ─────────────────────────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", () => {
  AddressFormController.init();
});

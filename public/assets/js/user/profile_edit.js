const ProfileModel = (() => {
  /**
   * Validation rules cho từng trường.
   * Mỗi rule trả về { valid: bool, message: string }.
   */
  const rules = {
    full_name(value) {
      if (!value.trim())
        return { valid: false, message: "Vui lòng nhập họ và tên." };
      if (value.trim().length > 100)
        return {
          valid: false,
          message: "Họ tên không được vượt quá 100 ký tự.",
        };
      return { valid: true };
    },

    email(value) {
      if (!value.trim())
        return { valid: false, message: "Vui lòng nhập email." };
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value.trim()))
        return { valid: false, message: "Email không hợp lệ." };
      if (value.trim().length > 150)
        return {
          valid: false,
          message: "Email không được vượt quá 150 ký tự.",
        };
      return { valid: true };
    },

    phone(value) {
      if (!value.trim()) return { valid: true }; // optional
      const phoneRegex = /^[0-9\s\+\-\(\)]{7,20}$/;
      if (!phoneRegex.test(value.trim()))
        return { valid: false, message: "Số điện thoại không hợp lệ." };
      return { valid: true };
    },

    password(value) {
      if (!value) return { valid: true }; // optional
      if (value.length < 6)
        return { valid: false, message: "Mật khẩu phải có ít nhất 6 ký tự." };
      return { valid: true };
    },

    confirm_password(value, formData) {
      const password = formData.get("password") || "";
      if (!password) return { valid: true }; // password not being changed
      if (value !== password)
        return { valid: false, message: "Mật khẩu xác nhận không khớp." };
      return { valid: true };
    },
  };

  /**
   * Validate toàn bộ form.
   * @param {FormData} formData
   * @returns {{ valid: boolean, errors: Object<string, string> }}
   */
  function validate(formData) {
    const errors = {};

    for (const [field, ruleFn] of Object.entries(rules)) {
      const value = formData.get(field) ?? "";
      const result = ruleFn(value, formData);
      if (!result.valid) {
        errors[field] = result.message;
      }
    }

    return {
      valid: Object.keys(errors).length === 0,
      errors,
    };
  }

  /**
   * Kiểm tra mật khẩu có độ mạnh (weak / medium / strong).
   */
  function passwordStrength(password) {
    if (!password) return null;
    let score = 0;
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    if (score <= 1) return "weak";
    if (score <= 3) return "medium";
    return "strong";
  }

  return { validate, passwordStrength };
})();

// ─── View ─────────────────────────────────────────────────────────────────────

const ProfileView = (() => {
  // Cache các phần tử DOM
  const form = document.querySelector('form[action*="profile/update"]');
  const submitBtn = form?.querySelector('button[type="submit"]');
  const passwordInput = form?.querySelector("#password");
  const confirmInput = form?.querySelector("#confirm_password");

  /** Hiển thị lỗi cho một field cụ thể */
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

  /** Xoá lỗi của một field */
  function clearFieldError(fieldName) {
    const input = form?.querySelector(`[name="${fieldName}"]`);
    if (!input) return;

    input.classList.remove("input--error");
    input.nextElementSibling?.classList.contains("field-error") &&
      input.nextElementSibling.remove();
  }

  /** Xoá tất cả lỗi */
  function clearAllErrors() {
    form?.querySelectorAll(".field-error").forEach((el) => el.remove());
    form
      ?.querySelectorAll(".input--error")
      .forEach((el) => el.classList.remove("input--error"));
  }

  /** Render tất cả lỗi từ object errors */
  function renderErrors(errors) {
    clearAllErrors();
    for (const [field, message] of Object.entries(errors)) {
      showFieldError(field, message);
    }
    // Scroll tới lỗi đầu tiên
    const firstError = form?.querySelector(".input--error");
    firstError?.scrollIntoView({ behavior: "smooth", block: "center" });
    firstError?.focus();
  }

  /** Hiển thị / ẩn thanh độ mạnh mật khẩu */
  function renderPasswordStrength(level) {
    let indicator = document.querySelector(".password-strength");

    if (!level) {
      indicator?.remove();
      return;
    }

    if (!indicator) {
      indicator = document.createElement("div");
      indicator.className = "password-strength";
      passwordInput.insertAdjacentElement("afterend", indicator);
    }

    const labels = { weak: "Yếu", medium: "Trung bình", strong: "Mạnh" };
    indicator.className = `password-strength password-strength--${level}`;
    indicator.textContent = `Độ mạnh: ${labels[level]}`;
  }

  /** Đặt trạng thái loading cho nút submit */
  function setSubmitting(isSubmitting) {
    if (!submitBtn) return;
    submitBtn.disabled = isSubmitting;
    submitBtn.textContent = isSubmitting ? "Đang lưu…" : "Lưu thay đổi";
  }

  /** Hiển thị toast thông báo (success / error) */
  function showToast(message, type = "success") {
    let toast = document.querySelector(".js-toast");
    if (!toast) {
      toast = document.createElement("div");
      toast.className = "js-toast";
      document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.className = `js-toast js-toast--${type} js-toast--visible`;

    clearTimeout(toast._hideTimer);
    toast._hideTimer = setTimeout(() => {
      toast.classList.remove("js-toast--visible");
    }, 3500);
  }

  return {
    form,
    passwordInput,
    confirmInput,
    showFieldError,
    renderErrors,
    clearFieldError,
    clearAllErrors,
    renderPasswordStrength,
    setSubmitting,
    showToast,
  };
})();

// ─── Controller ───────────────────────────────────────────────────────────────

const ProfileController = (() => {
  function init() {
    if (!ProfileView.form) return; // không ở trang edit → bỏ qua

    _setupRealtimeValidation();
    _setupPasswordStrength();
    _setupFormSubmit();
  }

  /** Validate từng field ngay khi người dùng rời khỏi ô (blur) */
  function _setupRealtimeValidation() {
    const fields = [
      "full_name",
      "email",
      "phone",
      "password",
      "confirm_password",
    ];

    fields.forEach((field) => {
      const input = ProfileView.form.querySelector(`[name="${field}"]`);
      if (!input) return;

      input.addEventListener("blur", () => {
        const formData = new FormData(ProfileView.form);
        const { errors } = ProfileModel.validate(formData);

        if (errors[field]) {
          ProfileView.showFieldError(field, errors[field]);
        } else {
          ProfileView.clearFieldError(field);
        }
      });

      // Xoá lỗi ngay khi bắt đầu gõ
      input.addEventListener("input", () => {
        ProfileView.clearFieldError(field);
      });
    });
  }

  /** Hiển thị thanh độ mạnh mật khẩu khi gõ */
  function _setupPasswordStrength() {
    ProfileView.passwordInput?.addEventListener("input", (e) => {
      const level = ProfileModel.passwordStrength(e.target.value);
      ProfileView.renderPasswordStrength(level);
    });
  }

  /** Validate toàn bộ trước khi submit */
  function _setupFormSubmit() {
    ProfileView.form.addEventListener("submit", (e) => {
      const formData = new FormData(ProfileView.form);
      const { valid, errors } = ProfileModel.validate(formData);

      if (!valid) {
        e.preventDefault();
        ProfileView.renderErrors(errors);
        ProfileView.showToast("Vui lòng kiểm tra lại thông tin.", "error");
        return;
      }

      // Nếu hợp lệ → hiển thị loading
      ProfileView.setSubmitting(true);
    });
  }

  return { init };
})();

// ─── Boot ─────────────────────────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", () => {
  ProfileController.init();
});

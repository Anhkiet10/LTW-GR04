// ─── Model ────────────────────────────────────────────────────────────────────

const ProfileIndexModel = (() => {
  const AUTO_HIDE_DELAY = 3500;

  return { AUTO_HIDE_DELAY };
})();

// ─── View ─────────────────────────────────────────────────────────────────────

const ProfileIndexView = (() => {
  const alertEls = document.querySelectorAll(".profile-content .alert");
  // Tìm form theo class mới thêm
  const deleteForms = document.querySelectorAll(".form-delete-address");
  const defaultForms = document.querySelectorAll(
    'form[action*="set-default-address"]',
  );

  // Custom Modal Elements
  const customModal = document.getElementById("custom-confirm-modal");
  const btnOk = document.getElementById("custom-confirm-ok");
  const btnCancel = document.getElementById("custom-confirm-cancel");

  /** Ẩn dần 1 alert rồi xóa khỏi DOM */
  function fadeOutAlert(el) {
    el.classList.add("alert--hiding");
    el.addEventListener(
      "transitionend",
      () => {
        el.remove();
      },
      { once: true },
    );
    setTimeout(() => el.remove(), 400);
  }

  /** Đặt trạng thái loading cho 1 nút submit */
  function setButtonLoading(btn, loadingText) {
    if (!btn) return;
    btn.dataset.originalText = btn.dataset.originalText || btn.textContent;
    btn.disabled = true;
    btn.textContent = loadingText;
  }

  /** Hiển thị Custom Modal và xử lý callback */
  function showConfirmModal(onConfirm) {
    customModal.style.display = "flex";

    const handleOk = () => {
      cleanup();
      onConfirm(); // Gọi hàm submit form
    };

    const handleCancel = () => {
      cleanup();
    };

    const cleanup = () => {
      customModal.style.display = "none";
      btnOk.removeEventListener("click", handleOk);
      btnCancel.removeEventListener("click", handleCancel);
    };

    btnOk.addEventListener("click", handleOk);
    btnCancel.addEventListener("click", handleCancel);
  }

  return {
    alertEls,
    deleteForms,
    defaultForms,
    fadeOutAlert,
    setButtonLoading,
    showConfirmModal,
  };
})();

// ─── Controller ───────────────────────────────────────────────────────────────

const ProfileIndexController = (() => {
  function init() {
    _setupAutoHideAlerts();
    _setupDeleteAddressForms();
    _setupDefaultAddressForms();
  }

  function _setupAutoHideAlerts() {
    ProfileIndexView.alertEls.forEach((el) => {
      setTimeout(() => {
        ProfileIndexView.fadeOutAlert(el);
      }, ProfileIndexModel.AUTO_HIDE_DELAY);
    });
  }

  /** Dùng Custom Modal trước khi xóa địa chỉ */
  function _setupDeleteAddressForms() {
    ProfileIndexView.deleteForms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        e.preventDefault(); // Chặn hành vi submit mặc định

        // Gọi Custom Modal
        ProfileIndexView.showConfirmModal(() => {
          const btn = form.querySelector('button[type="submit"]');
          ProfileIndexView.setButtonLoading(btn, "Đang xóa…");
          form.submit(); // Submit form thực sự sau khi bấm OK
        });
      });
    });
  }

  function _setupDefaultAddressForms() {
    ProfileIndexView.defaultForms.forEach((form) => {
      form.addEventListener("submit", () => {
        const btn = form.querySelector('button[type="submit"]');
        ProfileIndexView.setButtonLoading(btn, "Đang đặt…");
      });
    });
  }

  return { init };
})();

// ─── Boot ─────────────────────────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", () => {
  ProfileIndexController.init();
});

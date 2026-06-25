const modal = document.getElementById("deleteModal");

document.querySelectorAll(".delete-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    document.getElementById("deleteUserId").value = btn.dataset.id;
    document.getElementById("deleteUserName").textContent = btn.dataset.name;
    modal.style.display = "flex";
  });
});

document.getElementById("cancelDelete").addEventListener("click", () => {
  modal.style.display = "none";
});

modal.addEventListener("click", (e) => {
  if (e.target === modal) modal.style.display = "none";
});

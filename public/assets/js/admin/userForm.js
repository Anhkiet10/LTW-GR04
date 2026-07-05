const pwdInput = document.getElementById("password");
const eyeIcon = document.getElementById("eyeIcon");

document.querySelector(".toggle-password").addEventListener("click", () => {
  const show = pwdInput.type === "password";
  pwdInput.type = show ? "text" : "password";
  eyeIcon.className = show ? "fas fa-eye-slash" : "fas fa-eye";
});

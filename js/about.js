// js/about.js
function updateLoginButton() {
  const username = localStorage.getItem("username");
  const loginBtn = document.getElementById("loginBtn");
  if (username) {
    loginBtn.textContent = `👋 ${username} | Đăng xuất`;
    loginBtn.classList.remove("btn-primary");
    loginBtn.classList.add("btn-danger");
  } else {
    loginBtn.textContent = "Đăng nhập";
    loginBtn.classList.remove("btn-danger");
    loginBtn.classList.add("btn-primary");
  }
}

function handleLoginClick() {
  const username = localStorage.getItem("username");
  if (username) {
    if (confirm("Bạn có chắc muốn đăng xuất không?")) {
      localStorage.removeItem("username");
      localStorage.removeItem("password");
      alert("Đã đăng xuất thành công!");
      updateLoginButton();
    }
  } else {
    window.location.href = "login.html";
  }
}

// Hiệu ứng scroll cho section
function initScrollAnimations() {
  const sections = document.querySelectorAll(
    ".mission-section, .history-section, .values-section"
  );

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = "1";
          entry.target.style.transform = "translateY(0)";
        }
      });
    },
    { threshold: 0.1 }
  );

  sections.forEach((section) => {
    section.style.opacity = "0";
    section.style.transform = "translateY(30px)";
    section.style.transition = "all 0.6s ease-out";
    observer.observe(section);
  });
}

document.addEventListener("DOMContentLoaded", function () {
  updateLoginButton();
  document
    .getElementById("loginBtn")
    .addEventListener("click", handleLoginClick);
  initScrollAnimations();
});

// js/contact.js
document.addEventListener("DOMContentLoaded", function () {
  updateLoginButton();
  document
    .getElementById("loginBtn")
    .addEventListener("click", handleLoginClick);
  document
    .getElementById("contactForm")
    .addEventListener("submit", handleContactForm);
  initFAQ();
  initSmoothScroll();
  updateCartCount();
});

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
      localStorage.removeItem("isLoggedIn");
      localStorage.removeItem("user_id");
      localStorage.removeItem("user_role");
      alert("Đã đăng xuất thành công!");
      updateLoginButton();
      updateCartCount();
    }
  } else {
    window.location.href = "login.php";
  }
}

function updateCartCount() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  const cartCount = document.getElementById("cartCount");
  if (cartCount) {
    cartCount.textContent = cart.length;
  }
}

// Contact form handling
function handleContactForm(event) {
  event.preventDefault();

  const formData = {
    name: document.getElementById("name").value,
    phone: document.getElementById("phone").value,
    email: document.getElementById("email").value,
    subject: document.getElementById("subject").value,
    message: document.getElementById("message").value,
  };

  // Validate form
  if (
    !formData.name ||
    !formData.phone ||
    !formData.email ||
    !formData.subject ||
    !formData.message
  ) {
    alert("Vui lòng điền đầy đủ thông tin!");
    return;
  }

  if (!validateEmail(formData.email)) {
    alert("Email không hợp lệ!");
    return;
  }

  if (!validatePhone(formData.phone)) {
    alert("Số điện thoại không hợp lệ!");
    return;
  }

  // Simulate sending data
  showLoading();

  setTimeout(() => {
    hideLoading();
    alert(
      "✅ Cảm ơn bạn! Tin nhắn đã được gửi thành công.\nChúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất."
    );
    document.getElementById("contactForm").reset();
  }, 2000);
}

// Validation functions
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function validatePhone(phone) {
  const re = /(03|05|07|08|09|01[2|6|8|9])+([0-9]{8})\b/;
  return re.test(phone.replace(/\s/g, ""));
}

// Loading functions
function showLoading() {
  const btn = document.querySelector(".btn-send");
  const originalText = btn.textContent;
  btn.innerHTML =
    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang gửi...';
  btn.disabled = true;
}

function hideLoading() {
  const btn = document.querySelector(".btn-send");
  btn.textContent = "Gửi tin nhắn";
  btn.disabled = false;
}

// FAQ functionality
function initFAQ() {
  const faqItems = document.querySelectorAll(".faq-item");

  faqItems.forEach((item) => {
    item.style.cursor = "pointer";
    item.addEventListener("click", function () {
      this.classList.toggle("active");
    });
  });
}

// Smooth scroll
function initSmoothScroll() {
  const links = document.querySelectorAll('a[href^="#"]');

  links.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href");
      const targetElement = document.querySelector(targetId);

      if (targetElement) {
        targetElement.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });
}

// js/menu.js - HOÀN CHỈNH
document.addEventListener("DOMContentLoaded", function () {
  // Initialize all functionality
  initLoginButton();
  initCartCount();
  initFilter();
  initQuickOrder();
  initQuickAddToCart();
  initImageErrorHandling();
});

// Login functionality
function initLoginButton() {
  updateLoginButton();

  const loginBtn = document.getElementById("loginBtn");
  if (loginBtn) {
    loginBtn.addEventListener("click", handleLoginClick);
  }
}

function updateLoginButton() {
  const username = localStorage.getItem("username");
  const loginBtn = document.getElementById("loginBtn");

  if (!loginBtn) return;

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
      alert("Đã đăng xuất thành công!");
      updateLoginButton();
      updateCartCount();
    }
  } else {
    window.location.href = "login.php";
  }
}

// Cart functionality
function initCartCount() {
  updateCartCount();
}

function updateCartCount() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  const cartCount = document.getElementById("cartCount");

  if (cartCount) {
    cartCount.textContent = cart.length;
  }
}

// Filter functionality
function initFilter() {
  const filterBtns = document.querySelectorAll(".filter-btn");

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      // Update active button
      filterBtns.forEach((b) => b.classList.remove("active"));
      this.classList.add("active");

      const filter = this.getAttribute("data-filter");
      applyFilter(filter);
    });
  });
}

function applyFilter(filter) {
  const menuItems = document.querySelectorAll(".menu-item");
  const categories = document.querySelectorAll(".menu-category");

  // Filter items
  menuItems.forEach((item) => {
    const itemCategory = item.getAttribute("data-category");
    const isVisible = filter === "all" || filter === itemCategory;

    if (isVisible) {
      item.classList.remove("hidden");
    } else {
      item.classList.add("hidden");
    }
  });

  // Filter categories
  categories.forEach((category) => {
    const categoryType = category.getAttribute("data-category");
    const categoryItems = category.querySelectorAll(".menu-item");
    const hasVisibleItems = Array.from(categoryItems).some(
      (item) => !item.classList.contains("hidden")
    );

    if (filter === "all" || filter === categoryType) {
      if (hasVisibleItems) {
        category.classList.remove("hidden");
      } else {
        category.classList.add("hidden");
      }
    } else {
      category.classList.add("hidden");
    }
  });
}

// Quick order functionality
function initQuickOrder() {
  const orderButtons = document.querySelectorAll(".btn-order");

  orderButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      // Check if user is logged in
      if (localStorage.getItem("isLoggedIn") !== "true") {
        e.preventDefault();
        alert("Bạn cần đăng nhập để đặt món!");
        window.location.href = "login.php";
        return;
      }

      // Add loading effect
      showButtonLoading(this);
    });
  });
}

function showButtonLoading(button) {
  const originalText = button.textContent;
  button.textContent = "Đang xử lý...";
  button.disabled = true;

  setTimeout(() => {
    button.textContent = originalText;
    button.disabled = false;
  }, 1000);
}

// Quick add to cart functionality
function initQuickAddToCart() {
  const menuItems = document.querySelectorAll(".menu-item");

  menuItems.forEach((item) => {
    const card = item.querySelector(".card");
    let tapCount = 0;
    let tapTimer;

    card.addEventListener("click", function (e) {
      // Ignore clicks on order button
      if (e.target.classList.contains("btn-order")) return;

      tapCount++;

      if (tapCount === 1) {
        tapTimer = setTimeout(function () {
          tapCount = 0;
        }, 300);
      } else if (tapCount === 2) {
        clearTimeout(tapTimer);
        tapCount = 0;
        handleQuickAddToCart(this);
      }
    });
  });
}

function handleQuickAddToCart(cardElement) {
  // Check login
  if (localStorage.getItem("isLoggedIn") !== "true") {
    alert("Bạn cần đăng nhập để thêm vào giỏ hàng!");
    return;
  }

  // Get item details
  const itemName = cardElement.querySelector(".card-title").textContent;
  const itemPrice = parseInt(
    cardElement.querySelector(".card-text").textContent.replace(/[^\d]/g, "")
  );
  const itemImage = cardElement.querySelector(".card-img-top").src;

  // Create cart item (customer info will be added at checkout)
  const cartItem = {
    name: itemName,
    img: itemImage,
    quantity: 1,
    size: "M",
    price: itemPrice,
    total: itemPrice,
    addedAt: new Date().toISOString(),
  };

  // Add to cart
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  cart.push(cartItem);
  localStorage.setItem("cart", JSON.stringify(cart));

  // Update UI
  updateCartCount();
  showQuickAddNotification(itemName);
}

function showQuickAddNotification(itemName) {
  // Create notification element
  const notification = document.createElement("div");
  notification.className = "alert alert-success position-fixed";
  notification.style.cssText = `
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        min-width: 280px;
        text-align: center;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    `;
  notification.innerHTML = `
        ✅ Đã thêm <strong>${itemName}</strong> vào giỏ hàng!
    `;

  document.body.appendChild(notification);

  // Auto remove after 2 seconds
  setTimeout(() => {
    notification.style.opacity = "0";
    notification.style.transform = "translateX(-50%) translateY(-20px)";
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 2000);
}

// Image error handling
function initImageErrorHandling() {
  const images = document.querySelectorAll('img[loading="lazy"]');

  images.forEach((img) => {
    img.addEventListener("error", function () {
      this.src = "database/AnhDoAn/BanhTranBo.jpg";
    });

    // Preload important images
    if (img.getAttribute("src").includes("BanhTranBo")) {
      const preloadLink = document.createElement("link");
      preloadLink.rel = "preload";
      preloadLink.as = "image";
      preloadLink.href = img.getAttribute("src");
      document.head.appendChild(preloadLink);
    }
  });
}

// Handle page visibility changes
document.addEventListener("visibilitychange", function () {
  if (!document.hidden) {
    updateCartCount();
  }
});

// Handle beforeunload for cleanup
window.addEventListener("beforeunload", function () {
  // Cleanup any pending timeouts or intervals if needed
});

// Export functions for global access (if needed)
window.menuApp = {
  updateLoginButton,
  updateCartCount,
  applyFilter,
};

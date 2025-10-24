// js/menu.js
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

// Filter functionality
function initFilter() {
  const filterBtns = document.querySelectorAll(".filter-btn");
  const menuItems = document.querySelectorAll(".menu-item");
  const categories = document.querySelectorAll(".menu-category");

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      // Update active button
      filterBtns.forEach((b) => b.classList.remove("active"));
      this.classList.add("active");

      const filter = this.getAttribute("data-filter");

      // Filter items
      menuItems.forEach((item) => {
        const itemCategory = item.getAttribute("data-category");

        if (filter === "all" || filter === itemCategory) {
          item.classList.remove("hidden");
        } else {
          item.classList.add("hidden");
        }
      });

      // Filter categories
      categories.forEach((category) => {
        const categoryType = category.getAttribute("data-category");
        const hasVisibleItems = Array.from(
          category.querySelectorAll(".menu-item")
        ).some((item) => !item.classList.contains("hidden"));

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
    });
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
        window.location.href = "login.html";
        return;
      }

      // Add loading effect
      const originalText = this.textContent;
      this.textContent = "Đang xử lý...";
      this.disabled = true;

      setTimeout(() => {
        this.textContent = originalText;
        this.disabled = false;
      }, 1000);
    });
  });
}

// Search functionality
function initSearch() {
  // Create search box
  const searchBox = document.createElement("div");
  searchBox.className = "search-box";
  searchBox.innerHTML = `
        <input type="text" class="form-control" placeholder="🔍 Tìm kiếm món ăn..." id="menuSearch">
    `;

  const menuSection = document.querySelector(".menu-section .container");
  menuSection.insertBefore(searchBox, menuSection.firstChild);

  const searchInput = document.getElementById("menuSearch");

  searchInput.addEventListener("input", function () {
    const searchTerm = this.value.toLowerCase().trim();
    const menuItems = document.querySelectorAll(".menu-item");
    const categories = document.querySelectorAll(".menu-category");

    let hasVisibleItems = false;

    menuItems.forEach((item) => {
      const itemName = item
        .querySelector(".card-title")
        .textContent.toLowerCase();
      const itemCategory = item.getAttribute("data-category");

      if (itemName.includes(searchTerm)) {
        item.classList.remove("hidden");
        hasVisibleItems = true;
      } else {
        item.classList.add("hidden");
      }
    });

    // Show/hide categories based on visible items
    categories.forEach((category) => {
      const categoryType = category.getAttribute("data-category");
      const categoryItems = category.querySelectorAll(".menu-item");
      const hasVisibleCategoryItems = Array.from(categoryItems).some(
        (item) => !item.classList.contains("hidden")
      );

      if (hasVisibleCategoryItems) {
        category.classList.remove("hidden");
      } else {
        category.classList.add("hidden");
      }
    });

    // Update filter buttons if searching
    if (searchTerm) {
      document.querySelectorAll(".filter-btn").forEach((btn) => {
        btn.classList.remove("active");
      });
      document
        .querySelector('.filter-btn[data-filter="all"]')
        .classList.add("active");
    }
  });
}

// Add to cart directly from menu
function initQuickAddToCart() {
  const menuItems = document.querySelectorAll(".menu-item");

  menuItems.forEach((item) => {
    const card = item.querySelector(".card");

    card.addEventListener("dblclick", function () {
      if (localStorage.getItem("isLoggedIn") !== "true") {
        alert("Bạn cần đăng nhập để thêm vào giỏ hàng!");
        return;
      }

      const itemName = this.querySelector(".card-title").textContent;
      const itemPrice = this.querySelector(".card-text").textContent;
      const itemImage = this.querySelector(".card-img-top").src;

      // Create quick cart item
      const quickItem = {
        name: itemName,
        price: itemPrice,
        img: itemImage,
        quantity: 1,
        size: "M",
        customer: {
          name: localStorage.getItem("username") || "Khách hàng",
          phone: "",
          address: "",
          note: "Thêm nhanh từ menu",
        },
      };

      // Add to cart
      const cart = JSON.parse(localStorage.getItem("cart")) || [];
      cart.push(quickItem);
      localStorage.setItem("cart", JSON.stringify(cart));

      // Show confirmation
      showQuickAddNotification(itemName);
    });
  });
}

function showQuickAddNotification(itemName) {
  const notification = document.createElement("div");
  notification.className = "alert alert-success position-fixed";
  notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 1000;
        min-width: 300px;
    `;
  notification.innerHTML = `
        ✅ Đã thêm <strong>${itemName}</strong> vào giỏ hàng!
        <a href="cart.html" class="alert-link">Xem giỏ hàng</a>
    `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.remove();
  }, 3000);
}

// Initialize everything when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  updateLoginButton();
  document
    .getElementById("loginBtn")
    .addEventListener("click", handleLoginClick);
  initFilter();
  initQuickOrder();
  initSearch();
  initQuickAddToCart();
});

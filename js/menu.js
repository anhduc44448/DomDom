// js/menu.js - FIXED VERSION
document.addEventListener("DOMContentLoaded", function () {
  updateLoginButton();
  loadMenu();

  const loginBtn = document.getElementById("loginBtn");
  if (loginBtn) loginBtn.addEventListener("click", handleLoginClick);
});

function updateLoginButton() {
  const username = localStorage.getItem("username");
  const loginBtn = document.getElementById("loginBtn");
  if (!loginBtn) return;

  loginBtn.textContent = username ? `👋 ${username} | Đăng xuất` : "Đăng nhập";
  loginBtn.className = username ? "btn btn-danger" : "btn btn-primary";
}

function handleLoginClick() {
  const username = localStorage.getItem("username");
  if (username) {
    if (confirm("Bạn có chắc muốn đăng xuất?")) {
      localStorage.clear();
      alert("Đã đăng xuất!");
      updateLoginButton();
    }
  } else {
    window.location.href = "login.php";
  }
}

async function loadMenu() {
  try {
    const response = await fetch("api/get_products.php");
    const result = await response.json();

    if (result.success) {
      displayMenu(result.data);
    } else {
      showDefaultMenu();
    }
  } catch (error) {
    showDefaultMenu();
  }
}

function displayMenu(products) {
  const container = document.getElementById("menuContainer");
  if (!container) return;

  // Đảm bảo mỗi product có ID
  products = products.map((p) => ({
    ...p,
    id: p.id || 1,
  }));

  // Nhóm theo category
  const categories = {};
  products.forEach((product) => {
    const catId = product.category_id || 1;
    if (!categories[catId]) {
      categories[catId] = {
        name: product.category_name || (catId <= 5 ? "Đồ ăn" : "Thức uống"),
        products: [],
      };
    }
    categories[catId].products.push(product);
  });

  let html = "";

  // Đồ ăn (category 1)
  if (categories[1] && categories[1].products.length > 0) {
    html += `<div class="menu-category" data-category="1">
            <h3 class="category-title">🍽️ ${categories[1].name}</h3>
            <div class="row g-3">`;

    categories[1].products.forEach((product) => {
      html += createProductCard(product);
    });

    html += `</div></div>`;
  }

  // Thức uống (category 2+)
  Object.keys(categories)
    .filter((id) => id != 1)
    .forEach((catId) => {
      html += `<div class="menu-category" data-category="2">
                <h3 class="category-title">🥤 ${categories[catId].name}</h3>
                <div class="row g-3">`;

      categories[catId].products.forEach((product) => {
        html += createProductCard(product);
      });

      html += `</div></div>`;
    });

  container.innerHTML = html;
  initFilter();
  initQuickOrder();
}

function createProductCard(product) {
  const price = formatPrice(product.price);
  const imageUrl = product.image_path || "database/AnhDoAn/BanhTranBo.jpg";
  const productId = product.id || 1;

  return `
        <div class="col-6 col-md-4 col-lg-3 menu-item" data-category="${
          product.category_id <= 5 ? "1" : "2"
        }">
            <div class="card h-100 shadow-sm">
                <img src="${imageUrl}" class="card-img-top" alt="${
    product.name
  }" 
                     onerror="this.src='database/AnhDoAn/BanhTranBo.jpg'">
                <div class="card-body">
                    <h5 class="card-title">${product.name}</h5>
                    <p class="card-text text-danger fw-bold">${price}đ</p>
                    <a href="order.html?id=${productId}" class="btn btn-order w-100">Đặt món</a>
                </div>
            </div>
        </div>
    `;
}

function showDefaultMenu() {
  const container = document.getElementById("menuContainer");
  if (!container) return;

  const defaultProducts = [
    {
      id: 1,
      name: "Bánh tráng bơ",
      price: 25000,
      category_id: 1,
      image_path: "database/AnhDoAn/BanhTranBo.jpg",
    },
    {
      id: 2,
      name: "Trà đào cam sả",
      price: 32000,
      category_id: 2,
      image_path: "database/AnhDoUong/tra_dao_cam_sa.png",
    },
    {
      id: 3,
      name: "Trà sữa trân châu đường đen",
      price: 35000,
      category_id: 2,
      image_path: "database/AnhDoUong/tra_sua_tran_chau_duong_den.png",
    },
  ];

  displayMenu(defaultProducts);
}

function initFilter() {
  const filterBtns = document.querySelectorAll(".filter-btn");
  if (!filterBtns.length) return;

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      filterBtns.forEach((b) => b.classList.remove("active"));
      this.classList.add("active");
      applyFilter(this.getAttribute("data-filter"));
    });
  });
}

function applyFilter(filter) {
  const menuItems = document.querySelectorAll(".menu-item");
  const categories = document.querySelectorAll(".menu-category");

  menuItems.forEach((item) => {
    const isVisible =
      filter === "all" || filter === item.getAttribute("data-category");
    item.style.display = isVisible ? "block" : "none";
  });

  categories.forEach((category) => {
    const categoryItems = category.querySelectorAll(".menu-item");
    const hasVisible = Array.from(categoryItems).some(
      (item) => item.style.display !== "none"
    );
    category.style.display = hasVisible ? "block" : "none";
  });
}

function initQuickOrder() {
  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("btn-order")) {
      if (localStorage.getItem("isLoggedIn") !== "true") {
        e.preventDefault();
        alert("Bạn cần đăng nhập để đặt món!");
        window.location.href = "login.php";
      }
    }
  });
}

function formatPrice(price) {
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

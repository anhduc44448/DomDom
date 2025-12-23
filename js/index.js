// js/index.js - HOÀN CHỈNH
document.addEventListener("DOMContentLoaded", function () {
  updateLoginButton();
  document
    .getElementById("loginBtn")
    .addEventListener("click", handleLoginClick);

  // Load best sellers từ API
  loadBestSellers();
  updateCartCount();
  updateStats();
});

// Login functionality
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
      localStorage.removeItem("user_id");
      localStorage.removeItem("user_role");
      alert("Đã đăng xuất thành công!");
      updateLoginButton();
      updateCartCount();
      window.location.reload();
    }
  } else {
    window.location.href = "login.php";
  }
}

// Load best sellers từ API
async function loadBestSellers() {
  try {
    const response = await fetch("api/get_products.php?type=best_seller");
    const result = await response.json();

    if (result.success && result.data.length > 0) {
      displayBestSellers(result.data);
    } else {
      showDefaultProducts();
    }
  } catch (error) {
    console.error("Lỗi tải sản phẩm:", error);
    showDefaultProducts();
  }
}

function displayBestSellers(products) {
  const container = document.getElementById("bestSellersContainer");
  if (!container) return;

  let html = "";

  // Chỉ hiển thị tối đa 3 sản phẩm
  const displayProducts = products.slice(0, 3);

  displayProducts.forEach((product) => {
    const price = formatPrice(product.price);
    const imageUrl = product.image_path || "database/AnhDoAn/BanhTranBo.jpg";

    html += `
            <div class="col-md-4 mb-4">
                <div class="card best-seller-card border-warning">
                    <img src="${imageUrl}" class="card-img-top" alt="${product.name}" 
                         onerror="this.src='database/AnhDoAn/BanhTranBo.jpg'">
                    <div class="card-body text-center">
                        <h5 class="card-title">${product.name}</h5>
                        <span class="badge bg-warning text-dark mb-2">Best Seller</span>
                        <p class="card-text text-danger fw-bold">${price}đ</p>
                        <a href="order.html?id=${product.id}" class="btn btn-order w-100">Đặt món</a>
                    </div>
                </div>
            </div>
        `;
  });

  container.innerHTML = html;
}

function showDefaultProducts() {
  const container = document.getElementById("bestSellersContainer");
  if (!container) return;

  container.innerHTML = `
        <div class="col-md-4 mb-4">
            <div class="card best-seller-card border-warning">
                <img src="database/AnhDoAn/BanhTranBo.jpg" class="card-img-top" alt="Bánh tráng bơ">
                <div class="card-body text-center">
                    <h5 class="card-title">Bánh tráng bơ</h5>
                    <span class="badge bg-warning text-dark mb-2">Best Seller</span>
                    <p class="card-text text-danger fw-bold">25.000đ</p>
                    <a href="order.html?id=1" class="btn btn-order w-100">Đặt món</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card best-seller-card border-warning">
                <img src="database/AnhDoUong/tra_dao_cam_sa.png" class="card-img-top" alt="Trà đào cam sả">
                <div class="card-body text-center">
                    <h5 class="card-title">Trà đào cam sả</h5>
                    <span class="badge bg-warning text-dark mb-2">Best Seller</span>
                    <p class="card-text text-danger fw-bold">32.000đ</p>
                    <a href="order.html?id=11" class="btn btn-order w-100">Đặt món</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card best-seller-card border-warning">
                <img src="database/AnhDoUong/tra_sua_tran_chau_duong_den.png" class="card-img-top" 
                     alt="Trà sữa trân châu đường đen">
                <div class="card-body text-center">
                    <h5 class="card-title">Trà sữa trân châu đường đen</h5>
                    <span class="badge bg-warning text-dark mb-2">Best Seller</span>
                    <p class="card-text text-danger fw-bold">35.000đ</p>
                    <a href="order.html?id=12" class="btn btn-order w-100">Đặt món</a>
                </div>
            </div>
        </div>
    `;
}

// Update cart count
function updateCartCount() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  const cartCount = document.getElementById("cartCount");

  if (cartCount) {
    cartCount.textContent = cart.length;
  }
}

// Update statistics
async function updateStats() {
  try {
    const response = await fetch("api/get_products.php");
    const result = await response.json();

    if (result.success) {
      const productCount = document.getElementById("productCount");
      if (productCount) {
        productCount.textContent = result.data.length + "+";
      }
    }
  } catch (error) {
    console.error("Lỗi tải thống kê:", error);
  }
}

// Format price
function formatPrice(price) {
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Handle page visibility changes
document.addEventListener("visibilitychange", function () {
  if (!document.hidden) {
    updateCartCount();
  }
});

// Export functions for global access
window.indexApp = {
  updateLoginButton,
  updateCartCount,
  loadBestSellers,
};

// js/order.js - FIXED VERSION
document.addEventListener("DOMContentLoaded", function () {
  updateLoginButton();
  loadProduct();

  document
    .getElementById("loginBtn")
    .addEventListener("click", handleLoginClick);
});

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
    if (confirm("Bạn có chắc muốn đăng xuất?")) {
      localStorage.clear();
      alert("Đã đăng xuất!");
      updateLoginButton();
      window.location.reload();
    }
  } else {
    window.location.href = "login.php";
  }
}

async function loadProduct() {
  const urlParams = new URLSearchParams(window.location.search);
  const productId = urlParams.get("id") || 1;

  try {
    const response = await fetch(`api/get_product_by_id.php?id=${productId}`);
    const result = await response.json();

    if (result.success) {
      displayProduct(result.data);
      initOrderControls(result.data);
    }
  } catch (error) {
    showProductNotFound();
  }
}

function displayProduct(product) {
  const container = document.getElementById("orderContent");
  if (!container) return;

  const price = formatPrice(product.price);
  const imageUrl = product.image_path || "database/AnhDoAn/BanhTranBo.jpg";

  container.innerHTML = `
        <div class="order-header">
            <h2>Đặt món: <span class="item-name">${product.name}</span></h2>
            <div class="item-image">
                <img src="${imageUrl}" alt="${product.name}" 
                     onerror="this.src='database/AnhDoAn/BanhTranBo.jpg'">
            </div>
        </div>

        <form id="orderForm" class="order-form">
            <div class="form-section">
                <h4>Thông tin món</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Số lượng</label>
                            <div class="quantity-control">
                                <button type="button" class="quantity-btn" id="decreaseQty">-</button>
                                <input type="number" class="form-control" id="quantity" value="1" min="1" max="10">
                                <button type="button" class="quantity-btn" id="increaseQty">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Kích cỡ</label>
                            <div class="size-options">
                                <label class="size-option">
                                    <input type="radio" name="size" value="S">
                                    <span class="size-label">Nhỏ</span>
                                    <span class="size-price">+0đ</span>
                                </label>
                                <label class="size-option active">
                                    <input type="radio" name="size" value="M" checked>
                                    <span class="size-label">Vừa</span>
                                    <span class="size-price">+0đ</span>
                                </label>
                                <label class="size-option">
                                    <input type="radio" name="size" value="L">
                                    <span class="size-label">Lớn</span>
                                    <span class="size-price">+5.000đ</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4>Tóm tắt đơn hàng</h4>
                <div class="order-summary">
                    <div class="summary-item">
                        <span>Đơn giá:</span>
                        <span id="basePrice">${price}đ</span>
                    </div>
                    <div class="summary-item">
                        <span>Phí size:</span>
                        <span id="sizePrice">0đ</span>
                    </div>
                    <div class="summary-item">
                        <span>Số lượng:</span>
                        <span id="summaryQuantity">1</span>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-total">
                        <span>Tổng cộng:</span>
                        <span id="totalPrice" class="total-price">${price}đ</span>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-order btn-lg">Thêm vào giỏ hàng</button>
                <a href="menu.html" class="btn btn-secondary btn-lg">← Quay về thực đơn</a>
            </div>
        </form>
    `;
}

function initOrderControls(product) {
  const basePrice = product.price;
  let quantity = 1;
  let size = "M";

  // Quantity controls
  document.getElementById("decreaseQty").addEventListener("click", () => {
    if (quantity > 1) {
      quantity--;
      document.getElementById("quantity").value = quantity;
      updateTotalPrice();
    }
  });

  document.getElementById("increaseQty").addEventListener("click", () => {
    if (quantity < 10) {
      quantity++;
      document.getElementById("quantity").value = quantity;
      updateTotalPrice();
    }
  });

  document.getElementById("quantity").addEventListener("change", function () {
    let value = parseInt(this.value);
    if (value < 1) this.value = 1;
    if (value > 10) this.value = 10;
    quantity = parseInt(this.value);
    updateTotalPrice();
  });

  // Size selection
  document.querySelectorAll(".size-option").forEach((option) => {
    option.addEventListener("click", function () {
      document
        .querySelectorAll(".size-option")
        .forEach((opt) => opt.classList.remove("active"));
      this.classList.add("active");
      this.querySelector('input[type="radio"]').checked = true;
      size = this.querySelector('input[type="radio"]').value;
      updateTotalPrice();
    });
  });

  // Form submission
  document.getElementById("orderForm").addEventListener("submit", function (e) {
    e.preventDefault();
    addToCart(product);
  });

  function updateTotalPrice() {
    const sizeMultiplier = size === "L" ? 1.2 : 1;
    const sizeExtra = size === "L" ? basePrice * 0.2 : 0;
    const total = basePrice * sizeMultiplier * quantity;

    document.getElementById("basePrice").textContent =
      formatPrice(basePrice) + "đ";
    document.getElementById("sizePrice").textContent =
      formatPrice(sizeExtra * quantity) + "đ";
    document.getElementById("summaryQuantity").textContent = quantity;
    document.getElementById("totalPrice").textContent =
      formatPrice(total) + "đ";
  }

  updateTotalPrice();
}

function addToCart(product) {
  if (localStorage.getItem("isLoggedIn") !== "true") {
    alert("Bạn cần đăng nhập để thêm vào giỏ hàng!");
    window.location.href = "login.php";
    return;
  }

  const quantity = parseInt(document.getElementById("quantity").value);
  const size = document.querySelector('input[name="size"]:checked').value;
  const basePrice = product.price;
  const sizeMultiplier = size === "L" ? 1.2 : 1;
  const total = basePrice * sizeMultiplier * quantity;

  const cartItem = {
    product_id: product.id,
    name: product.name,
    img: product.image_path || "database/AnhDoAn/BanhTranBo.jpg",
    quantity: quantity,
    size: size,
    price: basePrice,
    total: total,
    addedAt: new Date().toISOString(),
  };

  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  cart.push(cartItem);
  localStorage.setItem("cart", JSON.stringify(cart));

  alert(`✅ Đã thêm ${product.name} vào giỏ hàng!`);
  setTimeout(() => {
    window.location.href = "menu.html";
  }, 1000);
}

function showProductNotFound() {
  const container = document.getElementById("orderContent");
  if (!container) return;

  container.innerHTML = `
        <div class="text-center py-5">
            <h3>Không tìm thấy sản phẩm</h3>
            <p>Vui lòng chọn món khác từ thực đơn</p>
            <a href="menu.html" class="btn btn-primary">← Quay về thực đơn</a>
        </div>
    `;
}

function formatPrice(price) {
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

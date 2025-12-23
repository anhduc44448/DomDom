// js/cart.js - HOÀN CHỈNH
let cart = [];

document.addEventListener("DOMContentLoaded", function () {
  updateLoginButton();
  loadCart();

  document
    .getElementById("loginBtn")
    .addEventListener("click", handleLoginClick);
  document
    .getElementById("submitOrderBtn")
    .addEventListener("click", submitOrder);
});

// Login functionality
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
    if (confirm("Bạn có chắc muốn đăng xuất?")) {
      localStorage.removeItem("username");
      localStorage.removeItem("isLoggedIn");
      localStorage.removeItem("user_id");
      localStorage.removeItem("user_role");
      alert("Đã đăng xuất!");
      updateLoginButton();
      window.location.reload();
    }
  } else {
    window.location.href = "login.php";
  }
}

// Load and render cart
function loadCart() {
  cart = JSON.parse(localStorage.getItem("cart")) || [];
  renderCart();
  updateCartCount();
}

function renderCart() {
  const cartList = document.getElementById("cartList");
  const emptyCart = document.getElementById("emptyCart");
  const customerForm = document.getElementById("customerForm");
  const orderSummary = document.getElementById("orderSummary");

  if (!cartList || !emptyCart || !customerForm || !orderSummary) return;

  if (cart.length === 0) {
    cartList.innerHTML = "";
    cartList.style.display = "none";
    customerForm.style.display = "none";
    orderSummary.style.display = "none";
    emptyCart.style.display = "block";
    return;
  }

  cartList.style.display = "block";
  customerForm.style.display = "block";
  orderSummary.style.display = "block";
  emptyCart.style.display = "none";

  let html = "";
  let total = 0;

  cart.forEach((item, idx) => {
    const itemTotal = item.price * item.quantity;
    total += itemTotal;

    html += `
            <div class="cart-item">
                <img src="${item.img}" alt="${
      item.name
    }" onerror="this.src='database/AnhDoAn/BanhTranBo.jpg'">
                <div class="cart-details">
                    <h5>${item.name}</h5>
                    <div class="cart-info">Kích cỡ: <b>${getSizeName(
                      item.size
                    )}</b></div>
                    <div class="cart-info">Số lượng: <b>${
                      item.quantity
                    }</b></div>
                    <div class="cart-price">${formatPrice(itemTotal)}đ</div>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(${idx}, -1)">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${idx}, 1)">+</button>
                    </div>
                </div>
                <button class="btn-remove" onclick="removeItem(${idx})">Xóa</button>
            </div>
        `;
  });

  cartList.innerHTML = html;
  document.getElementById("totalAmount").textContent = formatPrice(total) + "đ";

  // Auto-fill customer info
  const username = localStorage.getItem("username");
  if (username && !document.getElementById("customerName").value) {
    document.getElementById("customerName").value = username;
  }
}

// Update quantity
function updateQuantity(index, change) {
  cart[index].quantity += change;

  if (cart[index].quantity < 1) {
    cart[index].quantity = 1;
  }

  localStorage.setItem("cart", JSON.stringify(cart));
  renderCart();
  updateCartCount();
}

// Remove item
function removeItem(idx) {
  if (confirm("Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?")) {
    cart.splice(idx, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    renderCart();
    updateCartCount();
  }
}

// Submit order to API
async function submitOrder() {
  // Validate customer info
  const customerName = document.getElementById("customerName").value.trim();
  const customerTable = document.getElementById("customerTable").value.trim();

  if (!customerName) {
    alert("Vui lòng nhập tên người nhận!");
    document.getElementById("customerName").focus();
    return false;
  }

  if (!customerTable) {
    alert("Vui lòng chọn số bàn!");
    document.getElementById("customerTable").focus();
    return false;
  }

  // Check login
  if (localStorage.getItem("isLoggedIn") !== "true") {
    alert("Bạn cần đăng nhập để đặt hàng!");
    window.location.href = "login.php";
    return false;
  }

  const userId = localStorage.getItem("user_id");
  const customerNote = document.getElementById("customerNote").value.trim();

  // Prepare order data
  const orderData = {
    user_id: userId || null,
    customer_name: customerName,
    table_number: customerTable,
    customer_note: customerNote,
    items: cart.map((item) => ({
      product_name: item.name,
      quantity: item.quantity,
      size: item.size,
      unit_price: item.price,
      total_price: item.price * item.quantity,
    })),
    total_amount: calculateTotal(),
  };

  // Show loading
  const submitBtn = document.getElementById("submitOrderBtn");
  const originalText = submitBtn.textContent;
  submitBtn.textContent = "Đang xử lý...";
  submitBtn.disabled = true;

  try {
    const response = await fetch("api/create_order.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(orderData),
    });

    const result = await response.json();

    if (result.success) {
      // Clear cart
      localStorage.removeItem("cart");
      cart = [];

      // Show success message
      showOrderSuccess(result.order_id, customerTable);
    } else {
      throw new Error(result.message || "Lỗi tạo đơn hàng");
    }
  } catch (error) {
    alert("Lỗi: " + error.message);
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  }
}

// Show order success
function showOrderSuccess(orderId, tableNumber) {
  const cartContent = document.getElementById("cartContent");
  const customerForm = document.getElementById("customerForm");
  const orderSummary = document.getElementById("orderSummary");

  if (!cartContent || !customerForm || !orderSummary) return;

  cartContent.innerHTML = `
        <div class="order-success">
            <div style="font-size: 80px;">✅</div>
            <h3>Đặt hàng thành công!</h3>
            <p>Mã đơn hàng: <strong>#${orderId}</strong></p>
            <p>Nhân viên sẽ phục vụ tại bàn ${tableNumber}</p>
            <div class="mt-4">
                <button onclick="goToOrderSuccess(${orderId})" class="btn btn-order me-2">Xem chi tiết</button>
                <a href="menu.html" class="btn btn-secondary">Đặt thêm món</a>
            </div>
        </div>
    `;

  customerForm.style.display = "none";
  orderSummary.style.display = "none";
}

function goToOrderSuccess(orderId) {
  window.location.href = `order_success.html?order_id=${orderId}`;
}

// Helper functions
function calculateTotal() {
  return cart.reduce((total, item) => total + item.price * item.quantity, 0);
}

function getSizeName(size) {
  const sizes = { S: "Nhỏ", M: "Vừa", L: "Lớn" };
  return sizes[size] || size;
}

function formatPrice(price) {
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function updateCartCount() {
  const cartCount = document.getElementById("cartCount");
  if (cartCount) {
    cartCount.textContent = cart.length;
  }
}

// Export functions
window.cartApp = {
  updateLoginButton,
  loadCart,
  renderCart,
  updateQuantity,
  removeItem,
  submitOrder,
};

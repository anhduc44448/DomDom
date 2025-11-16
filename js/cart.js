// js/cart.js
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
      alert("Đã đăng xuất!");
      updateLoginButton();
      window.location.reload();
    }
  } else {
    window.location.href = "login.php";
  }
}

// Hiển thị giỏ hàng
function renderCart() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  const cartList = document.getElementById("cartList");
  const emptyCart = document.getElementById("emptyCart");
  const cartSummary = document.querySelector(".cart-summary");
  const totalAmount = document.getElementById("totalAmount");

  if (cart.length === 0) {
    cartList.style.display = "none";
    cartSummary.style.display = "none";
    emptyCart.style.display = "block";
    return;
  }

  cartList.style.display = "block";
  cartSummary.style.display = "block";
  emptyCart.style.display = "none";

  let html = "";
  let total = 0;

  cart.forEach((item, idx) => {
    const price = getItemPrice(item.name);
    const itemTotal = price * item.quantity;
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
                    <div class="cart-info">Người nhận: ${
                      item.customer.name
                    } | ĐT: ${item.customer.phone}</div>
                    <div class="cart-info">Địa chỉ: ${
                      item.customer.address
                    }</div>
                    ${
                      item.customer.note
                        ? `<div class="cart-info">Ghi chú: ${item.customer.note}</div>`
                        : ""
                    }
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
  totalAmount.textContent = formatPrice(total) + "đ";
}

// Lấy giá sản phẩm
function getItemPrice(itemName) {
  const prices = {
    "Bánh tráng bơ": 25000,
    "Bánh tráng kẹp dẻo": 28000,
    "Bánh tráng trộn Sài Gòn": 30000,
    "Khô bò trộn": 32000,
    "Da heo trộn": 27000,
    "Gỏi xoài trộn": 26000,
    "Mít trộn": 25000,
    "Ốc đinh trộn dừa": 35000,
    "Ốc thường trộn dừa": 33000,
    "Ram cuốn cải": 28000,
    "Ca cao đá": 30000,
    "Matcha đá xay": 38000,
    "Nước ép ổi": 28000,
    "Nước chanh": 22000,
    "Nước ép cà rốt": 28000,
    "Sữa tươi trân châu đường đen": 35000,
    "Trà đào cam sả": 32000,
    "Trà sữa trân châu đường đen": 35000,
  };
  return prices[itemName] || 25000;
}

// Định dạng giá
function formatPrice(price) {
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Lấy tên kích cỡ
function getSizeName(size) {
  const sizes = { S: "Nhỏ", M: "Vừa", L: "Lớn" };
  return sizes[size] || size;
}

// Cập nhật số lượng
function updateQuantity(index, change) {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  cart[index].quantity += change;

  if (cart[index].quantity < 1) {
    cart[index].quantity = 1;
  }

  localStorage.setItem("cart", JSON.stringify(cart));
  renderCart();
}

// Xóa sản phẩm
function removeItem(idx) {
  if (confirm("Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?")) {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart.splice(idx, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    renderCart();
  }
}

// Xác nhận đặt hàng
function confirmOrder() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  if (cart.length === 0) return;

  if (localStorage.getItem("isLoggedIn") !== "true") {
    alert("Bạn cần đăng nhập để đặt hàng!");
    window.location.href = "login.php";
    return;
  }

  if (
    confirm(
      "Xác nhận đặt hàng? Đơn hàng sẽ được xử lý trong thời gian sớm nhất."
    )
  ) {
    alert(
      "✅ Đặt hàng thành công! Cảm ơn bạn đã sử dụng dịch vụ Đom đóm quán."
    );
    localStorage.removeItem("cart");
    renderCart();
  }
}

document.addEventListener("DOMContentLoaded", function () {
  updateLoginButton();
  renderCart();

  document
    .getElementById("loginBtn")
    .addEventListener("click", handleLoginClick);
  document
    .getElementById("confirmOrderBtn")
    .addEventListener("click", confirmOrder);
});

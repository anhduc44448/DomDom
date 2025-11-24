// js/order.js
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

// Menu data với prices
const menu = {
  banhtrangbo: {
    name: "Bánh tráng bơ",
    price: 25000,
    image: "database/AnhDoAn/BanhTranBo.jpg",
  },
  banhtrangkepdeo: {
    name: "Bánh tráng kẹp dẻo",
    price: 28000,
    image: "database/AnhDoAn/BanhTrangKepDeo.jpg",
  },
  banhtrangtronsaigon: {
    name: "Bánh tráng trộn Sài Gòn",
    price: 30000,
    image: "database/AnhDoAn/BanhTranTronSaiGon.jpg",
  },
  khobotron: {
    name: "Khô bò trộn",
    price: 32000,
    image: "database/AnhDoAn/BoKhoTron.jpg",
  },
  daheotron: {
    name: "Da heo trộn",
    price: 27000,
    image: "database/AnhDoAn/DaHeoTron.jpg",
  },
  goixoaitron: {
    name: "Gỏi xoài trộn",
    price: 26000,
    image: "database/AnhDoAn/GoiXoaiTron.jpg",
  },
  mittron: {
    name: "Mít trộn",
    price: 25000,
    image: "database/AnhDoAn/MitTron.jpg",
  },
  ocdinhtrondua: {
    name: "Ốc đinh trộn dừa",
    price: 35000,
    image: "database/AnhDoAn/OcDinhTronDua.jpg",
  },
  octhuongtrondua: {
    name: "Ốc thường trộn dừa",
    price: 33000,
    image: "database/AnhDoAn/OcThuongTronDua.jpg",
  },
  ramcuoncai: {
    name: "Ram cuốn cải",
    price: 28000,
    image: "database/AnhDoAn/RomCuonCai.jpg",
  },
  cacaoda: {
    name: "Ca cao đá",
    price: 30000,
    image: "database/AnhDoUong/CaCaoDa.png",
  },
  matchadaxay: {
    name: "Matcha đá xay",
    price: 38000,
    image: "database/AnhDoUong/matchaDaXay.png",
  },
  nuocepoi: {
    name: "Nước ép ổi",
    price: 28000,
    image: "database/AnhDoUong/nuoc_ep_oi.png",
  },
  nuocchanh: {
    name: "Nước chanh",
    price: 22000,
    image: "database/AnhDoUong/NuocChanhTuoi.png",
  },
  nuocepcarot: {
    name: "Nước ép cà rốt",
    price: 28000,
    image: "database/AnhDoUong/NuocEpCaRot.png",
  },
  suatuoitranchauduongden: {
    name: "Sữa tươi trân châu đường đen",
    price: 35000,
    image: "database/AnhDoUong/sua_tuoi_tran_chau_duong_dene.png",
  },
  tradaocamsa: {
    name: "Trà đào cam sả",
    price: 32000,
    image: "database/AnhDoUong/tra_dao_cam_sa.png",
  },
  trasuatranchauduongden: {
    name: "Trà sữa trân châu đường đen",
    price: 35000,
    image: "database/AnhDoUong/tra_sua_tran_chau_duong_den.png",
  },
};

// Initialize order page
function initOrderPage() {
  // Check login status
  if (localStorage.getItem("isLoggedIn") !== "true") {
    alert("Bạn cần đăng nhập để đặt món!");
    window.location.href = "login.php";
    return;
  }

  // Get item from URL parameters
  const params = new URLSearchParams(window.location.search);
  const itemKey = params.get("item");

  if (!itemKey || !menu[itemKey]) {
    document.getElementById("itemName").innerText = "Không tìm thấy món này";
    return;
  }

  const item = menu[itemKey];

  // Display item information
  document.getElementById("itemName").innerText = item.name;
  document.getElementById("basePrice").textContent =
    formatPrice(item.price) + "đ";

  // Display item image
  const itemImageContainer = document.getElementById("itemImage");
  itemImageContainer.innerHTML = `<img src="${item.image}" alt="${item.name}" onerror="this.src='database/AnhDoAn/BanhTranBo.jpg'">`;

  // Update total price
  updateTotalPrice();
}

// Format price with thousand separators
function formatPrice(price) {
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Calculate total price
function updateTotalPrice() {
  const quantity = parseInt(document.getElementById("quantity").value);
  const basePrice = getBasePrice();
  const sizeMultiplier = getSizeMultiplier();

  const baseTotal = basePrice * quantity;
  const sizeExtra = (sizeMultiplier - 1) * basePrice * quantity;
  const total = baseTotal + sizeExtra;

  document.getElementById("basePrice").textContent =
    formatPrice(basePrice) + "đ";
  document.getElementById("sizePrice").textContent =
    formatPrice(sizeExtra) + "đ";
  document.getElementById("summaryQuantity").textContent = quantity;
  document.getElementById("totalPrice").textContent = formatPrice(total) + "đ";
}

// Get base price from current item
function getBasePrice() {
  const params = new URLSearchParams(window.location.search);
  const itemKey = params.get("item");
  return menu[itemKey]?.price || 0;
}

// Get size multiplier
function getSizeMultiplier() {
  const selectedSize = document.querySelector(
    'input[name="size"]:checked'
  ).value;
  const multipliers = { S: 1, M: 1, L: 1.2 }; // 20% extra for large
  return multipliers[selectedSize] || 1;
}

// Quantity controls
function initQuantityControls() {
  const quantityInput = document.getElementById("quantity");
  const decreaseBtn = document.getElementById("decreaseQty");
  const increaseBtn = document.getElementById("increaseQty");

  decreaseBtn.addEventListener("click", function () {
    let currentQty = parseInt(quantityInput.value);
    if (currentQty > 1) {
      quantityInput.value = currentQty - 1;
      updateTotalPrice();
    }
  });

  increaseBtn.addEventListener("click", function () {
    let currentQty = parseInt(quantityInput.value);
    if (currentQty < 10) {
      quantityInput.value = currentQty + 1;
      updateTotalPrice();
    }
  });

  quantityInput.addEventListener("change", function () {
    let value = parseInt(this.value);
    if (value < 1) this.value = 1;
    if (value > 10) this.value = 10;
    updateTotalPrice();
  });
}

// Size selection
function initSizeSelection() {
  const sizeOptions = document.querySelectorAll(".size-option");

  sizeOptions.forEach((option) => {
    option.addEventListener("click", function () {
      // Remove active class from all options
      sizeOptions.forEach((opt) => opt.classList.remove("active"));
      // Add active class to clicked option
      this.classList.add("active");
      // Check the radio input
      this.querySelector('input[type="radio"]').checked = true;
      // Update price
      updateTotalPrice();
    });
  });
}

// Form submission
function initOrderForm() {
  const orderForm = document.getElementById("orderForm");
  const submitBtn = orderForm.querySelector(".btn-order");

  orderForm.addEventListener("submit", function (e) {
    e.preventDefault();
    console.log("Form submit triggered");

    // Validate size selection first
    const sizeSelected = document.querySelector('input[name="size"]:checked');
    console.log("Size selected:", sizeSelected);

    if (!sizeSelected) {
      alert("Vui lòng chọn kích cỡ trước khi đặt hàng!");
      return false;
    }

    // Show loading state
    submitBtn.classList.add("loading");
    submitBtn.disabled = true;
    submitBtn.textContent = "Đang thêm vào giỏ...";

    // Process order
    setTimeout(() => {
      addToCart();
      submitBtn.classList.remove("loading");
      submitBtn.disabled = false;
    }, 1500);

    return false;
  });
}

// Add item to cart
function addToCart() {
  const params = new URLSearchParams(window.location.search);
  const itemKey = params.get("item");
  const item = menu[itemKey];

  if (!item) return;

  const cart = JSON.parse(localStorage.getItem("cart")) || [];

  const orderItem = {
    name: item.name,
    img: item.image,
    quantity: parseInt(document.getElementById("quantity").value),
    size: document.querySelector('input[name="size"]:checked').value,
    price: getBasePrice(),
    total: parseInt(
      document.getElementById("totalPrice").textContent.replace(/\./g, "")
    ),
    addedAt: new Date().toISOString(),
  };

  cart.push(orderItem);
  localStorage.setItem("cart", JSON.stringify(cart));

  // Show success message
  showSuccessMessage();
}

// Show success message and redirect
function showSuccessMessage() {
  const submitBtn = document.querySelector(".btn-order");

  submitBtn.textContent = "✅ Đã thêm vào giỏ hàng!";
  submitBtn.style.background = "#28a745";

  setTimeout(() => {
    // Redirect to cart page
    window.location.href = "cart.html";
  }, 1000);
}

// Customer info now handled at cart page

// Initialize everything when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  updateLoginButton();
  document
    .getElementById("loginBtn")
    .addEventListener("click", handleLoginClick);
  initOrderPage();
  initQuantityControls();
  initSizeSelection();
  initOrderForm();
});

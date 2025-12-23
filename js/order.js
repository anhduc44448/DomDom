// js/order.js - FIXED VERSION
document.addEventListener("DOMContentLoaded", function () {
  updateLoginButton();
  loadProduct();

  document
    .getElementById("loginBtn")
    .addEventListener("click", handleLoginClick);
});

function updateLoginButton() {
<<<<<<< HEAD
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
=======
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
		document.getElementById("itemName").innerText =
			"Không tìm thấy món này";
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

	// Update size prices labels
	updateSizeLabels(item.price);

	// Update total price
	updateTotalPrice();
}

// Update size labels based on base price
function updateSizeLabels(basePrice) {
	const sizeOptions = document.querySelectorAll('.size-option');
	sizeOptions.forEach(option => {
		const input = option.querySelector('input');
		const priceLabel = option.querySelector('.size-price');
		
		if (input.value === 'L') {
			const extra = Math.round(basePrice * 0.2);
			priceLabel.textContent = '+' + formatPrice(extra) + 'đ';
		} else if (input.value === 'S') {
			const discount = Math.round(basePrice * 0.1);
			priceLabel.textContent = '-' + formatPrice(discount) + 'đ';
		} else {
			priceLabel.textContent = '+0đ';
		}
	});
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
	const sizeExtra = Math.round((sizeMultiplier - 1) * basePrice * quantity);
	const total = baseTotal + sizeExtra;

	document.getElementById("basePrice").textContent =
		formatPrice(basePrice) + "đ";
	document.getElementById("sizePrice").textContent =
		formatPrice(sizeExtra) + "đ";
	document.getElementById("summaryQuantity").textContent = quantity;
	document.getElementById("totalPrice").textContent =
		formatPrice(total) + "đ";
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
	const multipliers = { S: 0.9, M: 1, L: 1.2 }; // S: -10%, L: +20%
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
		const sizeSelected = document.querySelector(
			'input[name="size"]:checked'
		);
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
>>>>>>> db8b244a84e9f5a8fddafc43aaa9f8a888cdf0f7
}

function showProductNotFound() {
  const container = document.getElementById("orderContent");
  if (!container) return;

<<<<<<< HEAD
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
=======
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
>>>>>>> db8b244a84e9f5a8fddafc43aaa9f8a888cdf0f7

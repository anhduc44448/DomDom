// js/news.js
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

// Event registration functionality
function initEventRegistration() {
  const eventButtons = document.querySelectorAll(".btn-event");

  eventButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const eventCard = this.closest(".event-card");
      const eventTitle = eventCard.querySelector("h4").textContent;
      const eventDate =
        eventCard.querySelector(".event-day").textContent +
        eventCard.querySelector(".event-month").textContent;

      if (localStorage.getItem("isLoggedIn") !== "true") {
        alert("Bạn cần đăng nhập để đăng ký sự kiện!");
        window.location.href = "login.html";
        return;
      }

      const userName = localStorage.getItem("username");

      if (
        confirm(
          `Bạn có chắc muốn đăng ký tham gia sự kiện:\n"${eventTitle}"\nVào ngày ${eventDate}?`
        )
      ) {
        // Simulate registration process
        this.textContent = "Đã đăng ký ✓";
        this.disabled = true;
        this.style.background = "#28a745";

        // Save registration to localStorage
        const registrations =
          JSON.parse(localStorage.getItem("eventRegistrations")) || [];
        registrations.push({
          event: eventTitle,
          date: eventDate,
          user: userName,
          registeredAt: new Date().toISOString(),
        });
        localStorage.setItem(
          "eventRegistrations",
          JSON.stringify(registrations)
        );

        alert(
          `✅ Đăng ký thành công!\nChúng tôi sẽ liên hệ với bạn qua số điện thoại đã đăng ký.`
        );
      }
    });
  });
}

// News filtering by category
function initNewsFilter() {
  // Create filter buttons
  const filterContainer = document.createElement("div");
  filterContainer.className = "news-filter text-center mb-5";
  filterContainer.innerHTML = `
        <button class="btn filter-btn active" data-filter="all">Tất cả</button>
        <button class="btn filter-btn" data-filter="su-kien">Sự kiện</button>
        <button class="btn filter-btn" data-filter="san-pham">Sản phẩm</button>
        <button class="btn filter-btn" data-filter="khuyen-mai">Khuyến mãi</button>
        <button class="btn filter-btn" data-filter="tuyen-dung">Tuyển dụng</button>
    `;

  const newsSection = document.querySelector(".latest-news");
  newsSection.insertBefore(filterContainer, newsSection.querySelector(".row"));

  const filterBtns = document.querySelectorAll(".news-filter .filter-btn");
  const newsCards = document.querySelectorAll(".news-card");

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      // Update active button
      filterBtns.forEach((b) => b.classList.remove("active"));
      this.classList.add("active");

      const filter = this.getAttribute("data-filter");

      // Filter news cards
      newsCards.forEach((card) => {
        const category = card
          .querySelector(".news-category")
          .textContent.toLowerCase();
        const categoryMap = {
          "sự kiện": "su-kien",
          "sản phẩm": "san-pham",
          "khuyến mãi": "khuyen-mai",
          "tuyển dụng": "tuyen-dung",
        };

        if (filter === "all" || categoryMap[category] === filter) {
          card.style.display = "block";
          setTimeout(() => {
            card.style.opacity = "1";
            card.style.transform = "translateY(0)";
          }, 100);
        } else {
          card.style.opacity = "0";
          card.style.transform = "translateY(20px)";
          setTimeout(() => {
            card.style.display = "none";
          }, 300);
        }
      });
    });
  });
}

// Newsletter subscription
function initNewsletter() {
  // Create newsletter section
  const newsletterSection = document.createElement("section");
  newsletterSection.className = "newsletter-section";
  newsletterSection.innerHTML = `
        <div class="container">
            <div class="newsletter-content">
                <h3>Đăng ký nhận tin</h3>
                <p>Nhận thông tin mới nhất về khuyến mãi, sự kiện và tin tức từ Đom đóm quán</p>
                <form class="newsletter-form" id="newsletterForm">
                    <input type="email" placeholder="Nhập email của bạn..." required>
                    <button type="submit">Đăng ký</button>
                </form>
            </div>
        </div>
    `;

  document.querySelector(".news-section").appendChild(newsletterSection);

  const newsletterForm = document.getElementById("newsletterForm");

  newsletterForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const email = this.querySelector('input[type="email"]').value;

    if (validateEmail(email)) {
      // Simulate subscription
      const btn = this.querySelector("button");
      const originalText = btn.textContent;
      btn.textContent = "Đang đăng ký...";
      btn.disabled = true;

      setTimeout(() => {
        btn.textContent = "Đã đăng ký ✓";
        this.reset();

        // Save subscription
        const subscriptions =
          JSON.parse(localStorage.getItem("newsletterSubscriptions")) || [];
        subscriptions.push({
          email: email,
          subscribedAt: new Date().toISOString(),
        });
        localStorage.setItem(
          "newsletterSubscriptions",
          JSON.stringify(subscriptions)
        );

        setTimeout(() => {
          btn.textContent = originalText;
          btn.disabled = false;
        }, 2000);

        alert("✅ Cảm ơn bạn đã đăng ký nhận tin!");
      }, 1500);
    } else {
      alert("Vui lòng nhập email hợp lệ!");
    }
  });
}

// Email validation
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

// Share functionality
function initShareButtons() {
  const newsCards = document.querySelectorAll(".news-card");

  newsCards.forEach((card) => {
    const shareBtn = document.createElement("button");
    shareBtn.className = "btn-share";
    shareBtn.innerHTML = "🔗 Chia sẻ";
    shareBtn.style.cssText = `
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 15px;
            padding: 5px 12px;
            font-size: 0.8rem;
            margin-top: 10px;
            cursor: pointer;
            transition: all 0.3s;
        `;

    shareBtn.addEventListener("mouseenter", function () {
      this.style.background = "#e9ecef";
    });

    shareBtn.addEventListener("mouseleave", function () {
      this.style.background = "#f8f9fa";
    });

    shareBtn.addEventListener("click", function () {
      const newsTitle = card.querySelector(".news-title").textContent;
      const shareUrl = window.location.href;
      const shareText = `Tin tức từ Đom đóm quán: ${newsTitle}`;

      if (navigator.share) {
        navigator.share({
          title: newsTitle,
          text: shareText,
          url: shareUrl,
        });
      } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(`${shareText}\n${shareUrl}`).then(() => {
          alert("Đã sao chép link chia sẻ vào clipboard!");
        });
      }
    });

    card.querySelector(".news-content").appendChild(shareBtn);
  });
}

// Initialize everything when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  updateLoginButton();
  document
    .getElementById("loginBtn")
    .addEventListener("click", handleLoginClick);
  initEventRegistration();
  initNewsFilter();
  initNewsletter();
  initShareButtons();
});

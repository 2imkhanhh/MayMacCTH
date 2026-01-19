// FILE: script.js
// =============================================

document.addEventListener("DOMContentLoaded", () => {
  // ─────────────────────────────────────────────
  // 1. Chatbot (chỉ khởi tạo nếu có các phần tử cần thiết)
  // ─────────────────────────────────────────────
  const chatbotToggler = document.querySelector("#chatbot-toggler");
  const chatbotClose   = document.querySelector("#chatbot-close");
  const chatbotWrapper = document.querySelector(".chatbot-wrapper");
  const chatBox        = document.querySelector("#chat-box");
  const chatInput      = document.querySelector("#chat-input");
  const sendBtn        = document.querySelector("#send-btn");

  if (chatbotToggler && chatbotClose && chatbotWrapper && chatBox && chatInput && sendBtn) {
    const toggleChatbot = () => {
      document.body.classList.toggle("show-chatbot");
      chatbotWrapper.classList.toggle("show");
    };

    chatbotToggler.addEventListener("click", toggleChatbot);
    chatbotClose.addEventListener("click", () => {
      document.body.classList.remove("show-chatbot");
      chatbotWrapper.classList.remove("show");
    });

    const createChatLi = (message, className) => {
      const chatDiv = document.createElement("div");
      chatDiv.classList.add("chat-message", className, "d-flex");

      const content = className === "user-message"
        ? `<div class="message-content">${message}</div>`
        : `<div class="message-content"><i class='bx bx-loader-alt bx-spin'></i> Đang nhập...</div>`;

      chatDiv.innerHTML = content;
      return chatDiv;
    };

    const handleChat = () => {
      const userMessage = chatInput.value.trim();
      if (!userMessage) return;

      chatInput.value = "";

      chatBox.appendChild(createChatLi(userMessage, "user-message"));
      chatBox.scrollTo(0, chatBox.scrollHeight);

      const incomingChatLi = createChatLi("", "bot-message");
      chatBox.appendChild(incomingChatLi);
      chatBox.scrollTo(0, chatBox.scrollHeight);

      fetch('/MayMacCTH/api/chatbot/chat.php', {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message: userMessage })
      })
        .then(res => res.json())
        .then(data => {
          const messageElement = incomingChatLi.querySelector(".message-content");
          messageElement.innerHTML = data.reply || "Không có phản hồi.";
        })
        .catch(() => {
          const messageElement = incomingChatLi.querySelector(".message-content");
          messageElement.classList.add("error");
          messageElement.textContent = "Có lỗi kết nối, vui lòng thử lại sau.";
        })
        .finally(() => {
          chatBox.scrollTo(0, chatBox.scrollHeight);
        });
    };

    sendBtn.addEventListener("click", handleChat);
    chatInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        handleChat();
      }
    });
  } else {
    console.warn("Chatbot elements not found → chatbot skipped on this page");
  }

  // ─────────────────────────────────────────────
  // 2. Hiệu ứng fade khi scroll
  // ─────────────────────────────────────────────
  const fadeElements = document.querySelectorAll(".fade-element");

  if (fadeElements.length > 0) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
          } else {
            entry.target.classList.remove("visible");
          }
        });
      },
      { threshold: 0.5 }
    );

    fadeElements.forEach((el) => observer.observe(el));
  }

  // ─────────────────────────────────────────────
  // 3. Breadcrumb động
  // ─────────────────────────────────────────────
  const breadcrumb = document.getElementById("breadcrumb");
  if (breadcrumb) {
    const currentPage = location.pathname.split("/").pop();

    if (currentPage === "product-detail.php") {
    } else if (currentPage === "product-detail.html") {
      const urlParams = new URLSearchParams(location.search);
      const productName = urlParams.get("name") || "Chi tiết sản phẩm";

      breadcrumb.innerHTML = `
        <a href="index.html">Trang chủ</a>
        <span class="sep">/</span>
        <a href="products.html">Sản phẩm</a>
        <span class="sep">/</span>
        <span class="current">${productName}</span>
      `;
    } else {
      const pageMap = {
        "index.html": "Trang chủ",
        "about-us.html": "Về chúng tôi",
        "products.html": "Sản phẩm",
        "guide.html": "Hướng dẫn",
        "news.html": "Tin tức",
        "news-detail.html": "Tin tức",
        "contact.html": "Liên hệ",
        "cart.html": "Giỏ hàng",
        "order.html": "Đặt hàng",
        "thankyou.html": "Cảm ơn"
      };

      const pageName = pageMap[currentPage] || "Trang chủ";

      if (currentPage === "index.html") {
        breadcrumb.innerHTML = `<span class="current">${pageName}</span>`;
      } else {
        breadcrumb.innerHTML = `
          <a href="index.html">Trang chủ</a>
          <span class="sep"> > </span>
          <span class="current">${pageName}</span>
        `;
      }
    }
  }

  // ─────────────────────────────────────────────
  // 4. Load thông tin liên hệ footer từ API
  // ─────────────────────────────────────────────
  const BASE_URL = "/MayMacCTH";
  const addressEl = document.getElementById("footerAddress");
  const websiteEl = document.getElementById("footerWebsite");
  const phoneEl   = document.getElementById("footerPhone");

  if (addressEl || websiteEl || phoneEl) {
    const loadFooterContact = async () => {
      try {
        const res = await fetch(`${BASE_URL}/api/contact/get_contact.php?t=${Date.now()}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        const data = await res.json();

        if (data.success && data.data?.length > 0) {
          const c = data.data[0];

          if (addressEl) addressEl.textContent = c.address || "Chưa cập nhật địa chỉ";
          if (websiteEl) {
            if (c.website?.trim()) {
              const url = /^https?:\/\//i.test(c.website) ? c.website : `https://${c.website}`;
              websiteEl.outerHTML = `<a href="${url}" target="_blank" class="text-decoration-none" style="font-size: 14px; color: #ffffffd9;">${c.website}</a>`;
            } else {
              websiteEl.textContent = "Chưa có website";
            }
          }
          if (phoneEl) phoneEl.textContent = c.phone_number || "Chưa cập nhật số điện thoại";
        }
      } catch (err) {
        console.error("Lỗi load thông tin liên hệ:", err);
        if (addressEl) addressEl.textContent = "Không tải được";
        if (websiteEl) websiteEl.textContent = "Không tải được";
        if (phoneEl) phoneEl.textContent = "Không tải được";
      }
    };

    loadFooterContact();
  }

  // ─────────────────────────────────────────────
  // 5. Menu mobile - dropdown & nút đóng
  // ─────────────────────────────────────────────
  if (window.innerWidth < 992) {
    document.querySelectorAll(".dropdown-custom > .nav-link").forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        const parent = this.closest(".dropdown-custom");
        if (parent) parent.classList.toggle("show-dropdown");
      });
    });

    const closeBtn = document.querySelector(".btn-close-menu");
    const navbarCollapse = document.querySelector(".navbar-collapse");

    if (closeBtn && navbarCollapse) {
      closeBtn.addEventListener("click", () => {
        navbarCollapse.classList.remove("show");
        document.querySelectorAll(".dropdown-custom").forEach((el) => {
          el.classList.remove("show-dropdown");
        });
      });
    }
  }

  // ─────────────────────────────────────────────
  // 6. Cập nhật số lượng giỏ hàng (mobile + desktop)
  // ─────────────────────────────────────────────
  const updateCartCount = () => {
    const cart = JSON.parse(localStorage.getItem("cart") || "[]");
    const totalItems = cart.length;

    const elMobile = document.getElementById("cartCountMobile");
    if (elMobile) {
      elMobile.textContent = totalItems;
      elMobile.style.display = totalItems > 0 ? "block" : "none";
    }

    const elDesktop = document.getElementById("cartCountDesktop");
    if (elDesktop) {
      elDesktop.textContent = totalItems;
      elDesktop.style.display = totalItems > 0 ? "block" : "none";
    }
  };

  updateCartCount();
});
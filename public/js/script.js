document.addEventListener("DOMContentLoaded", function () {
  const fadeElements = document.querySelectorAll(".fade-element");

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
      } else {
        entry.target.classList.remove("visible");
      }
    });
  }, { threshold: 0.5 });

  fadeElements.forEach(el => observer.observe(el));
});

// Breadcrumb
document.addEventListener('DOMContentLoaded', async function () {
  const breadcrumb = document.getElementById('breadcrumb');
  if (!breadcrumb) return;

  const currentPage = location.pathname.split('/').pop();

  if (currentPage === 'product-detail.php') {
    return;
  }

  if (currentPage === 'product-detail.html') {
    const urlParams = new URLSearchParams(location.search);
    const productName = urlParams.get('name') || 'Chi tiết sản phẩm';

    breadcrumb.innerHTML = `
            <a href="index.html">Trang chủ</a>
            <span class="sep">/</span>
            <a href="products.html">Sản phẩm</a>
            <span class="sep">/</span>
            <span class="current">${productName}</span>
        `;
    return;
  }

  const pageMap = {
    'index.html': 'Trang chủ',
    'about-us.html': 'Về chúng tôi',
    'products.html': 'Sản phẩm',
    'guide.html': 'Hướng dẫn',
    'news.html': 'Tin tức',
    'news-detail.html': 'Tin tức',
    'contact.html': 'Liên hệ',
    'cart.html': 'Giỏ hàng',
    'order.html': 'Đặt hàng',
    'thankyou.html': 'Cảm ơn'
  };

  const pageName = pageMap[currentPage] || 'Trang chủ';

  if (currentPage === 'index.html') {
    breadcrumb.innerHTML = `<span class="current">${pageName}</span>`;
  } else {
    breadcrumb.innerHTML = `
            <a href="index.html">Trang chủ</a>
            <span class="sep"> > </span>
            <span class="current">${pageName}</span>
        `;
  }
});

document.addEventListener("DOMContentLoaded", async function () {
  const BASE_URL = "/MayMacCTH";

  const addressEl = document.getElementById("footerAddress");
  const websiteEl = document.getElementById("footerWebsite");
  const phoneEl = document.getElementById("footerPhone");

  if (!addressEl && !websiteEl && !phoneEl) return;

  async function loadFooterContact() {
    try {
      const res = await fetch(`${BASE_URL}/api/contact/get_contact.php?t=${Date.now()}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const data = await res.json();

      if (data.success && data.data && data.data.length > 0) {
        const c = data.data[0];

        if (addressEl) {
          addressEl.textContent = c.address || "Chưa cập nhật địa chỉ";
        }

        if (websiteEl) {
          if (c.website && c.website.trim()) {
            const url = c.website.match(/^https?:\/\//i) ? c.website : "https://" + c.website;
            websiteEl.outerHTML = `<a href="${url}" target="_blank" class="text-decoration-none" style="font-size: 14px; color: #ffffffd9;">${c.website}</a>`;
          } else {
            websiteEl.textContent = "Chưa có website";
          }
        }

        if (phoneEl) {
          phoneEl.textContent = c.phone_number || "Chưa cập nhật số điện thoại";
        }
      }
    } catch (err) {
      console.error("Lỗi load thông tin liên hệ:", err);
      if (addressEl) addressEl.textContent = "Không tải được";
      if (websiteEl) websiteEl.textContent = "Không tải được";
      if (phoneEl) phoneEl.textContent = "Không tải được";
    }
  }

  loadFooterContact();
});

document.addEventListener("DOMContentLoaded", function () {
  if (window.innerWidth < 992) {
    // Xử lý click menu cha
    const dropdownLinks = document.querySelectorAll('.dropdown-custom > .nav-link');

    dropdownLinks.forEach(link => {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        const parent = this.closest('.dropdown-custom');
        if (parent) {
          parent.classList.toggle('show-dropdown');
        }
      });
    });

    // Xử lý nút đóng
    const closeBtn = document.querySelector('.btn-close-menu');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    if (closeBtn && navbarCollapse) {
      closeBtn.addEventListener('click', function () {
        navbarCollapse.classList.remove('show');
        document.querySelectorAll('.dropdown-custom').forEach(el => {
          el.classList.remove('show-dropdown');
        });
      });
    }
  }
});

function updateCartCount() {
  const cart = JSON.parse(localStorage.getItem('cart') || '[]');
  const totalItems = cart.length;

  const elMobile = document.getElementById('cartCountMobile');
  if (elMobile) {
    elMobile.textContent = totalItems;
    elMobile.style.display = totalItems > 0 ? 'block' : 'none';
  }

  const elDesktop = document.getElementById('cartCountDesktop');
  if (elDesktop) {
    elDesktop.textContent = totalItems;
    elDesktop.style.display = totalItems > 0 ? 'block' : 'none';
  }
}
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
// document.addEventListener('DOMContentLoaded', function () {
//   const breadcrumbPage = window.location.pathname.split('/').pop() || 'index.html';
//   const pageMap = {
//     'index.html': 'Trang chủ',
//     'about-us.html': 'Về chúng tôi',
//     'products.html': 'Sản phẩm',
//     'guide.html': 'Hướng dẫn',
//     'news.html': 'Tin tức',
//     'contact.html': 'Liên hệ',
//     'product-detail.html': 'Áo Đồng Phục',
//     'cart.html': 'Giỏ hàng',
//     'order.html': 'Đặt hàng',
//     'news-detail.html': 'Tin tứcc'
//   };

//   const currentPageName = pageMap[breadcrumbPage] || 'Trang chủ';
//   const urlParams = new URLSearchParams(window.location.search);
//   const productName = urlParams.get('name') || 'Áo Đồng Phục';

//   if (breadcrumbPage === 'index.html') {
//     breadcrumb.innerHTML = `${currentPageName}`;
//   } else if (breadcrumbPage === 'product-detail.html') {
//     breadcrumb.innerHTML = `
//       Trang chủ >
//       <a href="products.html">Sản phẩm</a> >
//       <a href="product-detail.html?name=${encodeURIComponent(productName)}" class="product-name">
//         ${productName}
//       </a>
//     `;
//   } else {
//     breadcrumb.innerHTML = `Trang chủ > <a href="${breadcrumbPage}">${currentPageName}</a>`;
//   }
// });

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
            websiteEl.innerHTML = `<a href="${url}" target="_blank" class="text-white text-decoration-none">${c.website}</a>`;
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
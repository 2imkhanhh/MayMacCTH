document.addEventListener('DOMContentLoaded', function () {
  // Example: Smooth scroll for navigation
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelector(this.getAttribute('href')).scrollIntoView({
        behavior: 'smooth'
      });
    });
  });
});

// document.addEventListener("DOMContentLoaded", function () {
//   const fadeElements = document.querySelectorAll(".fade-element");

//   const observer = new IntersectionObserver((entries) => {
//     entries.forEach(entry => {
//       if (entry.isIntersecting) {
//         entry.target.classList.add("visible");
//       } else {
//         entry.target.classList.remove("visible"); // Cho phép fade-out khi cuộn ngược
//       }
//     });
//   }, { threshold: 0.5 }); // 20% phần tử vào khung nhìn thì kích hoạt

//   fadeElements.forEach(el => observer.observe(el));
// });

document.addEventListener('DOMContentLoaded', function () {
  const breadcrumbPage = window.location.pathname.split('/').pop() || 'index.html';
  const pageMap = {
    'index.html': 'Trang chủ',
    'about-us.html': 'Về chúng tôi',
    'products.html': 'Sản phẩm',
    'guide.html': 'Hướng dẫn',
    'news.html': 'Tin tức',
    'contact.html': 'Liên hệ',
    'product-detail.html': 'Áo Đồng Phục' 
  };

  const currentPageName = pageMap[breadcrumbPage] || 'Trang chủ';
  const urlParams = new URLSearchParams(window.location.search);
  const productName = urlParams.get('name') || 'Áo Đồng Phục'; // Lấy tên sản phẩm từ query parameter, nếu không có thì dùng mặc định

  if (breadcrumbPage === 'index.html') {
    breadcrumb.innerHTML = `${currentPageName}`;
  } else if (breadcrumbPage === 'product-detail.html') {
    breadcrumb.innerHTML = `Trang chủ > <a href="products.html">Sản phẩm</a> > <a href="#" class="product-link" style="color: #007bff;">${productName}</a>`;
  } else {
    breadcrumb.innerHTML = `Trang chủ > <a href="${breadcrumbPage}">${currentPageName}</a>`;
  }
});


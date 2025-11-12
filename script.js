// Add any interactivity if needed
document.addEventListener('DOMContentLoaded', function() {
    // Example: Smooth scroll for navigation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
  const banners = document.querySelectorAll(".banner-image");
  const dots = document.querySelectorAll(".banner-dots .dot");
  let currentBanner = 0;

  function showBanner(index) {
    // ẩn ảnh cũ, bỏ active dot cũ
    banners[currentBanner].classList.remove("active");
    dots[currentBanner].classList.remove("active");

    // hiển thị ảnh mới, active dot mới
    currentBanner = index;
    banners[currentBanner].classList.add("active");
    dots[currentBanner].classList.add("active");
  }

  function nextBanner() {
    let nextIndex = (currentBanner + 1) % banners.length;
    showBanner(nextIndex);
  }

  // tự động chuyển banner mỗi 4 giây
  let interval = setInterval(nextBanner, 4000);

  // click vào dot
  dots.forEach(dot => {
    dot.addEventListener("click", function() {
      showBanner(parseInt(this.dataset.index));
      clearInterval(interval); // reset interval
      interval = setInterval(nextBanner, 4000); // tiếp tục tự động
    });
  });

  const fadeElements = document.querySelectorAll(".fade-element");

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
      } else {
        entry.target.classList.remove("visible"); // Cho phép fade-out khi cuộn ngược
      }
    });
  }, { threshold: 0.4 }); // 20% phần tử vào khung nhìn thì kích hoạt

  fadeElements.forEach(el => observer.observe(el));
});


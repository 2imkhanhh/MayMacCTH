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


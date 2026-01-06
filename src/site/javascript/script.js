/* banner đầu trang home */
document.addEventListener("DOMContentLoaded", () => {
  const slider = document.querySelector("#bannerSlider");
  if (!slider) return;

  const slides = Array.from(slider.querySelectorAll(".slide"));
  const nextBtn = slider.querySelector(".next");
  const prevBtn = slider.querySelector(".prev");

  let index = 0;
  const TIME = 3000; // ✅ 3 giây

  function render() {
    slides.forEach((s, i) => s.classList.toggle("active", i === index));
  }

  function next() {
    index = (index + 1) % slides.length;
    render();
  }

  function prev() {
    index = (index - 1 + slides.length) % slides.length;
    render();
  }

  // ✅ auto chạy
  let timer = setInterval(next, TIME);

  // ✅ bấm nút + reset timer để không bị “nhảy”
  function resetTimer() {
    clearInterval(timer);
    timer = setInterval(next, TIME);
  }

  nextBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    next();
    resetTimer();
  });

  prevBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    prev();
    resetTimer();
  });

  // ✅ click ảnh mở trang khác
  slides.forEach((slide) => {
    slide.addEventListener("click", () => {
      const link = slide.dataset.link;
      if (link) window.location.href = link;
    });
  });

  render();
});
/* button brand */
document.addEventListener("DOMContentLoaded", () => {
  const brandButtons = document.querySelectorAll(".brand-nho");

  brandButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      const link = btn.dataset.link;
      if (link) {
        window.location.href = link;
      }
    });
  });
});
/* pro detail */

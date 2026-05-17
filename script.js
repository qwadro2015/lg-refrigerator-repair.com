function qs(sel, root = document) {
  return root.querySelector(sel);
}

function qsa(sel, root = document) {
  return Array.from(root.querySelectorAll(sel));
}

function closeNav() {
  const panel = qs("[data-nav-panel]");
  const toggle = qs("[data-nav-toggle]");
  if (!panel || !toggle) return;
  panel.dataset.open = "false";
  toggle.setAttribute("aria-expanded", "false");
}

function openNav() {
  const panel = qs("[data-nav-panel]");
  const toggle = qs("[data-nav-toggle]");
  if (!panel || !toggle) return;
  panel.dataset.open = "true";
  toggle.setAttribute("aria-expanded", "true");
}

function initNav() {
  const panel = qs("[data-nav-panel]");
  const toggle = qs("[data-nav-toggle]");
  if (!panel || !toggle) return;

  panel.dataset.open = "false";

  toggle.addEventListener("click", () => {
    const isOpen = panel.dataset.open === "true";
    if (isOpen) closeNav();
    else openNav();
  });

  qsa(".nav__link", panel).forEach((a) => {
    a.addEventListener("click", () => closeNav());
  });

  document.addEventListener("click", (e) => {
    const t = e.target;
    if (!(t instanceof Element)) return;
    if (panel.contains(t) || toggle.contains(t)) return;
    closeNav();
  });

  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeNav();
  });
}

function initAccordion() {
  const items = qsa("[data-accordion] details");
  if (!items.length) return;

  items.forEach((details) => {
    details.addEventListener("toggle", () => {
      if (!details.open) return;
      items.forEach((other) => {
        if (other !== details) other.open = false;
      });
    });
  });
}

function initContactForm() {
  const form = qs("[data-contact-form]");
  const note = qs("[data-form-note]");
  if (!form || !note) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());

    note.hidden = false;
    note.textContent = `Thanks, ${String(payload.name || "there")} — we received your request and will contact you shortly.`;

    form.reset();
  });
}

function initCarousel() {
  const root = qs("[data-carousel]");
  if (!root) return;

  const track = qs("[data-carousel-track]", root);
  const slides = qsa("[data-carousel-slide]", root);
  const dots = qsa("[data-carousel-dot]", root);
  const prev = qs("[data-carousel-prev]", root);
  const next = qs("[data-carousel-next]", root);
  if (!track || !slides.length) return;

  let index = 0;
  let timer = null;

  function apply(i, opts = { user: false }) {
    index = (i + slides.length) % slides.length;
    const x = -index * 100;
    track.style.transform = `translateX(${x}%)`;

    slides.forEach((s, si) => {
      s.classList.toggle("is-active", si === index);
      s.setAttribute("aria-hidden", si === index ? "false" : "true");
    });
    dots.forEach((d, di) => d.classList.toggle("is-active", di === index));

    if (opts.user) restart();
  }

  function restart() {
    if (timer) window.clearInterval(timer);
    timer = window.setInterval(() => apply(index + 1), 6500);
  }

  prev?.addEventListener("click", () => apply(index - 1, { user: true }));
  next?.addEventListener("click", () => apply(index + 1, { user: true }));
  dots.forEach((d) => {
    d.addEventListener("click", () => {
      const v = Number(d.getAttribute("data-index") || "0");
      apply(v, { user: true });
    });
  });

  root.addEventListener("mouseenter", () => timer && window.clearInterval(timer));
  root.addEventListener("mouseleave", () => restart());

  window.addEventListener("keydown", (e) => {
    if (e.key === "ArrowLeft") apply(index - 1, { user: true });
    if (e.key === "ArrowRight") apply(index + 1, { user: true });
  });

  apply(0);
  restart();
}

initNav();
initCarousel();
initContactForm();

function initHeroSlider() {
  const root = qs("[data-hero-slider]");
  if (!root) return;

  const slides = qsa(".hero__slide", root);
  const dots = qsa(".hero__dot", root);
  if (!slides.length) return;

  let index = 0;
  let timer = null;

  function show(i) {
    index = (i + slides.length) % slides.length;
    slides.forEach((s, si) => s.classList.toggle("is-active", si === index));
    dots.forEach((d, di) => d.classList.toggle("is-active", di === index));
    if (timer) window.clearInterval(timer);
    timer = window.setInterval(() => show(index + 1), 7000);
  }

  dots.forEach((d) => {
    d.addEventListener("click", () => {
      const v = Number(d.getAttribute("data-slide") || "0");
      show(v);
    });
  });

  root.addEventListener("mouseenter", () => timer && window.clearInterval(timer));
  root.addEventListener("mouseleave", () => {
    if (timer) window.clearInterval(timer);
    timer = window.setInterval(() => show(index + 1), 7000);
  });

  show(0);
}

initHeroSlider();


// Form submission handling
document.getElementById("contactForm").addEventListener("submit", function (event) {
    event.preventDefault(); // Зупиняємо стандартну відправку форми

    let formData = new FormData(this);

    fetch("/send_email.php", { 
    method: "POST",
    body: formData
})

    .then(response => response.json()) // Очікуємо JSON-відповідь
    .then(data => {
        alert(data.message); // Виводимо повідомлення
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while sending the email.");
    });

    this.reset(); // Очищуємо форму після відправки
});

/* NAVBAR — MENU MOBILE */

document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.querySelector(".navbar-toggle");
    const mobileMenu = document.querySelector(".mobile-menu");

    if (!toggle || !mobileMenu) return;

    const closeMenu = () => {
        toggle.classList.remove("is-active");
        mobileMenu.classList.remove("is-open");
        toggle.setAttribute("aria-expanded", "false");
    };

    toggle.addEventListener("click", () => {
        const isOpen = toggle.classList.toggle("is-active");
        mobileMenu.classList.toggle("is-open", isOpen);
        toggle.setAttribute("aria-expanded", String(isOpen));
    });

    mobileMenu.querySelectorAll("a").forEach(link => {
        link.addEventListener("click", closeMenu);
    });

    window.addEventListener("resize", () => {
        if (window.innerWidth > 800) closeMenu();
    });
});

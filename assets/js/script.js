/**
 * Cloud Closet - Interactivity Scripts
 *
 * Handles the responsive mobile navigation drawer, scroll behavior,
 * and page transitions/hover actions.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. DOM Elements
    const navbar = document.getElementById('navbar');
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    const navLinks = document.querySelectorAll('.nav-item');

    // 2. Mobile Menu Toggle Action
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            // Toggle the 'active' class on both the toggle button and the navigation drawer
            navToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }

    // 3. Close Mobile Menu when clicking a nav link
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navToggle && navMenu) {
                navToggle.classList.remove('active');
                navMenu.classList.remove('active');
            }
            
            // Update active link styling
            navLinks.forEach(item => item.classList.remove('active'));
            link.classList.add('active');
        });
    });

    // 4. Change Navbar Styling on Scroll
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // 5. Hero Background Image Slider
    const heroSlider = document.getElementById('heroSlider');
    const heroDots = document.querySelectorAll('#heroSliderNav .hero-ctrl-dot');

    if (heroSlider && heroDots.length > 0) {
        const slides = heroSlider.querySelectorAll('.hero-slide');
        let currentSlide = 0;
        let timer = null;

        const goToSlide = (index) => {
            if (!slides || slides.length === 0 || index < 0 || index >= slides.length) return;
            slides.forEach((slide, i) => {
                if (i === index) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });
            heroDots.forEach((dot, i) => {
                if (i === index) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
            currentSlide = index;
        };

        const nextSlide = () => {
            const nextIdx = (currentSlide + 1) % slides.length;
            goToSlide(nextIdx);
        };

        const startSlider = () => {
            if (timer) clearInterval(timer);
            timer = setInterval(nextSlide, 4500);
        };

        const stopSlider = () => {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        };

        heroDots.forEach((dot) => {
            dot.addEventListener('click', (e) => {
                e.stopPropagation();
                const targetIdx = parseInt(dot.getAttribute('data-slide'), 10);
                if (!isNaN(targetIdx)) {
                    goToSlide(targetIdx);
                    startSlider();
                }
            });
        });

        startSlider();
    }
});

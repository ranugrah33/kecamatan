/* =========================================
   BERANDA - JAVASCRIPT
   Kecamatan Cikampek Digital Homepage
   ========================================= */

document.addEventListener('DOMContentLoaded', function () {

    // ---- 1. NAVBAR SCROLL EFFECT ----
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ---- 2. MOBILE HAMBURGER MENU ----
    const hamburger = document.querySelector('.nav-hamburger');
    const navMenu = document.querySelector('.nav-menu');
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function () {
            this.classList.toggle('active');
            navMenu.classList.toggle('open');
        });

        // Close menu when clicking a link
        navMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                hamburger.classList.remove('active');
                navMenu.classList.remove('open');
            });
        });
    }

    // ---- 3. SCROLL REVEAL ANIMATIONS ----
    const revealElements = document.querySelectorAll('.reveal');
    const revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    revealElements.forEach(function (el) {
        revealObserver.observe(el);
    });

    // ---- 4. COUNTER ANIMATION ----
    const counters = document.querySelectorAll('.stat-number[data-target]');
    let countersAnimated = false;

    function animateCounters() {
        if (countersAnimated) return;
        countersAnimated = true;

        counters.forEach(function (counter) {
            const target = parseInt(counter.getAttribute('data-target'));
            const suffix = counter.getAttribute('data-suffix') || '';
            const duration = 2000;
            const startTime = performance.now();

            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function (ease-out-quart)
                const easeProgress = 1 - Math.pow(1 - progress, 4);
                const current = Math.floor(easeProgress * target);

                counter.textContent = current.toLocaleString('id-ID') + suffix;

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toLocaleString('id-ID') + suffix;
                }
            }

            requestAnimationFrame(updateCounter);
        });
    }

    // Trigger counter when stats section is visible
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        const statsObserver = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) {
                animateCounters();
            }
        }, { threshold: 0.3 });

        statsObserver.observe(statsSection);
    }

    // ---- 5. FLOATING PARTICLES ----
    const particlesContainer = document.querySelector('.particles');
    if (particlesContainer) {
        for (var i = 0; i < 20; i++) {
            createParticle(particlesContainer);
        }
    }

    function createParticle(container) {
        var particle = document.createElement('div');
        particle.classList.add('particle');

        var size = Math.random() * 4 + 2;
        var left = Math.random() * 100;
        var duration = Math.random() * 15 + 10;
        var delay = Math.random() * 10;
        var opacity = Math.random() * 0.4 + 0.1;

        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.left = left + '%';
        particle.style.animationDuration = duration + 's';
        particle.style.animationDelay = delay + 's';
        particle.style.opacity = opacity;

        container.appendChild(particle);
    }

    // ---- 6. SCROLL TO TOP BUTTON ----
    var scrollTopBtn = document.querySelector('.scroll-top-btn');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 400) {
                scrollTopBtn.classList.add('visible');
            } else {
                scrollTopBtn.classList.remove('visible');
            }
        });

        scrollTopBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ---- 7. SMOOTH SCROLL FOR ANCHOR LINKS ----
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // ---- 8. ACTIVE NAV LINK ON SCROLL ----
    var sections = document.querySelectorAll('section[id]');
    var navLinks = document.querySelectorAll('.nav-menu a[href^="#"]');

    window.addEventListener('scroll', function () {
        var scrollY = window.scrollY + 100;

        sections.forEach(function (section) {
            var sectionTop = section.offsetTop;
            var sectionHeight = section.offsetHeight;
            var sectionId = section.getAttribute('id');

            if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
                navLinks.forEach(function (link) {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + sectionId) {
                        link.classList.add('active');
                    }
                });
            }
        });
    });

    // ---- 9. 3D CARD MOUSE TILT EFFECT ----
    var hero3dCard = document.querySelector('.hero-3d-card');
    if (hero3dCard) {
        var heroVisual = document.querySelector('.hero-visual');

        heroVisual.addEventListener('mousemove', function (e) {
            var rect = heroVisual.getBoundingClientRect();
            var x = (e.clientX - rect.left) / rect.width;
            var y = (e.clientY - rect.top) / rect.height;

            var rotateX = (y - 0.5) * 15;
            var rotateY = (x - 0.5) * -15;

            hero3dCard.style.animation = 'none';
            hero3dCard.style.transform = 'rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg)';
        });

        heroVisual.addEventListener('mouseleave', function () {
            hero3dCard.style.animation = '';
            hero3dCard.style.transform = '';
        });
    }

});

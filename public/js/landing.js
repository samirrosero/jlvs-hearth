// ── Reveal lateral del equipo al hacer scroll ──────────────────
(function () {
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.2 });

    document.querySelectorAll('.reveal-left, .reveal-right').forEach(function (el) {
        observer.observe(el);
    });
})();

// ── Lightbox ───────────────────────────────────────────────────
(function () {
    var lightbox = document.getElementById('lightbox');
    var lightboxImg = document.getElementById('lightboxImg');
    var btnCerrar = document.getElementById('lightboxCerrar');
    if (!lightbox || !lightboxImg || !btnCerrar) return;

    document.querySelectorAll('.foto-wrapper img').forEach(function (img) {
        img.addEventListener('click', function () {
            lightboxImg.src = img.src;
            lightbox.classList.add('abierto');
        });
    });

    function cerrar() { lightbox.classList.remove('abierto'); }
    btnCerrar.addEventListener('click', cerrar);
    lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) cerrar();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') cerrar();
    });
})();

// ── Animación fade-up al hacer scroll ─────────────────────────
(function () {
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (e, i) {
            if (e.isIntersecting) {
                setTimeout(function () {
                    e.target.classList.add('visible');
                }, i * 100);
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('.fade-up').forEach(function (el) {
        observer.observe(el);
    });

    // aplica fade-up al video-wrapper
    var videoWrapper = document.querySelector('.video-wrapper');
    if (videoWrapper) {
        videoWrapper.classList.add('fade-up');
        observer.observe(videoWrapper);
    }
})();

// ── Barra de progreso de scroll ────────────────────────────────
(function () {
    var bar = document.getElementById('scrollProgress');
    if (!bar) return;
    function update() {
        var max = document.body.scrollHeight - window.innerHeight;
        bar.style.width = (max > 0 ? (window.scrollY / max) * 100 : 0) + '%';
    }
    window.addEventListener('scroll', update, { passive: true });
    update();
})();

// ── Parallax suave con el mouse en el hero ─────────────────────
(function () {
    var hero = document.querySelector('.hero');
    if (!hero) return;
    var ticking = false;
    document.addEventListener('mousemove', function (e) {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () {
            var cx = window.innerWidth  / 2;
            var cy = window.innerHeight / 2;
            var dx = (e.clientX - cx) / cx;
            var dy = (e.clientY - cy) / cy;
            var before = hero.style;
            // mueve los pseudo-elementos via custom props
            hero.style.setProperty('--px', (dx * 18) + 'px');
            hero.style.setProperty('--py', (dy * 18) + 'px');
            ticking = false;
        });
    });
})();

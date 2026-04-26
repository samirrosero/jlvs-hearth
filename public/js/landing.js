
// ── Slider del equipo ─────────────────────────────────────────
(function () {
    var track  = document.getElementById('sliderTrack');
    var dots   = document.querySelectorAll('.slider-dot');
    var total  = 4;
    var actual = 0;
    var timer  = null;
    var INTERVALO = 6000;

    if (!track) return;

    function irA(i) {
        actual = (i + total) % total;
        track.style.transform = 'translateX(-' + (actual * 100) + '%)';
        dots.forEach(function (d, j) {
            d.classList.toggle('activo', j === actual);
        });
    }

    function iniciar() {
        detener();
        timer = setInterval(function () { irA(actual + 1); }, INTERVALO);
    }

    function detener() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    var btnPrev = document.getElementById('btnPrev');
    var btnNext = document.getElementById('btnNext');
    var wrapper = document.querySelector('.slider-wrapper');

    if (btnPrev) btnPrev.addEventListener('click', function () { irA(actual - 1); iniciar(); });
    if (btnNext) btnNext.addEventListener('click', function () { irA(actual + 1); iniciar(); });

    dots.forEach(function (d) {
        d.addEventListener('click', function () { irA(parseInt(d.dataset.index)); iniciar(); });
    });

    if (wrapper) {
        wrapper.addEventListener('mouseenter', detener);
        wrapper.addEventListener('mouseleave', iniciar);
    }

    iniciar();
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

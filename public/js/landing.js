// ── Carrusel ──────────────────────────────────────────────────
(function () {
    const track = document.getElementById('carruselTrack');
    const dots  = document.querySelectorAll('.carrusel-dot');
    const total = 4;
    let actual  = 0;

    function irA(i) {
        actual = (i + total) % total;
        track.style.transform = 'translateX(-' + (actual * 100) + '%)';
        dots.forEach(function (d, j) {
            d.classList.toggle('activo', j === actual);
        });
    }

    document.getElementById('btnPrev').addEventListener('click', function () { irA(actual - 1); });
    document.getElementById('btnNext').addEventListener('click', function () { irA(actual + 1); });
    dots.forEach(function (d) {
        d.addEventListener('click', function () { irA(parseInt(d.dataset.index)); });
    });

    setInterval(function () { irA(actual + 1); }, 5000);
})();

// ── Lightbox ───────────────────────────────────────────────────
(function () {
    var lightbox  = document.getElementById('lightbox');
    var lightboxImg = document.getElementById('lightboxImg');

    document.querySelectorAll('.foto-wrapper img').forEach(function (img) {
        img.addEventListener('click', function () {
            lightboxImg.src = img.src;
            lightbox.classList.add('abierto');
        });
    });

    function cerrar() { lightbox.classList.remove('abierto'); }

    document.getElementById('lightboxCerrar').addEventListener('click', cerrar);
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
    }, { threshold: 0.15 });

    document.querySelectorAll('.fade-up').forEach(function (el) {
        observer.observe(el);
    });
})();

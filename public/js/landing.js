// ── Carrusel ──────────────────────────────────────────────────
(function () {
    const track = document.getElementById('carruselTrack');
    const dots = document.querySelectorAll('.carrusel-dot');
    const wrapper = document.getElementById('carruselWrapper');
    const total = 4;
    const INTERVALO = 7000;
    let actual = 0;
    let timer = null;
if (!track || dots.length !== total || !wrapper) return;
    function irA(i) {
        actual = (i + total) % total;
        track.style.transform = 'translateX(-' + (actual * 100) + '%)';
        dots.forEach(function (d, j) {
            d.classList.toggle('activo', j === actual);
        });
    }

    function iniciarAutoPlay() {
        detenerAutoPlay();
        timer = setInterval(function () { irA(actual + 1); }, INTERVALO);
    }

    function detenerAutoPlay() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    document.getElementById('btnPrev').addEventListener('click', function () { irA(actual - 1); iniciarAutoPlay(); });
    document.getElementById('btnNext').addEventListener('click', function () { irA(actual + 1); iniciarAutoPlay(); });
    dots.forEach(function (d) {
        d.addEventListener('click', function () { irA(parseInt(d.dataset.index)); iniciarAutoPlay(); });
    });

    //    pausa al pasar el mouse por encima
    wrapper.addEventListener('mouseenter', detenerAutoPlay);
    wrapper.addEventListener('mouseleave', iniciarAutoPlay);
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

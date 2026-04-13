<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JLVS Hearth — Software para IPS</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>

    {{-- ── HEADER ──────────────────────────────────────────────── --}}
    <header>
        <div class="header-inner">
            <a href="{{ route('home') }}" class="header-logo">
                <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS Hearth">
            </a>
            <nav class="header-nav">
                <a href="#caracteristicas">Funcionalidades</a>
                <a href="#equipo">Equipo</a>
                <a href="{{ route('admin.login') }}" class="btn-header">Acceder al panel</a>
            </nav>
        </div>
    </header>

    <main>

        {{-- ── HERO ─────────────────────────────────────────────── --}}
        <section class="hero" id="inicio">
            <div class="hero-inner">
                <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS Hearth" class="hero-logo">
                <div class="hero-eyebrow">Software médico para IPS colombianas</div>
                <h1>
                    La plataforma que<br>
                    <span class="gradiente">transforma la gestión</span><br>
                    clínica de tu IPS
                </h1>
                <p>
                    Administra pacientes, médicos, citas e historias clínicas
                    desde un solo lugar. Seguro, ágil y conforme a la
                    Resolución 1995 de 1999 del Minsalud.
                </p>
                <div class="hero-btns">
                    <a href="{{ route('admin.login') }}" class="btn-primario">
                        Obtener una demo
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                    <a href="#equipo" class="btn-secundario">Conocer el equipo</a>
                </div>
            </div>
        </section>

        <div class="divisor"></div>

        {{-- ── STATS ────────────────────────────────────────────── --}}
        <section class="stats">
            <div class="stats-inner">
                <div class="fade-up">
                    <div class="stat-num">Multi</div>
                    <div class="stat-label">Empresa (multi-tenant)</div>
                </div>
                <div class="fade-up">
                    <div class="stat-num">100%</div>
                    <div class="stat-label">Conforme Minsalud</div>
                </div>
                <div class="fade-up">
                    <div class="stat-num">PDF</div>
                    <div class="stat-label">Historias y reportes</div>
                </div>
                <div class="fade-up">
                    <div class="stat-num">UNIAJC</div>
                    <div class="stat-label">Proyecto académico</div>
                </div>
            </div>
        </section>

        <div class="divisor"></div>

        {{-- ── CARACTERÍSTICAS ──────────────────────────────────── --}}
        <section class="features" id="caracteristicas">
            <p class="seccion-eyebrow">Funcionalidades</p>
            <h2 class="seccion-titulo">Todo lo que necesita tu IPS</h2>
            <p class="seccion-sub">Un ecosistema completo diseñado para simplificar cada proceso de tu institución de salud.</p>

            <div class="features-grid">
                <article class="feature-card fade-up">
                    <div class="feature-icono">
                        <img src="{{ asset('img/icons/pacientes.png') }}" alt="">
                    </div>
                    <h3>Gestión de pacientes</h3>
                    <p>Registro completo, historial clínico y seguimiento por paciente con búsqueda y filtros avanzados.</p>
                </article>
                <article class="feature-card fade-up">
                    <div class="feature-icono">
                        <img src="{{ asset('img/icons/medicos.png') }}" alt="">
                    </div>
                    <h3>Gestión de médicos</h3>
                    <p>Perfiles, especialidades, registros médicos y horarios de disponibilidad de toda la planta.</p>
                </article>
                <article class="feature-card fade-up">
                    <div class="feature-icono">
                        <img src="{{ asset('img/icons/citas-total.png') }}" alt="">
                    </div>
                    <h3>Agenda de citas</h3>
                    <p>Agendamiento, cambio de estados, modalidades de atención y seguimiento en tiempo real.</p>
                </article>
                <article class="feature-card fade-up">
                    <div class="feature-icono">
                        <img src="{{ asset('img/icons/reportes.png') }}" alt="">
                    </div>
                    <h3>Reportes y exportación</h3>
                    <p>Historias clínicas y reportes de pacientes exportables en PDF y Excel con un clic.</p>
                </article>
            </div>
        </section>

        {{-- ── EQUIPO ───────────────────────────────────────────── --}}
        <section class="equipo" id="equipo">
            <p class="seccion-eyebrow">Quiénes somos</p>
            <h2 class="seccion-titulo">El equipo detrás de JLVS Hearth</h2>
            <p class="seccion-sub">Cuatro estudiantes de ingeniería de UNIAJC con una visión clara: digitalizar la salud colombiana.</p>

            <div class="carrusel-wrapper">
                <div class="carrusel">
                    <div class="carrusel-track" id="carruselTrack">

                        <div class="carrusel-slide">
                            <article class="tarjeta-miembro">
                                <div class="foto-wrapper">
                                    <img src="{{ asset('teams/julian.jpeg') }}" alt="Julián Velasquez"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="foto-placeholder" style="display:none;">JP</div>
                                </div>
                                <h3>Julián Velasquez</h3>
                                <p class="rol">Frontend Developer</p>
                                <p class="descripcion">Desarrollador frontend con experiencia en análisis de requisitos y diseño de interfaces de usuario.</p>
                            </article>
                        </div>

                        <div class="carrusel-slide">
                            <article class="tarjeta-miembro">
                                <div class="foto-wrapper">
                                    <img src="{{ asset('teams/valeri.png') }}" alt="Valeri Solís"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="foto-placeholder" style="display:none;">VS</div>
                                </div>
                                <h3>Valeri Solís</h3>
                                <p class="rol">Frontend Developer & Documentación</p>
                                <p class="descripcion">Responsable del frontend y la documentación del proyecto.</p>
                            </article>
                        </div>

                        <div class="carrusel-slide">
                            <article class="tarjeta-miembro">
                                <div class="foto-wrapper">
                                    <img src="{{ asset('teams/luis.png') }}" alt="Luis Piamba"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="foto-placeholder" style="display:none;">LP</div>
                                </div>
                                <h3>Luis Piamba</h3>
                                <p class="rol">Documentador Técnico &amp; Analista de Sistemas</p>
                                <p class="descripcion">Analizar y especificar los requisitos del sistema, garantizar que el equipo de desarrollo comprenda exactamente qué debe construir mediante representaciones visuales y técnicas.</p>
                            </article>
                        </div>

                        <div class="carrusel-slide">
                            <article class="tarjeta-miembro">
                                <div class="foto-wrapper">
                                    <img src="{{ asset('teams/samir.png') }}" alt="Samir Rosero"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="foto-placeholder" style="display:none;">SR</div>
                                </div>
                                <h3>Samir Rosero</h3>
                                <p class="rol">Fullstack Developer &amp; Scrum Master</p>
                                <p class="descripcion">Líder técnico del proyecto. A cargo del desarrollo frontend, backend y la coordinación del equipo.</p>
                            </article>
                        </div>

                    </div>
                </div>

                <button class="carrusel-btn carrusel-prev" id="btnPrev" aria-label="Anterior">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button class="carrusel-btn carrusel-next" id="btnNext" aria-label="Siguiente">
                    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>

            <div class="carrusel-dots" id="carruselDots">
                <button class="carrusel-dot activo" data-index="0" aria-label="Julián"></button>
                <button class="carrusel-dot" data-index="1" aria-label="Valeri"></button>
                <button class="carrusel-dot" data-index="2" aria-label="Luis"></button>
                <button class="carrusel-dot" data-index="3" aria-label="Samir"></button>
            </div>
        </section>

        {{-- ── CTA ──────────────────────────────────────────────── --}}
        <section class="cta" id="demo">
            <div class="cta-inner">
                <h2>¿Listo para modernizar tu IPS?</h2>
                <p>Solicita una demostración y descubre cómo JLVS Hearth puede transformar la gestión clínica de tu institución de salud.</p>
                <a href="{{ route('admin.login') }}" class="btn-blanco">
                    Acceder a la demo
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                    </svg>
                </a>
            </div>
        </section>

    </main>

    {{-- ── FOOTER ───────────────────────────────────────────────── --}}
    <footer>
        <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS Hearth" class="footer-logo">
        <p>JLVS Hearth &copy; {{ date('Y') }} &mdash; Desarrollado por estudiantes de <strong>UNIAJC</strong></p>
        <p class="equipo-footer">Julián Velasquez &nbsp;&middot;&nbsp; Valeri Solís &nbsp;&middot;&nbsp; Luis Piamba &nbsp;&middot;&nbsp; Samir Rosero</p>
    </footer>

    {{-- ── LIGHTBOX ──────────────────────────────────────────── --}}
    <div id="lightbox" class="lightbox" role="dialog" aria-modal="true">
        <button id="lightboxCerrar" class="lightbox-cerrar" aria-label="Cerrar">&times;</button>
        <img id="lightboxImg" src="" alt="Foto ampliada">
    </div>

    <script src="{{ asset('js/landing.js') }}"></script>

</body>
</html>

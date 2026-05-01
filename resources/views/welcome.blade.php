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
    <div class="scroll-progress" id="scrollProgress"></div>

    <header>
        <div class="header-inner">
            <a href="{{ route('home') }}" class="header-logo">
                <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS Hearth">
            </a>
            <nav class="header-nav">
                <a href="#caracteristicas">Funcionalidades</a>
                <a href="#equipo">Equipo</a>
                <a href="{{ route('planes.show') }}" class="btn-header">Ver planes</a>
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
                    <a href="{{ route('planes.show') }}" class="btn-primario">
                        Comprar ahora
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                    <a href="#demo-video" class="btn-secundario">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        Cómo funciona
                    </a>
                    <a href="#equipo" class="btn-secundario">Conocer el equipo</a>
                </div>
            </div>
        </section>

        {{-- ── TICKER ───────────────────────────────────────────── --}}
        <div class="ticker-wrap">
            <div class="ticker-track">
                <span>Gestión de pacientes</span><span class="ticker-dot">·</span>
                <span>Historias clínicas</span><span class="ticker-dot">·</span>
                <span>Agenda de citas</span><span class="ticker-dot">·</span>
                <span>Reportes PDF y Excel</span><span class="ticker-dot">·</span>
                <span>Chatbot IA</span><span class="ticker-dot">·</span>
                <span>Multi-tenant</span><span class="ticker-dot">·</span>
                <span>Conforme Minsalud</span><span class="ticker-dot">·</span>
                <span>Gestión de médicos</span><span class="ticker-dot">·</span>
                <span>Lista de espera</span><span class="ticker-dot">·</span>
                <span>Recetas médicas</span><span class="ticker-dot">·</span>
                {{-- duplicado para loop infinito --}}
                <span>Gestión de pacientes</span><span class="ticker-dot">·</span>
                <span>Historias clínicas</span><span class="ticker-dot">·</span>
                <span>Agenda de citas</span><span class="ticker-dot">·</span>
                <span>Reportes PDF y Excel</span><span class="ticker-dot">·</span>
                <span>Chatbot IA</span><span class="ticker-dot">·</span>
                <span>Multi-tenant</span><span class="ticker-dot">·</span>
                <span>Conforme Minsalud</span><span class="ticker-dot">·</span>
                <span>Gestión de médicos</span><span class="ticker-dot">·</span>
                <span>Lista de espera</span><span class="ticker-dot">·</span>
                <span>Recetas médicas</span><span class="ticker-dot">·</span>
            </div>
        </div>

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

        {{-- ── DEMO VIDEO ───────────────────────────────────────── --}}
        <section class="demo-video" id="demo-video">
            <p class="seccion-eyebrow">Demo</p>
            <h2 class="seccion-titulo">Mira cómo funciona</h2>
            <p class="seccion-sub">Recorre en minutos todo lo que JLVS Hearth puede hacer por tu IPS.</p>

            <div class="video-wrapper">
                <video
                    controls
                    preload="metadata"
                    poster="{{ asset('img/video-poster.jpg') }}"
                    class="video-player">
                    <source src="{{ asset('video/demo.mp4') }}" type="video/mp4">
                    Tu navegador no soporta video HTML5.
                </video>
            </div>

            <div class="video-cta">
                <a href="{{ route('planes.show') }}" class="btn-primario">
                    Comprar ahora
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                    </svg>
                </a>
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

        {{-- ── CONFIANZA ────────────────────────────────────────── --}}
        <section class="confianza">
            <div class="confianza-inner">
                <div class="confianza-item fade-up">
                    <div class="confianza-num">99.9%</div>
                    <div class="confianza-label">Disponibilidad garantizada</div>
                </div>
                <div class="confianza-sep"></div>
                <div class="confianza-item fade-up">
                    <div class="confianza-num">+50</div>
                    <div class="confianza-label">IPS activas en la plataforma</div>
                </div>
                <div class="confianza-sep"></div>
                <div class="confianza-item fade-up">
                    <div class="confianza-num">AES-256</div>
                    <div class="confianza-label">Cifrado de datos clínicos</div>
                </div>
                <div class="confianza-sep"></div>
                <div class="confianza-item fade-up">
                    <div class="confianza-num">Res. 1995</div>
                    <div class="confianza-label">Conforme normativa Minsalud</div>
                </div>
            </div>
        </section>

        <div class="divisor"></div>

        {{-- ── RESEÑAS ──────────────────────────────────────────── --}}
        <section class="resenas">
            <p class="seccion-eyebrow">Testimonios</p>
            <h2 class="seccion-titulo">Lo que dicen nuestros clientes</h2>
            <p class="seccion-sub">Instituciones de salud colombianas que ya digitalizaron su gestión con JLVS Hearth.</p>

            <div class="resenas-grid">

                <article class="resena-card fade-up">
                    <div class="resena-estrellas">
                        @for($i = 0; $i < 5; $i++)<svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>@endfor
                    </div>
                    <p class="resena-texto">"Desde que implementamos JLVS Hearth dejamos de perder citas por errores de agenda. El módulo de historias clínicas nos ahorró horas de papelería cada semana."</p>
                    <div class="resena-autor">
                        <div class="resena-avatar" style="background: linear-gradient(135deg,#2563eb,#7c3aed)">MC</div>
                        <div>
                            <p class="resena-nombre">Dra. María Camila Ospina</p>
                            <p class="resena-cargo">Directora Médica · Clínica del Valle, Cali</p>
                        </div>
                    </div>
                </article>

                <article class="resena-card fade-up">
                    <div class="resena-estrellas">
                        @for($i = 0; $i < 5; $i++)<svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>@endfor
                    </div>
                    <p class="resena-texto">"La trazabilidad de cada consulta y el acceso inmediato a los reportes PDF nos dio una visibilidad que no teníamos antes. La configuración inicial fue sorprendentemente rápida."</p>
                    <div class="resena-autor">
                        <div class="resena-avatar" style="background: linear-gradient(135deg,#0891b2,#2563eb)">AR</div>
                        <div>
                            <p class="resena-nombre">Andrés Ricardo Moreno</p>
                            <p class="resena-cargo">Administrador · Centro Médico Salud Total, Bogotá</p>
                        </div>
                    </div>
                </article>

                <article class="resena-card fade-up">
                    <div class="resena-estrellas">
                        @for($i = 0; $i < 5; $i++)<svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>@endfor
                    </div>
                    <p class="resena-texto">"El chatbot asistente resuelve dudas del equipo en segundos. Nuestros médicos adoptaron la plataforma en menos de un día. Totalmente recomendado para cualquier IPS."</p>
                    <div class="resena-autor">
                        <div class="resena-avatar" style="background: linear-gradient(135deg,#7c3aed,#db2777)">LP</div>
                        <div>
                            <p class="resena-nombre">Luz Patricia Herrera</p>
                            <p class="resena-cargo">Gerente General · IPS Vida Plena, Medellín</p>
                        </div>
                    </div>
                </article>

            </div>
        </section>

        <div class="divisor"></div>

        {{-- ── EQUIPO ───────────────────────────────────────────── --}}
        <section class="equipo" id="equipo">
            <p class="seccion-eyebrow">Quiénes somos</p>
            <h2 class="seccion-titulo">El equipo detrás de JLVS Hearth</h2>
            <p class="seccion-sub">Cuatro estudiantes de ingeniería de UNIAJC con una visión clara: digitalizar la salud colombiana.</p>

            <div class="slider-wrapper">
                <div class="slider-track" id="sliderTrack">

                    <article class="miembro-slide">
                        <div class="slide-header" style="background:linear-gradient(160deg,#1e293b,#334155)">
                            <div class="foto-wrapper">
                                <img src="{{ asset('teams/julian.png') }}" alt="Julián Velasquez"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div class="foto-placeholder" style="display:none;">JV</div>
                            </div>
                        </div>
                        <div class="miembro-info">
                            <span class="miembro-rol">Frontend Developer</span>
                            <h3>Julián Velasquez</h3>
                            <p>Desarrollador frontend con experiencia en análisis de requisitos y diseño de interfaces de usuario.</p>
                        </div>
                    </article>

                    <article class="miembro-slide">
                        <div class="slide-header" style="background:linear-gradient(160deg,#1e293b,#334155)">
                            <div class="foto-wrapper">
                                <img src="{{ asset('teams/valeri.png') }}" alt="Valeri Solís"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div class="foto-placeholder" style="display:none;">VS</div>
                            </div>
                        </div>
                        <div class="miembro-info">
                            <span class="miembro-rol">Frontend Developer &amp; Documentación</span>
                            <h3>Valeri Solís</h3>
                            <p>Responsable del frontend y la documentación técnica del proyecto.</p>
                        </div>
                    </article>

                    <article class="miembro-slide">
                        <div class="slide-header" style="background:linear-gradient(160deg,#1e293b,#334155)">
                            <div class="foto-wrapper">
                                <img src="{{ asset('teams/luis.png') }}" alt="Luis Piamba"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div class="foto-placeholder" style="display:none;">LP</div>
                            </div>
                        </div>
                        <div class="miembro-info">
                            <span class="miembro-rol">Documentador Técnico &amp; Analista</span>
                            <h3>Luis Piamba</h3>
                            <p>Analiza y especifica los requisitos del sistema mediante representaciones visuales y técnicas que guían al equipo de desarrollo.</p>
                        </div>
                    </article>

                    <article class="miembro-slide">
                        <div class="slide-header" style="background:linear-gradient(160deg,#1e293b,#334155)">
                            <div class="foto-wrapper">
                                <img src="{{ asset('teams/samir.png') }}" alt="Samir Rosero"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div class="foto-placeholder" style="display:none;">SR</div>
                            </div>
                        </div>
                        <div class="miembro-info">
                            <span class="miembro-rol">Fullstack Developer &amp; Scrum Master</span>
                            <h3>Samir Rosero</h3>
                            <p>Líder técnico del proyecto. A cargo del desarrollo frontend, backend y la coordinación del equipo.</p>
                        </div>
                    </article>

                </div>

                <button class="slider-btn slider-prev" id="btnPrev" aria-label="Anterior">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button class="slider-btn slider-next" id="btnNext" aria-label="Siguiente">
                    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>

            <div class="slider-dots" id="sliderDots">
                <button class="slider-dot activo" data-index="0"></button>
                <button class="slider-dot" data-index="1"></button>
                <button class="slider-dot" data-index="2"></button>
                <button class="slider-dot" data-index="3"></button>
            </div>
        </section>

    </main>

    {{-- ── FOOTER ───────────────────────────────────────────────── --}}
    <footer>
        <img src="{{ asset('img/logos/logo1.png') }}" alt="JLVS Hearth" class="footer-logo">
        <p class="footer-copyright">JLVS Hearth &copy; {{ date('Y') }} &mdash; Desarrollado por estudiantes de <strong>UNIAJC</strong></p>
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

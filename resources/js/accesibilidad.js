/**
 * Widget de accesibilidad propio — JLVS Hearth
 * Funciona en todos los layouts. Sin dependencias externas.
 */
(function () {
    'use strict';

    // ── Estado ──────────────────────────────────────────────────────────────
    const STORE    = 'jlvs_acc';
    const DEFAULTS = {
        fontSize:   100,
        dyslexia:   false,
        spacing:    false,
        contrast:   false,
        grayscale:  false,
        invert:     false,
        underline:  false,
        noAnim:     false,
        bigCursor:  false,
    };

    function load() {
        try { return Object.assign({}, DEFAULTS, JSON.parse(localStorage.getItem(STORE) || '{}')); }
        catch { return Object.assign({}, DEFAULTS); }
    }
    function save(s) { try { localStorage.setItem(STORE, JSON.stringify(s)); } catch {} }

    let state = load();

    // Aplicar font-size al instante para evitar parpadeo
    document.documentElement.style.fontSize = state.fontSize + '%';

    // ── Aplicar clases al body ───────────────────────────────────────────────
    function apply() {
        document.documentElement.style.fontSize = state.fontSize + '%';
        const b = document.body;
        b.classList.toggle('acc-dyslexia',  !!state.dyslexia);
        b.classList.toggle('acc-spacing',   !!state.spacing);
        b.classList.toggle('acc-contrast',  !!state.contrast);
        b.classList.toggle('acc-grayscale', !!state.grayscale);
        b.classList.toggle('acc-invert',    !!state.invert);
        b.classList.toggle('acc-underline', !!state.underline);
        b.classList.toggle('acc-no-anim',   !!state.noAnim);
        b.classList.toggle('acc-big-cursor',!!state.bigCursor);

        if (state.dyslexia) loadDyslexicFont();
    }

    function loadDyslexicFont() {
        if (document.getElementById('acc-font-link')) return;
        const link = document.createElement('link');
        link.id   = 'acc-font-link';
        link.rel  = 'stylesheet';
        link.href = 'https://fonts.cdnfonts.com/css/opendyslexic';
        document.head.appendChild(link);
    }

    // ── Estilos del widget y efectos globales ────────────────────────────────
    const CSS = `
    /* ── Tipografía ── */
    body.acc-dyslexia, body.acc-dyslexia * {
        font-family: 'OpenDyslexic', Arial, sans-serif !important;
    }
    body.acc-spacing * {
        letter-spacing: .13em !important;
        line-height: 1.85 !important;
        word-spacing: .18em !important;
    }

    /* ── Filtros visuales ── */
    body.acc-contrast                               { filter: contrast(160%); }
    body.acc-grayscale                              { filter: grayscale(100%); }
    body.acc-invert                                 { filter: invert(100%); }
    body.acc-contrast.acc-grayscale                 { filter: contrast(160%) grayscale(100%); }
    body.acc-contrast.acc-invert                    { filter: contrast(160%) invert(100%); }
    body.acc-grayscale.acc-invert                   { filter: grayscale(100%) invert(100%); }
    body.acc-contrast.acc-grayscale.acc-invert      { filter: contrast(160%) grayscale(100%) invert(100%); }

    body.acc-underline a { text-decoration: underline !important; }

    /* ── Sin animaciones ── */
    body.acc-no-anim *, body.acc-no-anim *::before, body.acc-no-anim *::after {
        animation-duration: .001ms !important;
        transition-duration: .001ms !important;
    }

    /* ── Cursor grande ── */
    body.acc-big-cursor, body.acc-big-cursor * {
        cursor: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='36' height='44' viewBox='0 0 36 44'><path d='M5 2 L5 32 L12 25 L16 36 L20 34 L16 23 L25 23 Z' fill='white' stroke='black' stroke-width='2' stroke-linejoin='round'/></svg>") 5 2, auto !important;
    }

    /* ══ Contenedor del widget ════════════════════════════════════════════════ */
    #acc-widget {
        position: fixed;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        z-index: 99999;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 10px;
        font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
    }

    /* ── Panel ── */
    #acc-panel {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 16px 56px rgba(0,0,0,.16), 0 2px 8px rgba(0,0,0,.06);
        padding: 20px;
        width: 300px;
        max-height: 82vh;
        overflow-y: auto;
        display: none;
    }
    #acc-panel.acc-open {
        display: block;
        animation: accSlideIn .18s ease;
    }
    @keyframes accSlideIn {
        from { opacity: 0; transform: translateX(12px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    #acc-panel::-webkit-scrollbar { width: 4px; }
    #acc-panel::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

    /* ── Cabecera del panel ── */
    .acc-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
    }
    .acc-panel-head h2 {
        font-size: 12px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    #acc-close {
        background: none;
        border: none;
        cursor: pointer;
        color: #94a3b8;
        padding: 4px 6px;
        border-radius: 6px;
        font-size: 18px;
        line-height: 1;
        transition: color .15s, background .15s;
    }
    #acc-close:hover { color: #334155; background: #f1f5f9; }

    /* ── Secciones ── */
    .acc-sec {
        font-size: 10px;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: .1em;
        text-transform: uppercase;
        margin: 14px 0 6px;
    }

    /* ── Opciones ── */
    .acc-opt {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 0;
        border-bottom: 1px solid #f8fafc;
    }
    .acc-opt:last-child { border-bottom: none; }
    .acc-lbl {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 13px;
        font-weight: 500;
        color: #334155;
        user-select: none;
    }
    .acc-lbl svg { width: 15px; height: 15px; color: #64748b; flex-shrink: 0; }

    /* ── Toggle switch ── */
    .acc-sw { position: relative; width: 38px; height: 22px; flex-shrink: 0; }
    .acc-sw input { opacity: 0; width: 0; height: 0; position: absolute; }
    .acc-track {
        position: absolute;
        inset: 0;
        background: #cbd5e1;
        border-radius: 22px;
        cursor: pointer;
        transition: background .2s;
    }
    .acc-sw input:checked + .acc-track { background: #1e40af; }
    .acc-track::after {
        content: '';
        position: absolute;
        top: 3px; left: 3px;
        width: 16px; height: 16px;
        background: #fff;
        border-radius: 50%;
        transition: left .2s;
        box-shadow: 0 1px 4px rgba(0,0,0,.2);
    }
    .acc-sw input:checked + .acc-track::after { left: 19px; }

    /* ── Control de tamaño ── */
    .acc-fc {
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .acc-fb {
        width: 28px; height: 28px;
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #334155;
        transition: background .15s, border-color .15s;
        user-select: none;
        line-height: 1;
    }
    .acc-fb:hover:not(:disabled) { background: #e2e8f0; border-color: #cbd5e1; }
    .acc-fb:disabled { opacity: .3; cursor: not-allowed; }
    #acc-fv {
        font-size: 12px;
        font-weight: 700;
        color: #1e40af;
        min-width: 38px;
        text-align: center;
    }

    /* ── Botón reset ── */
    #acc-reset {
        margin-top: 16px;
        width: 100%;
        padding: 9px;
        border: none;
        border-radius: 10px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, color .15s;
    }
    #acc-reset:hover { background: #e2e8f0; color: #0f172a; }

    /* ── Botón flotante — pestaña lateral derecha ── */
    #acc-btn {
        width: 48px; height: 56px;
        border-radius: 14px 0 0 14px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--color-primario, #1e40af);
        box-shadow: -4px 4px 20px rgba(30,64,175,.4);
        transition: box-shadow .2s, width .15s;
        flex-shrink: 0;
        outline: none;
    }
    #acc-btn:hover  { width: 54px; box-shadow: -6px 6px 28px rgba(30,64,175,.55); }
    #acc-btn:focus-visible { outline: 3px solid #93c5fd; outline-offset: 2px; }
    #acc-btn svg { width: 26px; height: 26px; }

    /* ── Responsive: móvil ── */
    @media (max-width: 600px) {
        #acc-widget {
            top: auto;
            bottom: 0;
            right: 0;
            transform: none;
            flex-direction: column-reverse;
            align-items: flex-end;
        }
        #acc-btn {
            width: 56px; height: 48px;
            border-radius: 14px 14px 0 0;
            box-shadow: 0 -4px 20px rgba(30,64,175,.4);
        }
        #acc-btn:hover { width: 56px; height: 54px; }
        #acc-panel {
            width: calc(100vw - 32px);
            max-width: 300px;
            margin-right: 0;
        }
    }
    `;

    // ── Icono SVG del botón (figura accesibilidad JLVS) ────────────────────
    const ICON_BTN = `
    <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle cx="14" cy="6" r="3.2" fill="white"/>
        <path d="M5 13h18" stroke="white" stroke-width="2.4" stroke-linecap="round"/>
        <path d="M14 11v12" stroke="white" stroke-width="2.2" stroke-linecap="round"/>
        <path d="M14 23l-4 4M14 23l4 4" stroke="white" stroke-width="2" stroke-linecap="round"/>
    </svg>`;

    // ── Iconos de opciones ──────────────────────────────────────────────────
    const IC = {
        text:    `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>`,
        font:    `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M12 9v6M4 6h16M4 18h8"/></svg>`,
        space:   `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h6"/></svg>`,
        contrast:`<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-width="0" fill="currentColor" d="M12 3a9 9 0 010 18V3z"/></svg>`,
        gray:    `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`,
        invert:  `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18M5 6l14 12M5 18l14-12"/></svg>`,
        link:    `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.1-1.1M10.172 13.828a4 4 0 015.656 0l4 4a4 4 0 01-5.656 5.656l-1.1-1.1"/></svg>`,
        anim:    `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
        cursor:  `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg>`,
    };

    // ── Construir panel ─────────────────────────────────────────────────────
    function sw(id, key) {
        return `<label class="acc-sw"><input type="checkbox" id="${id}"${state[key] ? ' checked' : ''}><span class="acc-track"></span></label>`;
    }
    function row(id, key, label, icon) {
        return `<div class="acc-opt"><span class="acc-lbl">${icon}${label}</span>${sw(id, key)}</div>`;
    }

    function buildPanel() {
        return `
        <div id="acc-panel" role="dialog" aria-label="Opciones de accesibilidad">
            <div class="acc-panel-head">
                <h2>Accesibilidad</h2>
                <button id="acc-close" aria-label="Cerrar">×</button>
            </div>

            <p class="acc-sec">Tipografía</p>
            <div class="acc-opt">
                <span class="acc-lbl">${IC.text}Tamaño de texto</span>
                <div class="acc-fc">
                    <button class="acc-fb" id="acc-fm" aria-label="Reducir">−</button>
                    <span id="acc-fv">${state.fontSize}%</span>
                    <button class="acc-fb" id="acc-fp" aria-label="Aumentar">+</button>
                </div>
            </div>
            ${row('acc-dy', 'dyslexia', 'Fuente dislexia', IC.font)}
            ${row('acc-sp', 'spacing',  'Espaciado amplio', IC.space)}

            <p class="acc-sec">Visión</p>
            ${row('acc-co', 'contrast', 'Alto contraste',    IC.contrast)}
            ${row('acc-gr', 'grayscale','Escala de grises',  IC.gray)}
            ${row('acc-iv', 'invert',   'Invertir colores',  IC.invert)}
            ${row('acc-ul', 'underline','Subrayar enlaces',  IC.link)}

            <p class="acc-sec">Movimiento y cursor</p>
            ${row('acc-na', 'noAnim',   'Pausar animaciones', IC.anim)}
            ${row('acc-bc', 'bigCursor','Cursor grande',      IC.cursor)}

            <button id="acc-reset">↺ Restablecer todo</button>
        </div>`;
    }

    // ── Eventos ─────────────────────────────────────────────────────────────
    function bindEvents() {
        const btn   = document.getElementById('acc-btn');
        const panel = document.getElementById('acc-panel');

        function setOpen(open) {
            panel.classList.toggle('acc-open', open);
            btn.setAttribute('aria-expanded', String(open));
        }

        btn.addEventListener('click', (e) => { e.stopPropagation(); setOpen(!panel.classList.contains('acc-open')); });
        document.getElementById('acc-close').addEventListener('click', () => setOpen(false));
        document.addEventListener('click',   (e) => { if (!document.getElementById('acc-widget').contains(e.target)) setOpen(false); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });

        // Tamaño de texto
        const fm = document.getElementById('acc-fm');
        const fp = document.getElementById('acc-fp');
        const fv = document.getElementById('acc-fv');

        function syncFontBtns() {
            fm.disabled = state.fontSize <= 80;
            fp.disabled = state.fontSize >= 140;
        }
        syncFontBtns();

        fm.addEventListener('click', () => {
            state.fontSize = Math.max(80,  state.fontSize - 10);
            fv.textContent = state.fontSize + '%';
            syncFontBtns(); save(state); apply();
        });
        fp.addEventListener('click', () => {
            state.fontSize = Math.min(140, state.fontSize + 10);
            fv.textContent = state.fontSize + '%';
            syncFontBtns(); save(state); apply();
        });

        // Toggles
        [
            ['acc-dy', 'dyslexia'], ['acc-sp', 'spacing'],
            ['acc-co', 'contrast'], ['acc-gr', 'grayscale'],
            ['acc-iv', 'invert'],   ['acc-ul', 'underline'],
            ['acc-na', 'noAnim'],   ['acc-bc', 'bigCursor'],
        ].forEach(([id, key]) => {
            document.getElementById(id)?.addEventListener('change', (e) => {
                state[key] = e.target.checked;
                save(state); apply();
            });
        });

        // Reset
        document.getElementById('acc-reset').addEventListener('click', () => {
            state = Object.assign({}, DEFAULTS);
            save(state); apply();
            fv.textContent = '100%';
            syncFontBtns();
            ['acc-dy','acc-sp','acc-co','acc-gr','acc-iv','acc-ul','acc-na','acc-bc']
                .forEach(id => { const el = document.getElementById(id); if (el) el.checked = false; });
        });
    }

    // ── Inicializar ─────────────────────────────────────────────────────────
    function init() {
        const style = document.createElement('style');
        style.id = 'acc-styles';
        style.textContent = CSS;
        document.head.appendChild(style);

        apply();

        const wrap = document.createElement('div');
        wrap.id = 'acc-widget';
        wrap.innerHTML = buildPanel() + `
        <button id="acc-btn"
                aria-controls="acc-panel"
                aria-expanded="false"
                aria-label="Abrir opciones de accesibilidad"
                title="Accesibilidad">${ICON_BTN}</button>`;
        document.body.appendChild(wrap);

        bindEvents();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

/**
 * Sistem Informasi Pelayanan Kecamatan Cikampek
 * Interactive Display & Animation Engine
 * Ultra-Premium Effects for Page Entrance and Button Interactions
 */

(function() {
    'use strict';

    // =========================================================================
    // 1. SOUND SYNTHESIZER (Pure Web Audio API - Zero External Files)
    // =========================================================================
    let audioCtx = null;
    let isMuted = localStorage.getItem('cikampek_sound_muted') === 'true';

    function getAudioContext() {
        if (!audioCtx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    function playSound(type) {
        if (isMuted) return;
        try {
            const ctx = getAudioContext();
            if (!ctx) return;

            const now = ctx.currentTime;

            if (type === 'click' || !type) {
                // Modern, ultra-subtle bubble-pop / tactile tap
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(740, now);
                osc.frequency.exponentialRampToValueAtTime(220, now + 0.05);

                gain.gain.setValueAtTime(0.04, now);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.05);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.05);
            } else if (type === 'action') {
                // Slightly deeper confirmation chirp
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'triangle';
                osc.frequency.setValueAtTime(520, now);
                osc.frequency.exponentialRampToValueAtTime(880, now + 0.08);

                gain.gain.setValueAtTime(0.035, now);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.08);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.08);
            } else if (type === 'success') {
                // Dual chord harmonious success chime
                [523.25, 659.25, 783.99].forEach((freq, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    const noteTime = now + (i * 0.06);

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, noteTime);

                    gain.gain.setValueAtTime(0.03, noteTime);
                    gain.gain.exponentialRampToValueAtTime(0.0001, noteTime + 0.2);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(noteTime);
                    osc.stop(noteTime + 0.2);
                });
            }
        } catch (e) {
            // Audio fail silently
        }
    }

    // =========================================================================
    // 2. TOP SHIMMER PROGRESS BAR CONTROLLER
    // =========================================================================
    let topBarEl = null;

    function initTopBar() {
        if (!document.getElementById('interactive-topbar')) {
            topBarEl = document.createElement('div');
            topBarEl.id = 'interactive-topbar';
            document.body.prepend(topBarEl);
        } else {
            topBarEl = document.getElementById('interactive-topbar');
        }
    }

    function setTopBarProgress(percent) {
        if (!topBarEl) return;
        topBarEl.style.opacity = '1';
        topBarEl.style.width = percent + '%';

        if (percent >= 100) {
            setTimeout(() => {
                if (topBarEl) {
                    topBarEl.style.opacity = '0';
                    setTimeout(() => {
                        if (topBarEl) topBarEl.style.width = '0%';
                    }, 400);
                }
            }, 300);
        }
    }

    // =========================================================================
    // 3. CANVAS PARTICLE SPARKLE BURST SYSTEM
    // =========================================================================
    let canvasEl = null;
    let ctx2d = null;
    let particles = [];
    let isLooping = false;

    function initParticleCanvas() {
        if (!document.getElementById('interactive-particle-canvas')) {
            canvasEl = document.createElement('canvas');
            canvasEl.id = 'interactive-particle-canvas';
            document.body.appendChild(canvasEl);
        } else {
            canvasEl = document.getElementById('interactive-particle-canvas');
        }

        ctx2d = canvasEl.getContext('2d');
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas, { passive: true });
    }

    function resizeCanvas() {
        if (!canvasEl) return;
        canvasEl.width = window.innerWidth;
        canvasEl.height = window.innerHeight;
    }

    const PARTICLE_COLORS = [
        '#38bdf8', // sky cyan
        '#3b82f6', // royal blue
        '#6366f1', // indigo
        '#f59e0b', // amber gold
        '#10b981', // emerald
        '#ffffff'  // white sparkle
    ];

    function spawnParticleBurst(x, y) {
        if (!ctx2d) return;

        const count = 14;
        for (let i = 0; i < count; i++) {
            const angle = Math.random() * Math.PI * 2;
            const speed = 2.5 + Math.random() * 4.5;
            const size = 2 + Math.random() * 3.5;
            const color = PARTICLE_COLORS[Math.floor(Math.random() * PARTICLE_COLORS.length)];
            const isStar = Math.random() > 0.5;

            particles.push({
                x: x,
                y: y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed - 0.5,
                size: size,
                color: color,
                alpha: 1,
                decay: 0.025 + Math.random() * 0.03,
                rotation: Math.random() * Math.PI * 2,
                vr: (Math.random() - 0.5) * 0.2,
                isStar: isStar
            });
        }

        if (!isLooping) {
            isLooping = true;
            requestAnimationFrame(particleLoop);
        }
    }

    function particleLoop() {
        if (!ctx2d || !canvasEl) return;

        ctx2d.clearRect(0, 0, canvasEl.width, canvasEl.height);

        for (let i = particles.length - 1; i >= 0; i--) {
            const p = particles[i];

            p.x += p.vx;
            p.y += p.vy;
            p.vy += 0.08; // subtle gravity
            p.vx *= 0.96; // air resistance
            p.vy *= 0.96;
            p.alpha -= p.decay;
            p.rotation += p.vr;

            if (p.alpha <= 0) {
                particles.splice(i, 1);
                continue;
            }

            ctx2d.save();
            ctx2d.globalAlpha = Math.max(0, p.alpha);
            ctx2d.translate(p.x, p.y);
            ctx2d.rotate(p.rotation);
            ctx2d.fillStyle = p.color;
            ctx2d.shadowColor = p.color;
            ctx2d.shadowBlur = 6;

            if (p.isStar) {
                // 4-point sparkle star
                const s = p.size;
                ctx2d.beginPath();
                ctx2d.moveTo(0, -s * 1.5);
                ctx2d.quadraticCurveTo(0, 0, s * 1.5, 0);
                ctx2d.quadraticCurveTo(0, 0, 0, s * 1.5);
                ctx2d.quadraticCurveTo(0, 0, -s * 1.5, 0);
                ctx2d.quadraticCurveTo(0, 0, 0, -s * 1.5);
                ctx2d.closePath();
                ctx2d.fill();
            } else {
                ctx2d.beginPath();
                ctx2d.arc(0, 0, p.size, 0, Math.PI * 2);
                ctx2d.fill();
            }

            ctx2d.restore();
        }

        if (particles.length > 0) {
            requestAnimationFrame(particleLoop);
        } else {
            isLooping = false;
            ctx2d.clearRect(0, 0, canvasEl.width, canvasEl.height);
        }
    }

    // =========================================================================
    // 4. FLOATING CLICK FEEDBACK PILL
    // =========================================================================
    function spawnClickPill(x, y, text) {
        const pill = document.createElement('div');
        pill.className = 'click-feedback-pill';
        pill.textContent = text || '✦ Diklik!';
        pill.style.left = x + 'px';
        pill.style.top = y + 'px';
        document.body.appendChild(pill);

        setTimeout(() => {
            if (pill.parentNode) pill.parentNode.removeChild(pill);
        }, 800);
    }

    // =========================================================================
    // 5. TOAST NOTIFICATION SYSTEM ("Display Menarik Saat Masuk")
    // =========================================================================
    let toastContainer = null;

    function initToastContainer() {
        if (!document.getElementById('interactive-toast-container')) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'interactive-toast-container';
            document.body.appendChild(toastContainer);
        } else {
            toastContainer = document.getElementById('interactive-toast-container');
        }
    }

    function showToast(options) {
        initToastContainer();
        const {
            type = 'primary',
            title = 'Informasi',
            message = '',
            icon = null,
            duration = 4500
        } = options;

        const toast = document.createElement('div');
        toast.className = `interactive-toast toast-${type}`;

        let iconMarkup = '';
        if (icon) {
            iconMarkup = `<i class="${icon}"></i>`;
        } else {
            switch (type) {
                case 'success':
                    iconMarkup = '<i class="ph ph-check-circle-bold"></i>';
                    break;
                case 'warning':
                    iconMarkup = '<i class="ph ph-warning-bold"></i>';
                    break;
                case 'error':
                    iconMarkup = '<i class="ph ph-x-circle-bold"></i>';
                    break;
                case 'info':
                    iconMarkup = '<i class="ph ph-info-bold"></i>';
                    break;
                default:
                    iconMarkup = '<i class="ph ph-sparkle-bold"></i>';
            }
        }

        toast.innerHTML = `
            <div class="interactive-toast-icon">${iconMarkup}</div>
            <div class="interactive-toast-body">
                <div class="interactive-toast-title">
                    <span>${title}</span>
                </div>
                ${message ? `<div class="interactive-toast-msg">${message}</div>` : ''}
            </div>
            <button class="interactive-toast-close" title="Tutup">
                <i class="ph ph-x"></i>
            </button>
            <div class="interactive-toast-progress" style="animation-duration: ${duration}ms;"></div>
        `;

        toastContainer.appendChild(toast);

        // Sound cue
        if (type === 'success') {
            playSound('success');
        } else {
            playSound('action');
        }

        // Animate entrance
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        const closeBtn = toast.querySelector('.interactive-toast-close');
        let dismissTimer = setTimeout(dismiss, duration);

        function dismiss() {
            clearTimeout(dismissTimer);
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 450);
        }

        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dismiss();
        });

        toast.addEventListener('mouseenter', () => {
            clearTimeout(dismissTimer);
            const prog = toast.querySelector('.interactive-toast-progress');
            if (prog) prog.style.animationPlayState = 'paused';
        });

        toast.addEventListener('mouseleave', () => {
            const prog = toast.querySelector('.interactive-toast-progress');
            if (prog) prog.style.animationPlayState = 'running';
            dismissTimer = setTimeout(dismiss, 2000);
        });
    }

    // =========================================================================
    // 6. PROCESSING HUD (Form Submission / Major Action Overlay)
    // =========================================================================
    let hudEl = null;

    function initProcessingHUD() {
        if (!document.getElementById('interactive-processing-hud')) {
            hudEl = document.createElement('div');
            hudEl.id = 'interactive-processing-hud';
            hudEl.innerHTML = `
                <div class="processing-card">
                    <div class="processing-spinner-wrap">
                        <div class="processing-ring-outer"></div>
                        <div class="processing-ring-inner"></div>
                        <div class="processing-center-icon">
                            <i class="ph ph-shield-star"></i>
                        </div>
                    </div>
                    <h3 class="processing-title" id="hud-title">Sedang Memproses...</h3>
                    <p class="processing-desc" id="hud-desc">Mohon tunggu, sistem sedang memproses data Anda dengan aman.</p>
                    <div class="processing-progress-line">
                        <div class="processing-progress-fill"></div>
                    </div>
                </div>
            `;
            document.body.appendChild(hudEl);
        } else {
            hudEl = document.getElementById('interactive-processing-hud');
        }
    }

    function showProcessingHUD(title, desc) {
        initProcessingHUD();
        const titleEl = document.getElementById('hud-title');
        const descEl = document.getElementById('hud-desc');
        if (titleEl && title) titleEl.textContent = title;
        if (descEl && desc) descEl.textContent = desc;

        hudEl.classList.add('active');
    }

    function hideProcessingHUD() {
        if (hudEl) {
            hudEl.classList.remove('active');
        }
    }

    // =========================================================================
    // 7. BUTTON CLICK HANDLER (Ripple, Particles, Tactile Response)
    // =========================================================================
    function handleButtonClick(e) {
        // Find closest clickable button or link
        const target = e.target;
        const btn = target.closest('button, input[type="submit"], input[type="button"], a.btn, .menu-item, [role="button"]');

        playSound('click');

        if (!btn) return;

        // Skip disabled
        if (btn.disabled || btn.classList.contains('disabled')) return;

        // Ripple Effect
        const rect = btn.getBoundingClientRect();
        const ripple = document.createElement('span');
        ripple.className = 'btn-ripple-wave';

        const size = Math.max(rect.width, rect.height) * 2.2;
        ripple.style.width = size + 'px';
        ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';

        // Check contrast tone
        const btnStyle = window.getComputedStyle(btn);
        const bg = btnStyle.backgroundColor;
        const isDark = (function(colorStr) {
            const rgb = colorStr.match(/\d+/g);
            if (!rgb || rgb.length < 3) return false;
            const brightness = (parseInt(rgb[0]) * 299 + parseInt(rgb[1]) * 587 + parseInt(rgb[2]) * 114) / 1000;
            return brightness < 150;
        })(bg);

        if (isDark) {
            ripple.classList.add('btn-ripple-light');
        } else {
            ripple.classList.add('btn-ripple-dark');
        }

        btn.appendChild(ripple);
        setTimeout(() => {
            if (ripple.parentNode) ripple.parentNode.removeChild(ripple);
        }, 650);

        // If it's a minor interactive button (not a link, not a submit), give a mini floating feedback pill
        if (btn.tagName === 'BUTTON' && btn.type !== 'submit' && !btn.closest('form')) {
            const label = btn.getAttribute('data-action-label');
            if (label) {
                spawnClickPill(e.clientX, e.clientY, label);
            }
        }
    }

    // =========================================================================
    // 8. FORM SUBMISSION INTERCEPTOR (HUD & Loading State)
    // =========================================================================
    function handleFormSubmit(e) {
        const form = e.target;

        // If form has bypass attribute or target="_blank", don't show full HUD
        if (form.getAttribute('target') === '_blank' || form.hasAttribute('data-no-hud')) {
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');

        let title = 'Sedang Memproses...';
        let desc = 'Mohon tunggu, data sedang diverifikasi dan disimpan.';
        let showHud = false;

        if (submitBtn) {
            // Determine friendly message from button text
            const btnText = submitBtn.textContent.trim().toLowerCase();
            if (btnText.includes('login') || btnText.includes('masuk')) {
                title = 'Memverifikasi Akun...';
                desc = 'Memeriksa kredensial Anda, mohon tunggu sebentar.';
                showHud = true;
            } else if (btnText.includes('keluar') || btnText.includes('logout')) {
                title = 'Keluar Sistem...';
                desc = 'Menutup sesi Anda dengan aman. Sampai jumpa kembali.';
                showHud = true;
            }

            // Always disable button to prevent double submit
            submitBtn.style.opacity = '0.85';
            submitBtn.style.pointerEvents = 'none';
        }

        if (showHud) {
            // Show centered HUD display
            showProcessingHUD(title, desc);
            setTopBarProgress(70);
        }
    }

    // =========================================================================
    // 9. NAVIGATION LINK INTERCEPTOR (Top Loading Bar on Page Change)
    // =========================================================================
    function handleLinkClick(e) {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href) return;

        // Skip anchor jumps, javascript:, new tabs, and downloads
        if (
            href.startsWith('#') ||
            href.startsWith('javascript:') ||
            link.getAttribute('target') === '_blank' ||
            link.hasAttribute('download')
        ) {
            return;
        }

        // Start top shimmer progress bar immediately
        setTopBarProgress(40);
        setTimeout(() => setTopBarProgress(80), 150);
    }

    // =========================================================================
    // 10. PAGE ENTRANCE LOGIC ("Setiap Masuk")
    // =========================================================================
    function onPageEnter() {
        // Start top shimmer bar on initial page load
        setTopBarProgress(30);
        setTimeout(() => setTopBarProgress(70), 100);
        setTimeout(() => setTopBarProgress(100), 280);

        // Apply smooth reveal to main content
        const mainContent = document.querySelector('main, .relative.flex.min-h-screen, .welcome-card');
        if (mainContent && !mainContent.classList.contains('page-enter-reveal')) {
            mainContent.classList.add('page-enter-reveal');
        }

        // Check for session flash messages from server (rendered in data attributes on body or meta)
        const flashSuccess = document.body.getAttribute('data-session-success');
        const flashError = document.body.getAttribute('data-session-error');
        const flashWarning = document.body.getAttribute('data-session-warning');
        const flashInfo = document.body.getAttribute('data-session-info');

        const userRole = document.body.getAttribute('data-user-role');
        const userName = document.body.getAttribute('data-user-name');
        const pageTitle = document.title || '';

        // Priority 1: Server flash messages
        if (flashSuccess) {
            showToast({
                type: 'success',
                title: 'Berhasil!',
                message: flashSuccess,
                duration: 5000
            });
        } else if (flashError) {
            showToast({
                type: 'error',
                title: 'Perhatian / Gagal',
                message: flashError,
                duration: 5500
            });
        } else if (flashWarning) {
            showToast({
                type: 'warning',
                title: 'Peringatan',
                message: flashWarning,
                duration: 5000
            });
        } else if (flashInfo) {
            showToast({
                type: 'info',
                title: 'Informasi',
                message: flashInfo,
                duration: 4500
            });
        }
    }

    // =========================================================================
    // 11. INITIALIZATION & GLOBAL BINDINGS
    // =========================================================================
    function init() {
        initTopBar();
        initParticleCanvas();
        initToastContainer();
        initProcessingHUD();

        // Event Listeners
        document.addEventListener('click', handleButtonClick, { passive: true });
        document.addEventListener('click', handleLinkClick, { passive: true });
        document.addEventListener('submit', handleFormSubmit);

        // Page enter
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', onPageEnter);
        } else {
            onPageEnter();
        }

        // Hide processing HUD if page restored from bfcache
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                hideProcessingHUD();
                setTopBarProgress(100);
            }
        });
    }

    // Initialize as soon as DOM is ready or immediately if already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose API for blade templates or scripts
    window.CikampekUI = {
        showToast,
        showProcessingHUD,
        hideProcessingHUD,
        playSound,
        spawnParticles: spawnParticleBurst,
        setTopProgress: setTopBarProgress,
        toggleMute: function() {
            isMuted = !isMuted;
            localStorage.setItem('cikampek_sound_muted', isMuted ? 'true' : 'false');
            showToast({
                type: 'info',
                title: isMuted ? '🔇 Audio Dimatikan' : '🔊 Audio Diaktifkan',
                message: isMuted ? 'Efek suara interaktif dinonaktifkan.' : 'Efek suara klik tombol sekarang aktif.',
                duration: 2500
            });
            return isMuted;
        },
        isMuted: () => isMuted
    };

})();

/**
 * TRANSLOG - Global Notification System
 * Provides: top progress bar, toast notifications, delete confirm modal
 */

const Notify = (() => {
    // ── Progress Bar ──────────────────────────────────────────────
    const bar = document.getElementById('nprogress-bar');
    let barTimer = null;

    function startProgress() {
        if (!bar) return;
        bar.style.opacity = '1';
        bar.style.width = '0%';
        bar.style.transition = 'width 0.2s ease';
        // Animate to 80% quickly
        setTimeout(() => { bar.style.width = '30%'; }, 10);
        setTimeout(() => { bar.style.width = '75%'; }, 300);
        barTimer = setTimeout(() => { bar.style.width = '90%'; }, 1200);
    }

    function doneProgress() {
        if (!bar) return;
        clearTimeout(barTimer);
        bar.style.transition = 'width 0.25s ease, opacity 0.4s ease 0.3s';
        bar.style.width = '100%';
        setTimeout(() => { bar.style.opacity = '0'; }, 400);
        setTimeout(() => { bar.style.width = '0%'; bar.style.transition = 'none'; }, 800);
    }

    // ── Toast ─────────────────────────────────────────────────────
    const container = document.getElementById('toast-container');
    const ICONS = {
        success: 'fa-check',
        error:   'fa-times',
        warning: 'fa-exclamation',
        info:    'fa-info'
    };
    const TITLES = {
        success: 'Succès',
        error:   'Erreur',
        warning: 'Attention',
        info:    'Information'
    };

    function toast(type = 'info', message = '', duration = 4000) {
        if (!container) return;
        const el = document.createElement('div');
        el.className = `toast ${type}`;
        el.innerHTML = `
            <div class="toast-icon"><i class="fas ${ICONS[type]}"></i></div>
            <div class="toast-body">
                <div class="toast-title">${TITLES[type]}</div>
                <div class="toast-msg">${message}</div>
            </div>
            <button class="toast-close" aria-label="Fermer"><i class="fas fa-times"></i></button>
        `;
        // Override duration for toast timer animation
        el.style.cssText += `--toast-dur: ${duration}ms`;
        el.querySelector('.toast-close').addEventListener('click', () => removeToast(el));
        el.addEventListener('click', () => removeToast(el));
        container.appendChild(el);
        // Auto-remove
        setTimeout(() => removeToast(el), duration);
    }

    function removeToast(el) {
        if (!el || el.classList.contains('removing')) return;
        el.classList.add('removing');
        setTimeout(() => el.remove(), 350);
    }

    // ── Delete Confirm Modal ──────────────────────────────────────
    const overlay = document.getElementById('confirm-modal-overlay');
    const confirmBtn = document.getElementById('modal-confirm-btn');
    let pendingUrl = null;

    function confirmDelete(url) {
        pendingUrl = url;
        overlay.classList.add('show');
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            if (pendingUrl) {
                startProgress();
                window.location.href = pendingUrl;
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('show');
                pendingUrl = null;
            }
        });
    }

    document.getElementById('modal-cancel-btn')?.addEventListener('click', () => {
        overlay.classList.remove('show');
        pendingUrl = null;
    });

    // Close modal on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay && overlay.classList.contains('show')) {
            overlay.classList.remove('show');
            pendingUrl = null;
        }
    });

    // ── Auto progress on ALL navigation links & forms ─────────────
    document.querySelectorAll('a:not([data-no-progress])').forEach(link => {
        if (link.href && !link.href.startsWith('javascript') && !link.href.startsWith('#') && !link.target) {
            link.addEventListener('click', (e) => {
                if (!e.ctrlKey && !e.metaKey) startProgress();
            });
        }
    });
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => startProgress());
    });

    // Done on page load
    window.addEventListener('load', () => doneProgress());

    return { toast, startProgress, doneProgress, confirmDelete };
})();

// Expose confirmDelete globally (for onclick= usage)
window.confirmDelete = Notify.confirmDelete;

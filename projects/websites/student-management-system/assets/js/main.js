/* Elevate Media College — main.js
   Shared interactions: nav, toasts, scroll reveal, modal system, delete confirmations. */
(function () {
    'use strict';

    /* ---------- Mobile nav ---------- */
    var navToggle = document.getElementById('navToggle');
    var siteNav = document.getElementById('siteNav');
    if (navToggle && siteNav) {
        navToggle.addEventListener('click', function () {
            var open = siteNav.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            navToggle.innerHTML = open
                ? '<i class="fa-solid fa-xmark"></i>'
                : '<i class="fa-solid fa-bars"></i>';
        });
        // Close nav when a link is clicked (mobile)
        siteNav.addEventListener('click', function (e) {
            if (e.target.closest('a')) {
                siteNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
            }
        });
    }

    /* ---------- Toasts auto-dismiss ---------- */
    document.querySelectorAll('.toast').forEach(function (toast) {
        setTimeout(function () { dismissToast(toast); }, 5200);
    });
    function dismissToast(toast) {
        toast.style.transition = 'opacity .3s ease, transform .3s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(function () { toast.remove(); }, 320);
    }
    document.addEventListener('click', function (e) {
        if (e.target.closest('.toast-close')) {
            dismissToast(e.target.closest('.toast'));
        }
    });

    /* ---------- Toast factory (global) ---------- */
    window.showToast = function (type, message) {
        var stack = document.querySelector('.toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'toast-stack';
            stack.setAttribute('aria-live', 'polite');
            document.body.appendChild(stack);
        }
        var icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation', info: 'fa-circle-info' };
        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        toast.innerHTML = '<i class="fa-solid ' + (icons[type] || icons.info) + '"></i>' +
            '<span></span>' +
            '<button class="toast-close" aria-label="Dismiss"><i class="fa-solid fa-xmark"></i></button>';
        toast.querySelector('span').textContent = message;
        stack.appendChild(toast);
        setTimeout(function () { dismissToast(toast); }, 5200);
    };

    /* ---------- Modal system ---------- */
    window.openModal = function (html) {
        closeModal();
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.setAttribute('role', 'dialog');
        backdrop.setAttribute('aria-modal', 'true');
        backdrop.innerHTML = '<div class="modal"></div>';
        backdrop.querySelector('.modal').innerHTML = html;
        document.body.appendChild(backdrop);
        requestAnimationFrame(function () { backdrop.classList.add('open'); });
        document.body.style.overflow = 'hidden';
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) closeModal();
        });
        return backdrop;
    };
    window.closeModal = function () {
        var b = document.querySelector('.modal-backdrop');
        if (b) {
            b.classList.remove('open');
            setTimeout(function () { b.remove(); }, 250);
        }
        document.body.style.overflow = '';
    };
    document.addEventListener('click', function (e) {
        if (e.target.closest('.modal-close')) closeModal();
        if (e.target.closest('[data-close-modal]')) closeModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });

    /* ---------- Delete confirmations (data-confirm) ---------- */
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-confirm]');
        if (!el) return;
        var msg = el.getAttribute('data-confirm') || 'Are you sure?';
        var href = el.getAttribute('href');
        var form = el.closest('form');
        e.preventDefault();
        openModal(
            '<div class="modal-head"><h3>Please confirm</h3>' +
            '<button class="modal-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button></div>' +
            '<p class="text-dim">' + msg.replace(/"/g, '&quot;') + '</p>' +
            '<div class="cp-actions" style="display:flex;gap:10px;justify-content:flex-end;margin-top:18px">' +
            '<button class="btn btn-ghost btn-sm" data-close-modal>Cancel</button>' +
            '<button class="btn btn-danger btn-sm" id="confirmOk">Yes, continue</button></div>'
        );
        document.getElementById('confirmOk').addEventListener('click', function () {
            if (form) {
                form.submit();
            } else if (href) {
                window.location.href = href;
            }
            closeModal();
        });
    });

    /* ---------- Scroll reveal ---------- */
    var revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && revealEls.length) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px' });
        revealEls.forEach(function (el) { io.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('visible'); });
    }

    /* ---------- Animated counters ---------- */
    var counters = document.querySelectorAll('[data-count]');
    if (counters.length) {
        function animateCounter(el) {
            var target = parseFloat(el.getAttribute('data-count') || '0');
            var suffix = el.getAttribute('data-suffix') || '';
            var dur = 1200;
            var start = null;
            function step(ts) {
                if (!start) start = ts;
                var p = Math.min((ts - start) / dur, 1);
                var eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(target * eased).toLocaleString() + suffix;
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }
        if ('IntersectionObserver' in window) {
            var cio = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        cio.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            counters.forEach(function (el) { cio.observe(el); });
        } else {
            counters.forEach(function (el) { animateCounter(el); });
        }
    }

    /* ---------- Sticky header shadow on scroll ---------- */
    var header = document.querySelector('.site-header');
    if (header) {
        var onScroll = function () {
            header.style.boxShadow = window.scrollY > 8
                ? '0 8px 30px rgba(0,0,0,.35)'
                : 'none';
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }
})();

(function() {
    'use strict';

    // ── Initialize AOS ──
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 700,
            once: true,
            offset: 100,
        });
    }

    // ── Mobile sidebar toggle for admin ──
    window.toggleSidebar = function() {
        const sidebar = document.getElementById('adminSidebar');
        const main    = document.getElementById('adminMain');
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
            if (main) main.classList.toggle('expanded');
        }
    };

    // ── Auto-dismiss flash messages ──
    document.querySelectorAll('.alert-dismissible, .action-message').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.4s ease';
            el.style.opacity = '0';
            setTimeout(function() { el.remove(); }, 450);
        }, 5000);
    });

    // ── Smooth scroll ──
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ── Language switcher ──
    window.switchLanguage = function(lang) {
        fetch('/api/health-check.php?lang=' + lang, { method: 'GET' })
            .then(function() {
                document.cookie = 'lang=' + lang + '; path=/; max-age=' + (86400 * 30);
                location.reload();
            })
            .catch(function() {
                document.cookie = 'lang=' + lang + '; path=/; max-age=' + (86400 * 30);
                location.reload();
            });
    };

    // ── Active nav link highlight ──
    document.querySelectorAll('.navbar-nav .nav-link').forEach(function(link) {
        if (link.getAttribute('href') === window.location.pathname) {
            link.classList.add('active');
        }
    });

    // ── Back to top ──
    var backToTopBtn = document.getElementById('backToTop');
    if (!backToTopBtn) {
        backToTopBtn = document.createElement('button');
        backToTopBtn.id = 'backToTop';
        backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        backToTopBtn.style.cssText = 'position:fixed;bottom:30px;right:30px;width:44px;height:44px;border-radius:50%;background:#1a3c5e;color:#fff;border:none;font-size:1.1rem;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:999;display:none;transition:all 0.3s';
        document.body.appendChild(backToTopBtn);

        window.addEventListener('scroll', function() {
            backToTopBtn.style.display = window.scrollY > 300 ? 'flex' : 'none';
            backToTopBtn.style.alignItems = 'center';
            backToTopBtn.style.justifyContent = 'center';
        });

        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ── File size formatting ──
    window.formatFileSize = function(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024, sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    // ── Copy to clipboard ──
    window.copyToClipboard = function(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).catch(function() {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    };

    function fallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch(e) {}
        document.body.removeChild(ta);
    }

    // ── Toast notification helper ──
    window.showToast = function(message, type) {
        type = type || 'info';
        var t = document.createElement('div');
        t.className = 'admin-toast toast-' + type;
        var icon = type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle';
        t.innerHTML = '<i class="fas fa-' + icon + '"></i> ' + message;
        document.body.appendChild(t);
        requestAnimationFrame(function() { t.classList.add('show'); });
        setTimeout(function() {
            t.classList.remove('show');
            setTimeout(function() { t.remove(); }, 300);
        }, 3500);
    };

    // ── Search form enhancement ──
    var searchForms = document.querySelectorAll('form[action*="search.php"]');
    searchForms.forEach(function(form) {
        var input = form.querySelector('input[name="q"]');
        if (input) {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') form.submit();
            });
        }
    });

})();

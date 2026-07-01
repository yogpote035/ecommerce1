document.addEventListener('DOMContentLoaded', function () {
    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    }

    function initTheme() {
        var saved = localStorage.getItem('theme') || 'light';
        setTheme(saved);
    }

    function initThemeToggle() {
        var toggle = document.getElementById('theme-toggle');
        if (!toggle) return;

        toggle.addEventListener('click', function () {
            var current = document.documentElement.getAttribute('data-theme');
            var next = current === 'dark' ? 'light' : 'dark';
            setTheme(next);
        });
    }

    function initToasts() {
        var toastEls = document.querySelectorAll('[data-toast]');
        toastEls.forEach(function (toastEl) {
            setTimeout(function () {
                toastEl.classList.add('toast-show');
            }, 100);
        });
    }

    initTheme();
    initThemeToggle();
    initToasts();
});

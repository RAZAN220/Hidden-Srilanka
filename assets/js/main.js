document.addEventListener('DOMContentLoaded', function () {

    // ── Nav toggle ──
    var toggle = document.getElementById('navToggle');
    var links  = document.getElementById('navLinks');
    if (toggle && links) {
        toggle.addEventListener('click', function () {
            links.classList.toggle('active');
        });
    }

    // ── Back button (auto-injected on every page except home) ──
    var isHome = (location.pathname === '/' || location.pathname === '/index.php');
    if (!isHome && history.length > 1) {
        var btn = document.createElement('button');
        btn.className = 'back-btn';
        btn.innerHTML = '<i class="fas fa-arrow-left"></i> Back';
        btn.setAttribute('aria-label', 'Go back');
        btn.addEventListener('click', function () { history.back(); });
        document.body.appendChild(btn);
    }

});
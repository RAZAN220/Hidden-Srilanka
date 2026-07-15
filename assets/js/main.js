// Nav toggle (already in inline script, but can be moved)
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('navToggle');
    const links = document.getElementById('navLinks');
    if (toggle && links) {
        toggle.addEventListener('click', function() {
            links.classList.toggle('active');
        });
    }
});

// Additional interactive features (e.g., favorite toggle, form validation)
// Can be extended
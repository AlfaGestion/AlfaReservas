(function () {
    const STORAGE_KEY = 'alfa_theme';

    function applyTheme(mode) {
        document.body.classList.toggle('theme-dark', mode === 'dark');
        const btn = document.getElementById('themeToggleGlobal');
        if (btn) {
            btn.textContent = mode === 'dark' ? 'Modo claro' : 'Modo oscuro';
        }
    }

    function ensureToggleButton() {
        if (document.getElementById('themeToggleGlobal')) {
            return;
        }

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'themeToggleGlobal';
        btn.className = 'btn btn-sm theme-toggle-global';
        btn.textContent = 'Modo oscuro';
        btn.addEventListener('click', function () {
            const current = document.body.classList.contains('theme-dark') ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem(STORAGE_KEY, next);
            applyTheme(next);
        });
        document.body.appendChild(btn);
    }

    function enhanceLegacyAdminFooter() {
        const footers = document.querySelectorAll('footer.my-4');
        footers.forEach(function (footer) {
            if (footer.dataset.enhanced === '1') {
                return;
            }
            footer.dataset.enhanced = '1';

            const links = footer.querySelectorAll('.nav-link');
            links.forEach(function (link) {
                const href = (link.getAttribute('href') || '').toLowerCase();
                const label = (link.textContent || '').trim().toLowerCase();

                if (!href && label === '-') {
                    link.parentElement.style.display = 'none';
                    return;
                }

                if (href.includes('auth/logout') || label.includes('cerrar')) {
                    link.innerHTML = '<i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesion';
                } else if (href.includes('abmadmin') || label.includes('panel')) {
                    link.innerHTML = '<i class="fa-solid fa-sliders me-1"></i> Panel';
                } else if (href.includes('auth/login')) {
                    link.innerHTML = '<i class="fa-solid fa-user-shield me-1"></i> Ingreso admin';
                } else if (href.includes('customers/register')) {
                    link.innerHTML = '<i class="fa-solid fa-user-plus me-1"></i> Registro clientes';
                }
            });

            const brandLink = footer.querySelector('.link a');
            if (brandLink) {
                brandLink.innerHTML = 'AlfaGestion <span style="opacity:.8">by Alfanet</span>';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const saved = localStorage.getItem(STORAGE_KEY) === 'dark' ? 'dark' : 'light';
        applyTheme(saved);
        ensureToggleButton();
        enhanceLegacyAdminFooter();
    });
})();

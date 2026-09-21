/**
 * GSW E-Commerce — JavaScript Principal
 */

document.addEventListener('DOMContentLoaded', function() {

    // ==================== FLASH MESSAGES ====================
    const flashMessage = document.getElementById('flash-message');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            flashMessage.style.transform = 'translateX(100%)';
            setTimeout(() => flashMessage.remove(), 300);
        }, 5000);
    }

    // ==================== DROPDOWN ====================
    window.toggleDropdown = function(id) {
        const dropdown = document.getElementById(id);
        const menu = dropdown.querySelector('.dropdown-menu');
        menu.classList.toggle('active');
    };

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', function(e) {
        document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
            if (!menu.parentElement.contains(e.target)) {
                menu.classList.remove('active');
            }
        });
    });

    // ==================== TEMA CLARO/OSCURO ====================
    // Por defecto sigue al sistema; el botón guarda un override en localStorage.
    const themeToggle = document.getElementById('theme-toggle');
    const syncThemeIcon = function() {
        if (!themeToggle) return;
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark'
            || (!document.documentElement.getAttribute('data-theme')
                && window.matchMedia('(prefers-color-scheme: dark)').matches);
        themeToggle.querySelector('.theme-icon-light').style.display = isDark ? 'none' : '';
        themeToggle.querySelector('.theme-icon-dark').style.display = isDark ? '' : 'none';
    };
    if (themeToggle) {
        syncThemeIcon();
        themeToggle.addEventListener('click', function() {
            const current = document.documentElement.getAttribute('data-theme');
            const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const effectiveDark = current === 'dark' || (!current && systemDark);
            const next = effectiveDark ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            try {
                localStorage.setItem('gsw_theme', next);
            } catch (e) {}
            syncThemeIcon();
        });
    }

    // ==================== MOBILE MENU ====================
    window.toggleMobileMenu = function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('active');
    };

    // ==================== ADD TO CART (AJAX) ====================
    document.querySelectorAll('.quick-add-form, .add-to-cart-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    // Actualizar contador del carrito
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                        cartCount.classList.add('cart-count-pulse');
                        setTimeout(() => cartCount.classList.remove('cart-count-pulse'), 300);
                    }

                    // Mostrar notificación
                    showNotification(data.message || 'Producto agregado al carrito', 'success');
                } else {
                    showNotification(data.message || 'Error al agregar al carrito', 'error');
                }
            } catch (error) {
                // Si falla AJAX, enviar el formulario normalmente
                this.submit();
            }
        });
    });

    // ==================== NOTIFICATIONS ====================
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `flash-message flash-${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" class="flash-close">&times;</button>
        `;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // ==================== SEARCH INPUT SUGGESTIONS ====================
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                // Placeholder para autocomplete semántico futuro
            }, 300);
        });
    }

    // ==================== SCROLL EFFECTS ====================
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('navbar-scrolled', window.pageYOffset > 8);
        }, { passive: true });
    }
});

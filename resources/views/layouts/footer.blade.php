<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            {{-- Brand --}}
            <div class="footer-section">
                <h3 class="footer-brand">GSW<span class="brand-accent">Store</span></h3>
                <p class="footer-text">Plataforma de comercio electrónico con búsqueda semántica potenciada por inteligencia artificial.</p>
                <p class="footer-text-sm">Proyecto de Gestión de Servidores Web — 2026</p>
            </div>

            {{-- Enlaces --}}
            <div class="footer-section">
                <h4 class="footer-title">Navegación</h4>
                <a href="{{ route('home') }}" class="footer-link">Inicio</a>
                <a href="{{ route('products.index') }}" class="footer-link">Productos</a>
                <a href="{{ route('cart.index') }}" class="footer-link">Carrito</a>
            </div>

            {{-- Info --}}
            <div class="footer-section">
                <h4 class="footer-title">Sobre GSWStore</h4>
                <a href="{{ route('about') }}" class="footer-link">Sobre Nosotros</a>
                <p class="footer-text-sm">Búsqueda semántica potenciada por IA que entiende la intención de cada consulta.</p>
                <p class="footer-text-sm">Proyecto de Gestión de Servidores Web — 2026</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} GSW E-Commerce. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<nav class="navbar" id="navbar">
    <div class="container navbar-inner">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="navbar-brand">
            <span class="brand-mark"><x-icon name="bolt" :size="17" :width="2.2" /></span>
            <span class="brand-text">GSW<span class="brand-accent">Store</span></span>
        </a>

        {{-- Navegación derecha --}}
        <div class="navbar-actions">
            <a href="{{ route('products.index') }}" class="nav-link">Productos</a>

            {{-- Tema claro/oscuro --}}
            <button class="nav-link theme-toggle" id="theme-toggle" aria-label="Cambiar tema" title="Cambiar tema">
                <span class="theme-icon-light"><x-icon name="moon" :size="20" /></span>
                <span class="theme-icon-dark" style="display:none"><x-icon name="sun" :size="20" /></span>
            </button>

            {{-- Carrito --}}
            <a href="{{ route('cart.index') }}" class="nav-link cart-link" id="cart-link" aria-label="Carrito">
                <x-icon name="bag" :size="21" />
                <span class="cart-count" id="cart-count">
                    {{ \App\Models\CartItem::getCartCount(auth()->id(), session()->getId()) }}
                </span>
            </a>

            {{-- Autenticación --}}
            @auth
                <div class="dropdown" id="user-dropdown">
                    <button class="dropdown-trigger" onclick="toggleDropdown('user-dropdown')">
                        <span class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        <span class="user-name">{{ auth()->user()->name }}</span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="{{ route('profile.show') }}" class="dropdown-item">
                            <x-icon name="user" :size="17" />
                            Mi Perfil
                        </a>
                        <a href="{{ route('orders.index') }}" class="dropdown-item">
                            <x-icon name="receipt" :size="17" />
                            Mis Pedidos
                        </a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                                <x-icon name="grid" :size="17" />
                                Panel Admin
                            </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <x-icon name="logout" :size="17" />
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Iniciar Sesión</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Registrarse</a>
            @endauth

            {{-- Menú móvil --}}
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Menú">
                <x-icon name="menu" :size="24" />
            </button>
        </div>
    </div>

    {{-- Menú móvil --}}
    <div class="mobile-menu" id="mobile-menu">
        <a href="{{ route('products.index') }}" class="mobile-menu-link">Productos</a>
        <a href="{{ route('cart.index') }}" class="mobile-menu-link">Carrito</a>
        @auth
            <a href="{{ route('profile.show') }}" class="mobile-menu-link">Mi Perfil</a>
            <a href="{{ route('orders.index') }}" class="mobile-menu-link">Mis Pedidos</a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="mobile-menu-link">Panel Admin</a>
            @endif
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="mobile-menu-link text-danger">Cerrar Sesión</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="mobile-menu-link">Iniciar Sesión</a>
            <a href="{{ route('register') }}" class="mobile-menu-link">Registrarse</a>
        @endauth
    </div>
</nav>

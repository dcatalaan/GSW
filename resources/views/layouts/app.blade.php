<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', config('app.name'))</title>

    {{-- Tipografías estilo bloome: DM Sans + Cormorant Garamond --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700&family=Cormorant+Garamond:ital,wght@1,500;1,600&display=swap" rel="stylesheet">

    {{-- Tema claro/oscuro sin flash: respeta el sistema salvo override guardado --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('gsw_theme');
                if (t === 'light' || t === 'dark') document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>

    {{-- CSS Principal --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="{{ auth()->check() && auth()->user()->isAdmin() ? 'admin-layout' : '' }}">
    {{-- Navegación --}}
    @include('layouts.navbar')

    {{-- Mensajes Flash --}}
    @if(session('success'))
        <div class="flash-message flash-success" id="flash-message">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="flash-close">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-message flash-error" id="flash-message">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="flash-close">&times;</button>
        </div>
    @endif

    {{-- Contenido Principal --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('layouts.footer')

    {{-- Chatbot RAG --}}
    @include('components.chatbot')

    {{-- Scripts --}}
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>

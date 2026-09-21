@extends('layouts.app')

@section('title', 'Iniciar Sesión — ' . config('app.name'))

@section('content')
<section class="section section-center">
    <div class="container-sm">
        <div class="auth-card">
            <h1 class="auth-title">Iniciar Sesión</h1>
            <p class="auth-subtitle">Accede a tu cuenta para continuar</p>

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-input @error('password') is-invalid @enderror"
                           required>
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" id="remember" name="remember" class="form-checkbox">
                    <label for="remember">Recordarme</label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-full">Iniciar Sesión</button>

                <p class="auth-footer">
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}">Regístrate aquí</a>
                </p>

                <div class="auth-demo">
                    <p class="text-sm text-muted">Cuentas de prueba:</p>
                    <p class="text-xs text-muted">Admin: admin@gsw-ecommerce.local / password</p>
                    <p class="text-xs text-muted">Cliente: cliente@gsw-ecommerce.local / password</p>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@extends('layouts.app')

@section('title', 'Crear Cuenta — ' . config('app.name'))

@section('content')
<section class="section section-center">
    <div class="container-sm">
        <div class="auth-card">
            <h1 class="auth-title">Crear Cuenta</h1>
            <p class="auth-subtitle">Regístrate para empezar a comprar</p>

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="name">Nombre Completo</label>
                    <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required>
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

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmar Contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input"
                           required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-full">Crear Cuenta</button>

                <p class="auth-footer">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}">Inicia sesión</a>
                </p>
            </form>
        </div>
    </div>
</section>
@endsection

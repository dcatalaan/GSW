@extends('layouts.app')

@section('title', 'Mi Perfil — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container-sm">
        <div class="section-header">
            <h1 class="section-title">Mi Perfil</h1>
            <p class="section-subtitle">{{ $user->email }}</p>
        </div>

        <div class="auth-card">
            <h2 class="profile-subtitle">Datos personales</h2>
            <form method="POST" action="{{ route('profile.update') }}" class="auth-form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="name">Nombre</label>
                    <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required>
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <h2 class="profile-subtitle">Datos de facturación</h2>
                <p class="profile-hint">Se usan como receptor en tus facturas electrónicas.</p>

                <div class="form-group">
                    <label class="form-label" for="billing_name">Nombre / Razón Social</label>
                    <input type="text" id="billing_name" name="billing_name" class="form-input"
                           value="{{ old('billing_name', $user->billing_name) }}" placeholder="Igual que tu DUI o NIT">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="doc_type">Tipo de documento</label>
                        <select id="doc_type" name="doc_type" class="form-select">
                            <option value="">Seleccionar...</option>
                            <option value="DUI" {{ old('doc_type', $user->doc_type) === 'DUI' ? 'selected' : '' }}>DUI</option>
                            <option value="NIT" {{ old('doc_type', $user->doc_type) === 'NIT' ? 'selected' : '' }}>NIT</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="doc_number">Número de documento</label>
                        <input type="text" id="doc_number" name="doc_number" class="form-input"
                               value="{{ old('doc_number', $user->doc_number) }}" placeholder="00000000-0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nrc">NRC (opcional)</label>
                        <input type="text" id="nrc" name="nrc" class="form-input"
                               value="{{ old('nrc', $user->nrc) }}" placeholder="000000-0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Teléfono</label>
                        <input type="text" id="phone" name="phone" class="form-input"
                               value="{{ old('phone', $user->phone) }}" placeholder="0000-0000" inputmode="tel">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="billing_address">Dirección</label>
                    <textarea id="billing_address" name="billing_address" class="form-textarea" rows="2"
                              placeholder="Dirección para facturación">{{ old('billing_address', $user->billing_address) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="city">Municipio / Ciudad</label>
                    <input type="text" id="city" name="city" class="form-input"
                           value="{{ old('city', $user->city) }}" placeholder="San Salvador">
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-full">Guardar Cambios</button>
            </form>
        </div>
    </div>
</section>
@endsection

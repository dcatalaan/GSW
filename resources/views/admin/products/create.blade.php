@extends('layouts.app')

@section('title', 'Crear Producto — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container-sm">
        <nav class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <span>/</span>
            <a href="{{ route('admin.products') }}">Productos</a>
            <span>/</span>
            <span class="breadcrumb-current">Crear</span>
        </nav>

        <div class="auth-card" style="max-width: 800px;">
            <h1 class="auth-title">Crear Producto</h1>

            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="auth-form">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-input @error('sku') is-invalid @enderror" value="{{ old('sku') }}" required>
                        @error('sku') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">Seleccionar...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción Corta</label>
                    <input type="text" name="short_description" class="form-input" value="{{ old('short_description') }}" maxlength="500">
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción Completa</label>
                    <textarea name="description" class="form-textarea" rows="5" required>{{ old('description') }}</textarea>
                    @error('description') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Precio ($)</label>
                        <input type="number" name="price" class="form-input @error('price') is-invalid @enderror" value="{{ old('price') }}" step="0.01" min="0" required>
                        @error('price') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Precio Comparación ($)</label>
                        <input type="number" name="compare_price" class="form-input" value="{{ old('compare_price') }}" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-input @error('stock') is-invalid @enderror" value="{{ old('stock', 0) }}" min="0" required>
                        @error('stock') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Imagen del Producto</label>
                    <input type="file" name="image" class="form-input" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">O URL de imagen (se usa si no subes archivo)</label>
                    <input type="url" name="image_url" class="form-input @error('image_url') is-invalid @enderror" value="{{ old('image_url') }}" placeholder="https://...">
                    @error('image_url') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-checkbox" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active">Activo</label>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="is_featured" id="is_featured" class="form-checkbox" {{ old('is_featured') ? 'checked' : '' }}>
                        <label for="is_featured">Destacado</label>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.products') }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Crear Producto</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

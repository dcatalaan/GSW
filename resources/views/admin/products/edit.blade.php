@extends('layouts.app')

@section('title', 'Editar Producto — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container-sm">
        <nav class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <span>/</span>
            <a href="{{ route('admin.products') }}">Productos</a>
            <span>/</span>
            <span class="breadcrumb-current">Editar</span>
        </nav>

        <div class="auth-card" style="max-width: 800px;">
            <h1 class="auth-title">Editar: {{ $product->name }}</h1>

            <form method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data" class="auth-form">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-input" value="{{ old('name', $product->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-input" value="{{ old('sku', $product->sku) }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción Corta</label>
                    <input type="text" name="short_description" class="form-input" value="{{ old('short_description', $product->short_description) }}" maxlength="500">
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción Completa</label>
                    <textarea name="description" class="form-textarea" rows="5" required>{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Precio ($)</label>
                        <input type="number" name="price" class="form-input" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Precio Comparación ($)</label>
                        <input type="number" name="compare_price" class="form-input" value="{{ old('compare_price', $product->compare_price) }}" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-input" value="{{ old('stock', $product->stock) }}" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Imagen Actual</label>
                    @if($product->image)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="table-thumb" style="margin-bottom: 0.5rem;">
                    @endif
                    <input type="file" name="image" class="form-input" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">O URL de imagen (reemplaza la actual si se indica)</label>
                    <input type="url" name="image_url" class="form-input @error('image_url') is-invalid @enderror" value="{{ old('image_url', str_starts_with($product->image ?? '', 'http') ? $product->image : '') }}" placeholder="https://...">
                    @error('image_url') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-checkbox" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label for="is_active">Activo</label>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="is_featured" id="is_featured" class="form-checkbox" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                        <label for="is_featured">Destacado</label>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.products') }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

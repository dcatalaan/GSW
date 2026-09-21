@extends('layouts.app')

@section('title', 'Productos Admin — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header-row">
            <div>
                <h1 class="section-title">Gestionar Productos</h1>
                <p class="section-subtitle">{{ $products->total() }} productos</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                + Nuevo Producto
            </a>
        </div>

        {{-- Filtros --}}
        <form class="admin-filters" method="GET">
            <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Buscar producto...">
            <select name="category" class="filter-select">
                <option value="">Todas las categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>SKU</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="table-thumb">
                            </td>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if($product->is_featured)
                                    <span class="badge badge-featured" title="Destacado"><x-icon name="star" :size="12" :width="2.2" /></span>
                                @endif
                            </td>
                            <td><code>{{ $product->sku }}</code></td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td>${{ number_format($product->price, 2) }}</td>
                            <td>
                                <span class="{{ $product->stock <= 5 ? 'text-danger' : '' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline btn-xs">Editar</a>
                                    <form action="{{ route('admin.products.delete', $product->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('¿Eliminar este producto?')">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No hay productos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $products->links() }}
        </div>
    </div>
</section>
@endsection

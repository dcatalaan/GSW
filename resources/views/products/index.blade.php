@extends('layouts.app')

@section('title', 'Productos — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Productos</h1>
            <p class="section-subtitle">{{ $products->total() }} productos disponibles</p>
        </div>

        <div class="catalog-layout">
            {{-- Filtros --}}
            <aside class="filters-sidebar">
                <form action="{{ route('products.index') }}" method="GET" class="filters-form">
                    {{-- Búsqueda --}}
                    <div class="filter-group">
                        <label class="filter-label">Buscar</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Nombre o descripción...">
                    </div>

                    {{-- Categorías --}}
                    <div class="filter-group">
                        <label class="filter-label">Categoría</label>
                        <select name="category" class="filter-select">
                            <option value="">Todas</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                    {{ $category->name }} ({{ $category->products_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Precio --}}
                    <div class="filter-group">
                        <label class="filter-label">Precio</label>
                        <div class="filter-price-range">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" class="filter-input" placeholder="Mín" step="0.01">
                            <span>—</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" class="filter-input" placeholder="Máx" step="0.01">
                        </div>
                    </div>

                    {{-- Ordenar --}}
                    <div class="filter-group">
                        <label class="filter-label">Ordenar por</label>
                        <select name="sort" class="filter-select">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Más recientes</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Precio: menor a mayor</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Precio: mayor a menor</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nombre</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">Aplicar Filtros</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline btn-full" style="margin-top: 0.5rem;">Limpiar</a>
                </form>
            </aside>

            {{-- Grid de productos --}}
            <div class="catalog-main">
                @if($products->count())
                    <div class="products-grid">
                        @foreach($products as $product)
                            @include('components.product-card', ['product' => $product])
                        @endforeach
                    </div>

                    <div class="pagination-wrapper">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon"><x-icon name="box" :size="32" :width="1.5" /></div>
                        <h3>No se encontraron productos</h3>
                        <p>Intenta ajustar los filtros de búsqueda</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">Ver todos</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

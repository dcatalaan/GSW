@extends('layouts.app')

@section('title', 'Inicio — ' . config('app.name'))

@section('content')
{{-- Categorías --}}
@if($categories->count())
<section class="section">
    <div class="container">
        <div class="section-header-row">
            <div>
                <h1 class="section-title">Categorías</h1>
                <p class="section-subtitle">Explora el catálogo por categoría</p>
            </div>
        </div>
        <div class="categories-grid">
            @foreach($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="category-card">
                    <div class="category-icon"><x-icon name="{{ getCategoryIcon($category->slug) }}" :size="26" /></div>
                    <h3 class="category-name">{{ $category->name }}</h3>
                    <span class="category-count">{{ $category->products_count }} productos</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Productos Destacados --}}
@if($featuredProducts->count())
<section class="section section-alt">
    <div class="container">
        <div class="section-header-row">
            <div>
                <h2 class="section-title">Destacados</h2>
                <p class="section-subtitle">Los productos más populares</p>
            </div>
            <a href="{{ route('products.index') }}" class="section-link">Ver todos <x-icon name="chevron-right" :size="16" :width="2.2" /></a>
        </div>
        <div class="products-grid">
            @foreach($featuredProducts as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

@php
function getCategoryIcon(string $slug): string {
    return match($slug) {
        'deportes' => 'activity',
        'tecnologia' => 'cpu',
        'hogar' => 'home',
        'moda' => 'bag',
        'outdoor' => 'mountain',
        default => 'box',
    };
}
@endphp

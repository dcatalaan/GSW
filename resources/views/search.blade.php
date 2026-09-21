@extends('layouts.app')

@section('title', 'Resultados: ' . $query . ' â€” GSWStore')

@section('content')
<section class="section search-results-section">
    <div class="container">

        {{-- Header --}}
        <div class="search-results-header">
            <div class="search-results-info">
                <h1 class="search-results-title">
                    Resultados para "<span class="highlight">{{ $query }}</span>"
                </h1>
                <p class="search-results-meta">
                    <span class="search-engine-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Motor de bÃºsqueda
                    </span>
                    {{ count($results) }} producto{{ count($results) !== 1 ? 's' : '' }} encontrado{{ count($results) !== 1 ? 's' : '' }}
                </p>
            </div>
            <a href="{{ route('products.index') }}" class="search-results-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                </svg>
                Ver todos los productos
            </a>
        </div>

        {{-- Results Grid --}}
        @if(count($results))
            <div class="search-results-grid">
                @foreach($results as $result)
                    @php
                        $slug = $result['slug'] ?? $result->slug ?? '#';
                        $name = $result['name'] ?? '';
                        $price = $result['price'] ?? 0;
                        $comparePrice = $result['compare_price'] ?? null;
                        $description = $result['short_description'] ?? $result['description'] ?? '';
                        $categoryName = $result['category']['name'] ?? $result->category->name ?? '';
                        $categoryId = $result['category_id'] ?? $result->category->id ?? 0;
                        $image = $result['image'] ?? null;
                        $imageUrl = $image
                            ? (str_starts_with($image, 'http') ? $image : asset('storage/' . $image))
                            : null;
                        $similarity = $result['combined_score'] ?? $result['similarity_score'] ?? null;

                        // Category icon
                        $icon = match(true) {
                            str_contains($categoryName, 'Deporte') => 'activity',
                            str_contains($categoryName, 'Tecnolog') => 'cpu',
                            str_contains($categoryName, 'Hogar') => 'home',
                            str_contains($categoryName, 'Moda') => 'bag',
                            str_contains($categoryName, 'Outdoor') => 'mountain',
                            default => 'box',
                        };

                        // Discount percentage
                        $discount = 0;
                        if ($comparePrice && $comparePrice > $price) {
                            $discount = round((($comparePrice - $price) / $comparePrice) * 100);
                        }
                    @endphp
                    <div class="sr-card" data-animate>
                        {{-- Image Area --}}
                        <a href="{{ route('products.show', $slug) }}" class="sr-card-image">
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $name }}" loading="lazy">
                            @else
                                <div class="sr-card-placeholder"><x-icon name="{{ $icon }}" :size="44" :width="1.2" /></div>
                            @endif

                            {{-- Badges --}}
                            <div class="sr-card-badges">
                                @if($discount > 0)
                                    <span class="sr-badge sr-badge--discount">-{{ $discount }}%</span>
                                @endif
                                @if($similarity !== null)
                                    <span class="sr-badge sr-badge--ai">
                                        <x-icon name="sparkles" :size="12" :width="2.2" /> {{ round($similarity * 100) }}%
                                    </span>
                                @endif
                            </div>
                        </a>

                        {{-- Content --}}
                        <div class="sr-card-body">
                            <span class="sr-card-category">{{ $categoryName }}</span>
                            <h3 class="sr-card-title">
                                <a href="{{ route('products.show', $slug) }}">{{ $name }}</a>
                            </h3>
                            <p class="sr-card-desc">{{ $description }}</p>

                            <div class="sr-card-footer">
                                <div class="sr-card-pricing">
                                    <span class="sr-card-price">${{ number_format((float)$price, 2) }}</span>
                                    @if($comparePrice && $comparePrice > $price)
                                        <span class="sr-card-compare">${{ number_format((float)$comparePrice, 2) }}</span>
                                    @endif
                                </div>
                                <form action="{{ route('cart.add') }}" method="POST" class="sr-card-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $result['id'] ?? $result->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="sr-card-cart-btn" title="Agregar al carrito">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                            <path d="m1 1 4 0 2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="search-empty">
                <div class="search-empty-icon"><x-icon name="search" :size="30" :width="1.5" /></div>
                <h3>No encontramos resultados para "{{ $query }}"</h3>
                <p>Intenta con otras palabras clave o explora nuestras categorÃ­as:</p>
                <div class="search-empty-categories">
                    <a href="{{ route('products.index', ['category' => 'deportes']) }}" class="search-empty-cat"><x-icon name="activity" :size="16" /> Deportes</a>
                    <a href="{{ route('products.index', ['category' => 'tecnologia']) }}" class="search-empty-cat"><x-icon name="cpu" :size="16" /> TecnologÃ­a</a>
                    <a href="{{ route('products.index', ['category' => 'hogar']) }}" class="search-empty-cat"><x-icon name="home" :size="16" /> Hogar</a>
                    <a href="{{ route('products.index', ['category' => 'moda']) }}" class="search-empty-cat"><x-icon name="bag" :size="16" /> Moda</a>
                    <a href="{{ route('products.index', ['category' => 'outdoor']) }}" class="search-empty-cat"><x-icon name="mountain" :size="16" /> Outdoor</a>
                </div>
                <a href="{{ route('products.index') }}" class="btn btn-primary" style="margin-top: 1.5rem;">Ver Todos los Productos</a>
            </div>
        @endif
    </div>
</section>

@endsection

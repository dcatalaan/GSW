@extends('layouts.app')

@section('title', $product->name . ' — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Inicio</a>
            <span>/</span>
            <a href="{{ route('products.index') }}">Productos</a>
            <span>/</span>
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
            <span>/</span>
            <span class="breadcrumb-current">{{ $product->name }}</span>
        </nav>

        <div class="product-detail">
            {{-- Imagen --}}
            <div class="product-detail-image">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                @if($product->hasDiscount())
                    <span class="product-badge badge-sale">-{{ $product->getDiscountPercentage() }}%</span>
                @endif
            </div>

            {{-- Info --}}
            <div class="product-detail-info">
                <span class="product-category">{{ $product->category->name }}</span>
                <h1 class="product-name">{{ $product->name }}</h1>
                @if($product->is_featured)
                    <div><span class="badge-featured"><x-icon name="star" :size="12" :width="2.2" /> Destacado</span></div>
                @endif

                {{-- Rating summary --}}
                @php
                    $avgRating = \App\Models\Review::getAverageRating($product->id);
                    $reviewCount = \App\Models\Review::getReviewCount($product->id);
                @endphp
                @if($reviewCount > 0)
                <div class="product-rating-summary">
                    <div class="stars">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="star {{ $i <= round($avgRating) ? 'filled' : '' }}"><x-icon name="star" :size="17" fill /></span>
                        @endfor
                    </div>
                    <span class="rating-text">{{ number_format($avgRating, 1) }} ({{ $reviewCount }} {{ $reviewCount === 1 ? 'reseña' : 'reseñas' }})</span>
                </div>
                @endif

                <div class="product-pricing">
                    <span class="product-price">${{ number_format($product->price, 2) }}</span>
                    @if($product->hasDiscount())
                        <span class="product-compare-price">${{ number_format($product->compare_price, 2) }}</span>
                    @endif
                </div>

                <div class="product-meta">
                    <span class="meta-item">
                        <strong>Stock:</strong>
                        @if($product->stock > 0)
                            <span class="stock-available">{{ $product->stock }} disponibles</span>
                        @else
                            <span class="stock-unavailable">Agotado</span>
                        @endif
                    </span>
                </div>

                <p class="product-short-desc">{{ $product->short_description }}</p>

                {{-- Formulario de agregar al carrito --}}
                @if($product->stock > 0)
                <form action="{{ route('cart.add') }}" method="POST" class="add-to-cart-form" id="add-to-cart-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="quantity-selector">
                        <button type="button" class="qty-btn" onclick="updateQuantity(-1)">−</button>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $product->stock }}" class="qty-input">
                        <button type="button" class="qty-btn" onclick="updateQuantity(1)">+</button>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-full">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="m1 1 4 0 2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        Agregar al Carrito
                    </button>
                </form>
                @else
                <div class="out-of-stock-message">
                    <p>Este producto está actualmente agotado.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Descripción --}}
        <div class="product-description-section">
            <h2 class="subsection-title">Descripción</h2>
            <div class="product-description">
                {!! nl2br(e($product->description)) !!}
            </div>
        </div>

        {{-- ═══ Reseñas ═══ --}}
        <div class="reviews-section" id="resenas">
            <h2 class="subsection-title">Reseñas ({{ $reviewCount }})</h2>

            {{-- Distribución de ratings --}}
            @if($reviewCount > 0)
            @php $dist = \App\Models\Review::getRatingDistribution($product->id); @endphp
            <div class="rating-distribution">
                @for($i = 5; $i >= 1; $i--)
                <div class="rating-bar-row">
                    <span class="rating-label">{{ $i }} <x-icon name="star" :size="12" fill /></span>
                    <div class="rating-bar-track">
                        <div class="rating-bar-fill" style="width: {{ $reviewCount > 0 ? round(($dist[$i] / $reviewCount) * 100) : 0 }}%"></div>
                    </div>
                    <span class="rating-count">{{ $dist[$i] }}</span>
                </div>
                @endfor
            </div>
            @endif

            {{-- Lista de reseñas --}}
            @if($product->reviews->count())
            <div class="reviews-list">
                @foreach($product->reviews as $review)
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-author">
                            <span class="review-avatar">{{ substr($review->user->name, 0, 1) }}</span>
                            <div>
                                <strong>{{ $review->user->name }}</strong>
                                @if($review->is_verified)
                                    <span class="verified-badge"><x-icon name="check" :size="13" :width="2.6" /> Comprador verificado</span>
                                @endif
                            </div>
                        </div>
                        <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="review-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="star {{ $i <= $review->rating ? 'filled' : '' }}"><x-icon name="star" :size="15" fill /></span>
                        @endfor
                    </div>
                    @if($review->title)
                    <h4 class="review-title">{{ $review->title }}</h4>
                    @endif
                    @if($review->comment)
                    <p class="review-comment">{{ $review->comment }}</p>
                    @endif
                    @auth
                        @if(auth()->id() === $review->user_id || auth()->user()->isAdmin())
                        <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="review-delete"
                              onsubmit="return confirm('¿Eliminar esta reseña?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-text-danger">Eliminar</button>
                        </form>
                        @endif
                    @endauth
                </div>
                @endforeach
            </div>
            @else
            <p class="no-reviews">Aún no hay reseñas. ¡Sé el primero en opinar!</p>
            @endif

            {{-- Formulario de reseña (solo quien recibió el producto) --}}
            @auth
            @if($canReview)
            <div class="review-form-card">
                <h3>Deja tu reseña</h3>
                <p class="review-eligible">Recibiste este producto. Cuéntanos qué te pareció.</p>
                <form action="{{ route('reviews.store', $product->slug) }}" method="POST" class="review-form">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Calificación</label>
                        <div class="star-rating-input" id="star-rating">
                            @for($i = 1; $i <= 5; $i++)
                            <button type="button" class="star-btn" data-value="{{ $i }}" onclick="setRating({{ $i }})"><x-icon name="star" :size="26" fill /></button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="rating-input" value="5" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Título (opcional)</label>
                        <input type="text" name="title" class="form-input" placeholder="Resumen de tu experiencia" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Comentario (opcional)</label>
                        <textarea name="comment" class="form-textarea" rows="3" placeholder="Cuéntanos qué te pareció..." maxlength="1000"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Publicar Reseña</button>
                </form>
            </div>
            @elseif($hasReviewed)
            <p class="review-done">Ya dejaste tu reseña para este producto. Gracias por tu opinión.</p>
            @else
            <p class="login-prompt">Podrás dejar una reseña cuando recibas este producto. <a href="{{ route('orders.index') }}">Ver mis pedidos</a></p>
            @endif
            @else
            <p class="login-prompt"><a href="{{ route('login') }}">Inicia sesión</a> para dejar una reseña.</p>
            @endauth
        </div>

        {{-- Productos relacionados --}}
        @if($relatedProducts->count())
        <div class="related-products">
            <h2 class="subsection-title">Productos Relacionados</h2>
            <div class="products-grid">
                @foreach($relatedProducts as $related)
                    @include('components.product-card', ['product' => $related])
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
function updateQuantity(delta) {
    const input = document.getElementById('quantity');
    let value = parseInt(input.value) + delta;
    value = Math.max(1, Math.min(parseInt(input.max), value));
    input.value = value;
}

function setRating(value) {
    document.getElementById('rating-input').value = value;
    const stars = document.querySelectorAll('#star-rating .star-btn');
    stars.forEach((star, idx) => {
        star.classList.toggle('active', idx < value);
    });
}
// Init stars
setRating(5);
</script>
@endpush
@endsection

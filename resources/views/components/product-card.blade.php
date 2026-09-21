@props(['product'])

<div class="product-card">
    <a href="{{ route('products.show', $product->slug) }}" class="product-card-image-link">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-card-image" loading="lazy">
        @if($product->hasDiscount())
            <span class="product-badge badge-sale">-{{ $product->getDiscountPercentage() }}%</span>
        @endif
    </a>
    <div class="product-card-body">
        <span class="product-card-category">{{ $product->category->name ?? '' }}</span>
        <h3 class="product-card-title">
            <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
        </h3>
        <p class="product-card-desc">{{ $product->short_description }}</p>
        <div class="product-card-footer">
            <div class="product-card-pricing">
                <span class="product-card-price">${{ number_format($product->price, 2) }}</span>
                @if($product->hasDiscount())
                    <span class="product-card-compare">${{ number_format($product->compare_price, 2) }}</span>
                @endif
            </div>
            @if($product->stock > 0)
            <form action="{{ route('cart.add') }}" method="POST" class="quick-add-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-icon" title="Agregar al carrito">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="m1 1 4 0 2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

@extends('layouts.app')

@section('title', 'Favoritos — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Mis Favoritos</h1>
            <p class="section-subtitle">{{ $items->count() }} producto{{ $items->count() === 1 ? '' : 's' }} guardado{{ $items->count() === 1 ? '' : 's' }}</p>
        </div>

        @if($items->count())
            <div class="products-grid">
                @foreach($items as $item)
                    @if($item->product)
                        @include('components.product-card', ['product' => $item->product])
                    @endif
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon"><x-icon name="heart" :size="32" /></div>
                <h3>Sin favoritos todavía</h3>
                <p>Toca el corazón en cualquier producto para guardarlo aquí.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Ver Productos</a>
            </div>
        @endif
    </div>
</section>
@endsection

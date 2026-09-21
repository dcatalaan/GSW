@extends('layouts.app')

@section('title', 'Mis Pedidos — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Mis Pedidos</h1>
        </div>

        @if($orders->count())
            <div class="orders-list">
                @foreach($orders as $order)
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <h3 class="order-number">{{ $order->order_number }}</h3>
                                <span class="order-date">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="order-status-group">
                                <span class="order-status status-{{ $order->status }}">{{ $order->status_label }}</span>
                                <span class="order-payment payment-{{ $order->payment_status }}">
                                    {{ $order->payment_status === 'paid' ? 'Pagado' : 'Pendiente' }}
                                </span>
                            </div>
                        </div>
                        <div class="order-items-preview">
                            @foreach($order->items->take(3) as $item)
                                <span class="order-item-chip">{{ $item->quantity }}x {{ $item->product->name }}</span>
                            @endforeach
                            @if($order->items->count() > 3)
                                <span class="order-item-chip">+{{ $order->items->count() - 3 }} más</span>
                            @endif
                        </div>
                        <div class="order-footer">
                            <span class="order-total">Total: ${{ number_format($order->total, 2) }}</span>
                            <div class="order-footer-actions">
                                <a href="{{ route('orders.invoice', $order->order_number) }}" class="btn btn-outline btn-sm">Factura PDF</a>
                                <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-outline btn-sm">Ver Detalle</a>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="pagination-wrapper">
                    {{ $orders->links() }}
                </div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon"><x-icon name="box" :size="32" :width="1.5" /></div>
                <h3>No tienes pedidos aún</h3>
                <p>Explora nuestros productos y haz tu primera compra</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Ver Productos</a>
            </div>
        @endif
    </div>
</section>
@endsection

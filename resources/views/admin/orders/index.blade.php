@extends('layouts.app')

@section('title', 'Pedidos Admin — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Gestionar Pedidos</h1>
        </div>

        {{-- Filtros --}}
        <form class="admin-filters" method="GET">
            <select name="status" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>En proceso</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Enviado</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Entregado</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Pago</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->user->name }}</td>
                            <td>{{ $order->items->count() }}</td>
                            <td>${{ number_format($order->total, 2) }}</td>
                            <td>
                                <span>{{ $order->payment_method_label }}</span>
                                @if($order->payment_method === 'card' && $order->card_brand)
                                    <br><small class="text-muted">{{ $order->card_brand }} •••• {{ $order->card_last4 }}</small>
                                @endif
                                @if($order->receipt_image)
                                    <br><a href="{{ asset('storage/' . $order->receipt_image) }}" target="_blank" class="receipt-link-sm">Ver comprobante</a>
                                @endif
                            </td>
                            <td><span class="order-status status-{{ $order->status }}">{{ $order->status_label }}</span></td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>En proceso</option>
                                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Enviado</option>
                                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Entregado</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No hay pedidos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $orders->links() }}
        </div>
    </div>
</section>
@endsection

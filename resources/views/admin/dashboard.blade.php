@extends('layouts.app')

@section('title', 'Panel Admin — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Panel de Administración</h1>
        </div>

        {{-- Stats Grid --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><x-icon name="box" :size="22" /></div>
                <div class="stat-info">
                    <span class="stat-value">{{ $stats['total_products'] }}</span>
                    <span class="stat-label">Productos</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><x-icon name="bag" :size="22" /></div>
                <div class="stat-info">
                    <span class="stat-value">{{ $stats['total_orders'] }}</span>
                    <span class="stat-label">Pedidos Totales</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><x-icon name="clock" :size="22" /></div>
                <div class="stat-info">
                    <span class="stat-value">{{ $stats['pending_orders'] }}</span>
                    <span class="stat-label">Pedidos Pendientes</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><x-icon name="users" :size="22" /></div>
                <div class="stat-info">
                    <span class="stat-value">{{ $stats['total_users'] }}</span>
                    <span class="stat-label">Usuarios</span>
                </div>
            </div>
            <div class="stat-card stat-card-highlight">
                <div class="stat-icon"><x-icon name="chart" :size="22" /></div>
                <div class="stat-info">
                    <span class="stat-value">${{ number_format($stats['total_revenue'], 2) }}</span>
                    <span class="stat-label">Ingresos</span>
                </div>
            </div>
        </div>

        {{-- Accesos Rápidos --}}
        <div class="admin-grid">
            <a href="{{ route('admin.products') }}" class="admin-card">
                <div class="admin-card-icon"><x-icon name="box" :size="22" /></div>
                <h3>Gestionar Productos</h3>
                <p>Crear, editar y eliminar productos del catálogo</p>
            </a>
            <a href="{{ route('admin.categories') }}" class="admin-card">
                <div class="admin-card-icon"><x-icon name="tag" :size="22" /></div>
                <h3>Categorías</h3>
                <p>Administrar categorías de productos</p>
            </a>
            <a href="{{ route('admin.orders') }}" class="admin-card">
                <div class="admin-card-icon"><x-icon name="receipt" :size="22" /></div>
                <h3>Pedidos</h3>
                <p>Ver y gestionar pedidos de clientes</p>
            </a>
            <div class="admin-card">
                <div class="admin-card-icon"><x-icon name="sparkles" :size="22" /></div>
                <h3>Búsqueda Semántica</h3>
                <p>Regenerar embeddings de todos los productos</p>
                <form action="{{ route('admin.embeddings.regenerate') }}" method="POST" style="margin-top: 1rem;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('¿Regenerar embeddings para todos los productos?')">
                        Regenerar Embeddings
                    </button>
                </form>
            </div>
        </div>

        {{-- Pedidos Recientes --}}
        @if($recentOrders->count())
        <div class="admin-section">
            <h2 class="subsection-title">Pedidos Recientes</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->user->name }}</td>
                                <td>${{ number_format($order->total, 2) }}</td>
                                <td><span class="order-status status-{{ $order->status }}">{{ $order->status_label }}</span></td>
                                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

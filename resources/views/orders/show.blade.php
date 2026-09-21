@extends('layouts.app')

@section('title', 'Pedido ' . $order->order_number . ' — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Inicio</a>
            <span>/</span>
            <a href="{{ route('orders.index') }}">Mis Pedidos</a>
            <span>/</span>
            <span class="breadcrumb-current">{{ $order->order_number }}</span>
        </nav>

        <div class="order-detail">
            <div class="order-detail-header">
                <div>
                    <h1 class="order-number-lg">{{ $order->order_number }}</h1>
                    <span class="order-date">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="order-header-actions">
                    <span class="order-status status-{{ $order->status }} status-lg">{{ $order->status_label }}</span>
                    <a href="{{ route('orders.invoice', $order->order_number) }}" class="btn btn-outline btn-sm">
                        <x-icon name="receipt" :size="16" /> Factura PDF
                    </a>
                </div>
            </div>

            {{-- Línea de tiempo del pedido --}}
            @php
                $steps = [
                    'pending' => ['label' => 'Recibido', 'icon' => 'receipt'],
                    'processing' => ['label' => 'En preparación', 'icon' => 'box'],
                    'shipped' => ['label' => 'Enviado', 'icon' => 'truck'],
                    'delivered' => ['label' => 'Entregado', 'icon' => 'check'],
                ];
                $orderFlow = ['pending', 'processing', 'shipped', 'delivered'];
                $currentIdx = array_search($order->status, $orderFlow);
                $isCancelled = $order->status === 'cancelled';
            @endphp
            @if(!$isCancelled)
            <div class="tracking-timeline">
                @foreach($orderFlow as $idx => $key)
                    @php
                        $done = $currentIdx !== false && $idx <= $currentIdx;
                        $lineDone = $currentIdx !== false && ($idx + 1) <= $currentIdx;
                    @endphp
                    <div class="timeline-step {{ $done ? 'done' : '' }} {{ $idx === $currentIdx ? 'current' : '' }}">
                        <span class="timeline-dot"><x-icon name="{{ $steps[$key]['icon'] }}" :size="18" /></span>
                        <span class="timeline-label">{{ $steps[$key]['label'] }}</span>
                        @if($idx < 3)<span class="timeline-line" style="{{ $lineDone ? 'background: var(--success);' : '' }}"></span>@endif
                    </div>
                @endforeach
            </div>
            @else
            <div class="cancelled-note">Este pedido fue cancelado.</div>
            @endif

            {{-- Rastreo en vivo (solo cuando ya fue enviado) --}}
            @if($order->status === 'shipped' && !$isCancelled)
            <div class="tracking-live">
                <div class="tracking-live-head">
                    <h3><x-icon name="pin" :size="20" /> Rastreo en vivo</h3>
                    <span class="tracking-eta">
                        @if($order->estimated_delivery)
                            Llega el {{ $order->estimated_delivery->format('d/m/Y') }}
                        @endif
                    </span>
                </div>

                <div class="tracking-map">
                    <svg viewBox="0 0 600 220" class="tracking-svg" aria-hidden="true">
                        <g class="map-grid" stroke-width="1">
                            @for($x = 30; $x <= 570; $x += 60)
                                <line x1="{{ $x }}" y1="10" x2="{{ $x }}" y2="210" />
                            @endfor
                            @for($y = 30; $y <= 190; $y += 40)
                                <line x1="10" y1="{{ $y }}" x2="590" y2="{{ $y }}" />
                            @endfor
                        </g>
                        <path id="route-path" d="M60,160 C160,160 140,70 260,70 S380,160 480,140 S540,90 545,80"
                              fill="none" stroke-width="3" stroke-dasharray="8 7" class="route-line" />
                        <g class="map-origin">
                            <circle cx="60" cy="160" r="9" class="map-pin" />
                            <text x="60" y="190" text-anchor="middle" class="map-label">Bodega</text>
                        </g>
                        <g class="map-dest">
                            <circle cx="545" cy="80" r="9" class="map-pin" />
                            <text x="545" y="110" text-anchor="middle" class="map-label">Tu dirección</text>
                        </g>
                        <g id="courier-marker">
                            <circle r="11" class="courier-halo" />
                            <circle r="6" class="courier-dot" />
                        </g>
                    </svg>
                    <div class="tracking-progress">
                        <div class="tracking-progress-track">
                            <div class="tracking-progress-fill" id="tracking-fill" style="width: {{ $order->shipping_progress }}%"></div>
                        </div>
                        <div class="tracking-progress-meta">
                            <span id="tracking-checkpoint">{{ $order->current_checkpoint }}</span>
                            <span>{{ $order->shipping_progress }}% del recorrido</span>
                        </div>
                    </div>
                </div>

                <div class="courier-card">
                    <span class="courier-icon"><x-icon name="truck" :size="22" /></span>
                    <div class="courier-info">
                        <strong>{{ $order->courier ?? 'Paquetería asignada' }}</strong>
                        <span>Guía: <code>{{ $order->tracking_number ?? '—' }}</code></span>
                    </div>
                </div>
            </div>

            @push('scripts')
            <script>
            (function () {
                var PROGRESS = {{ $order->shipping_progress }};
                var path = document.getElementById('route-path');
                var marker = document.getElementById('courier-marker');
                if (!path || !marker) return;
                try {
                    var len = path.getTotalLength();
                    var pt = path.getPointAtLength(len * (PROGRESS / 100));
                    marker.setAttribute('transform', 'translate(' + pt.x + ',' + pt.y + ')');
                } catch (e) {}
                // Actualizar posición cada 60 segundos
                setTimeout(function () { window.location.reload(); }, 60000);
            })();
            </script>
            @endpush
            @elseif($order->courier)
            <div class="courier-card">
                <span class="courier-icon"><x-icon name="truck" :size="22" /></span>
                <div class="courier-info">
                    <strong>{{ $order->courier }}</strong>
                    <span>Guía: <code>{{ $order->tracking_number ?? '—' }}</code></span>
                    @if($order->estimated_delivery)
                        <span>Entrega estimada: {{ $order->estimated_delivery->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
            @endif

            {{-- Historial de eventos --}}
            @if($order->trackingEvents->count())
            <div class="tracking-events">
                <h3>Historial del pedido</h3>
                <ul class="events-list">
                    @foreach($order->trackingEvents as $event)
                        <li class="event-item">
                            <span class="event-dot"></span>
                            <div>
                                <strong>{{ $event->title }}</strong>
                                @if($event->description)
                                    <p>{{ $event->description }}</p>
                                @endif
                                <span class="event-date">{{ $event->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="order-detail-grid">
                {{-- Información --}}
                <div class="order-info-card">
                    <h3>Información del Pedido</h3>
                    <div class="info-line">
                        <span class="info-label">Estado:</span>
                        <span class="order-status status-{{ $order->status }}">{{ $order->status_label }}</span>
                    </div>
                    <div class="info-line">
                        <span class="info-label">Pago:</span>
                        <span>{{ $order->payment_method_label }}
                            @if($order->payment_method === 'card' && $order->card_brand)
                                ({{ $order->card_brand }} •••• {{ $order->card_last4 }})
                            @endif
                            —
                            @if($order->payment_status === 'paid')
                                <span class="payment-paid order-status">Pagado</span>
                            @else
                                <span class="payment-pending order-status">Pendiente</span>
                            @endif
                        </span>
                    </div>
                    @if($order->payment_method === 'transfer')
                        <div class="info-line">
                            <span class="info-label">Cuenta destino:</span>
                            <span>{{ \App\Models\Order::BANK_NAME }} N.° {{ \App\Models\Order::BANK_ACCOUNT }} — {{ \App\Models\Order::BANK_HOLDER }}</span>
                        </div>
                    @endif
                    @if($order->receipt_image)
                        <div class="info-line">
                            <span class="info-label">Comprobante:</span>
                            <a href="{{ asset('storage/' . $order->receipt_image) }}" target="_blank" class="receipt-link">
                                <img src="{{ asset('storage/' . $order->receipt_image) }}" alt="Comprobante de pago" class="receipt-thumb">
                                Ver comprobante
                            </a>
                        </div>
                    @endif
                    <div class="info-line">
                        <span class="info-label">Envío a:</span>
                        <span>{{ $order->shipping_address }}</span>
                    </div>
                    @if($order->notes)
                        <div class="info-line">
                            <span class="info-label">Notas:</span>
                            <span>{{ $order->notes }}</span>
                        </div>
                    @endif
                </div>

                {{-- Resumen --}}
                <div class="order-summary-card">
                    <h3>Resumen</h3>
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span>${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="summary-line">
                        <span>IVA (13%)</span>
                        <span>${{ number_format($order->tax, 2) }}</span>
                    </div>
                    <div class="summary-line summary-total">
                        <span>Total</span>
                        <span>${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="order-items-section">
                <h3>Productos</h3>
                @if($order->status === 'delivered')
                    <p class="review-invite">¿Recibiste tu pedido? Deja una reseña y ayuda a otros compradores.</p>
                @endif
                @foreach($order->items as $item)
                    <div class="order-detail-item">
                        <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="order-item-img">
                        <div class="order-item-info">
                            <a href="{{ route('products.show', $item->product->slug) }}" class="order-item-name">
                                {{ $item->product->name }}
                            </a>
                        </div>
                        <div class="order-item-qty">
                            {{ $item->quantity }} x ${{ number_format($item->price, 2) }}
                        </div>
                        <div class="order-item-total">
                            ${{ number_format($item->total, 2) }}
                        </div>
                    </div>
                    @if($order->status === 'delivered' && !in_array($item->product_id, $reviewedIds ?? []))
                        <div class="order-review-cta">
                            <a href="{{ route('products.show', $item->product->slug) }}#resenas" class="btn btn-outline btn-sm">
                                <x-icon name="star" :size="15" /> Dejar reseña de {{ $item->product->name }}
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection

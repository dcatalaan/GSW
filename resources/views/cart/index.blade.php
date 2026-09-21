@extends('layouts.app')

@section('title', 'Carrito de Compras — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Carrito de Compras</h1>
            <p class="section-subtitle">{{ $count }} {{ \Illuminate\Support\Str::plural('producto', $count) }} en tu carrito</p>
        </div>

        @if($items->count())
            <div class="cart-layout">
                {{-- Items del carrito --}}
                <div class="cart-items">
                    @foreach($items as $item)
                        <div class="cart-item" id="cart-item-{{ $item->id }}">
                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="cart-item-image">
                            <div class="cart-item-info">
                                <a href="{{ route('products.show', $item->product->slug) }}" class="cart-item-name">
                                    {{ $item->product->name }}
                                </a>
                                <span class="cart-item-category">{{ $item->product->category->name }}</span>
                                <span class="cart-item-price">${{ number_format($item->product->price, 2) }}</span>
                            </div>
                            <div class="cart-item-actions">
                                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="cart-qty-form">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}" class="cart-qty-input" onchange="this.form.submit()">
                                </form>
                                <span class="cart-item-subtotal">${{ number_format($item->subtotal, 2) }}</span>
                                <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon text-danger" title="Eliminar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <div class="cart-actions">
                        <form action="{{ route('cart.clear') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline">Vaciar Carrito</button>
                        </form>
                        <a href="{{ route('products.index') }}" class="btn btn-outline">Seguir Comprando</a>
                    </div>
                </div>

                {{-- Resumen --}}
                <div class="cart-summary">
                    <h3 class="summary-title">Resumen del Pedido</h3>
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span>${{ number_format($total, 2) }}</span>
                    </div>
                    <div class="summary-line">
                        <span>IVA (13%)</span>
                        <span>${{ number_format($total * 0.13, 2) }}</span>
                    </div>
                    <div class="summary-line summary-total">
                        <span>Total</span>
                        <span>${{ number_format($total * 1.13, 2) }}</span>
                    </div>

                    @auth
                        <form action="{{ route('orders.store') }}" method="POST" class="checkout-form" enctype="multipart/form-data" id="checkout-form">
                            @csrf

                            @if($errors->any())
                                <div class="form-errors">
                                    <strong>No se pudo crear el pedido:</strong>
                                    <ul>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="form-group">
                                <label class="form-label">Dirección de Envío</label>
                                <textarea name="shipping_address" class="form-textarea" rows="3" required placeholder="Dirección completa...">{{ old('shipping_address', auth()->user()->billing_address) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Notas (opcional)</label>
                                <textarea name="notes" class="form-textarea" rows="2" placeholder="Instrucciones especiales...">{{ old('notes') }}</textarea>
                            </div>

                            {{-- Método de pago --}}
                            <div class="form-group">
                                <label class="form-label">Método de Pago</label>
                                <div class="pay-methods" role="radiogroup" aria-label="Método de pago">
                                    <label class="pay-method" data-method="card">
                                        <input type="radio" name="payment_method" value="card" {{ old('payment_method', 'card') === 'card' ? 'checked' : '' }}>
                                        <span class="pay-method-icon"><x-icon name="card" :size="22" /></span>
                                        <span class="pay-method-text"><strong>Tarjeta</strong><small>Débito o crédito</small></span>
                                    </label>
                                    <label class="pay-method" data-method="transfer">
                                        <input type="radio" name="payment_method" value="transfer" {{ old('payment_method') === 'transfer' ? 'checked' : '' }}>
                                        <span class="pay-method-icon"><x-icon name="bank" :size="22" /></span>
                                        <span class="pay-method-text"><strong>Transferencia</strong><small>Banco Agrícola</small></span>
                                    </label>
                                    <label class="pay-method" data-method="cash">
                                        <input type="radio" name="payment_method" value="cash" {{ old('payment_method') === 'cash' ? 'checked' : '' }}>
                                        <span class="pay-method-icon"><x-icon name="cash" :size="22" /></span>
                                        <span class="pay-method-text"><strong>Efectivo</strong><small>Pagas al recibir</small></span>
                                    </label>
                                </div>
                            </div>

                            {{-- Tarjeta --}}
                            <div class="pay-panel" id="pay-panel-card">
                                <div class="form-group">
                                    <label class="form-label" for="card_number">Número de Tarjeta</label>
                                    <div class="card-number-wrap">
                                        <input type="text" id="card_number" name="card_number" class="form-input"
                                               value="{{ old('card_number') }}" placeholder="1234 5678 9012 3456"
                                               inputmode="numeric" maxlength="23" autocomplete="cc-number">
                                        <span class="card-brand" id="card-brand">—</span>
                                    </div>
                                    <span class="form-error" id="card-number-error" style="display:none"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="card_name">Nombre en la Tarjeta</label>
                                    <input type="text" id="card_name" name="card_name" class="form-input"
                                           value="{{ old('card_name') }}" placeholder="Como aparece en la tarjeta" autocomplete="cc-name">
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="card_expiry">Vence (MM/AA)</label>
                                        <input type="text" id="card_expiry" name="card_expiry" class="form-input"
                                               value="{{ old('card_expiry') }}" placeholder="MM/AA" inputmode="numeric" maxlength="5" autocomplete="cc-exp">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="card_cvc">CVC</label>
                                        <input type="password" id="card_cvc" name="card_cvc" class="form-input"
                                               placeholder="123" inputmode="numeric" maxlength="4" autocomplete="cc-csc">
                                    </div>
                                </div>
                            </div>

                            {{-- Transferencia --}}
                            <div class="pay-panel" id="pay-panel-transfer" style="display:none">
                                <div class="bank-info">
                                    <div class="bank-info-row">
                                        <x-icon name="bank" :size="20" />
                                        <div>
                                            <strong>{{ \App\Models\Order::BANK_NAME }}</strong>
                                            <span>Cuenta N.° {{ \App\Models\Order::BANK_ACCOUNT }}</span>
                                            <span>{{ \App\Models\Order::BANK_HOLDER }}</span>
                                        </div>
                                    </div>
                                    <p class="bank-info-note">Realiza la transferencia por el total del pedido y sube la foto del comprobante.</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="receipt">Comprobante (foto)</label>
                                    <input type="file" id="receipt" name="receipt" class="form-input" accept="image/*">
                                </div>
                            </div>

                            {{-- Efectivo --}}
                            <div class="pay-panel" id="pay-panel-cash" style="display:none">
                                <p class="cash-note">Pagas en efectivo cuando recibas el pedido. No necesitas hacer nada más.</p>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-full">
                                Realizar Pedido
                            </button>
                        </form>

                        @push('scripts')
                        <script>
                        (function () {
                            var methods = document.querySelectorAll('.pay-method input[name="payment_method"]');
                            var panels = {
                                card: document.getElementById('pay-panel-card'),
                                transfer: document.getElementById('pay-panel-transfer'),
                                cash: document.getElementById('pay-panel-cash')
                            };

                            function detectBrand(digits) {
                                if (/^4/.test(digits)) return 'Visa';
                                if (/^(5[1-5]|2[2-7])/.test(digits)) return 'Mastercard';
                                if (/^3[47]/.test(digits)) return 'Amex';
                                if (/^6/.test(digits)) return 'Discover';
                                return '';
                            }

                            function luhnCheck(digits) {
                                if (digits.length < 13 || digits.length > 19) return false;
                                var sum = 0, alt = false;
                                for (var i = digits.length - 1; i >= 0; i--) {
                                    var d = parseInt(digits[i], 10);
                                    if (alt) { d *= 2; if (d > 9) d -= 9; }
                                    sum += d;
                                    alt = !alt;
                                }
                                return sum % 10 === 0;
                            }

                            function syncPanels() {
                                var selected = document.querySelector('.pay-method input[name="payment_method"]:checked');
                                var value = selected ? selected.value : 'card';
                                document.querySelectorAll('.pay-method').forEach(function (label) {
                                    label.classList.toggle('selected', label.dataset.method === value);
                                });
                                Object.keys(panels).forEach(function (key) {
                                    if (panels[key]) panels[key].style.display = key === value ? '' : 'none';
                                });
                            }

                            methods.forEach(function (radio) {
                                radio.addEventListener('change', syncPanels);
                            });
                            syncPanels();

                            var numInput = document.getElementById('card_number');
                            var brandEl = document.getElementById('card-brand');
                            var numError = document.getElementById('card-number-error');

                            if (numInput) {
                                numInput.addEventListener('input', function () {
                                    var digits = numInput.value.replace(/\D/g, '').slice(0, 19);
                                    numInput.value = digits.replace(/(\d{4})(?=\d)/g, '$1 ');
                                    var brand = detectBrand(digits);
                                    brandEl.textContent = brand || '—';
                                    brandEl.classList.toggle('known', !!brand);
                                });
                                numInput.dispatchEvent(new Event('input'));

                                var expInput = document.getElementById('card_expiry');
                                if (expInput) {
                                    expInput.addEventListener('input', function () {
                                        var d = expInput.value.replace(/\D/g, '').slice(0, 4);
                                        expInput.value = d.length > 2 ? d.slice(0, 2) + '/' + d.slice(2) : d;
                                    });
                                }

                                document.getElementById('checkout-form').addEventListener('submit', function (e) {
                                    var selected = document.querySelector('.pay-method input[name="payment_method"]:checked');
                                    if (!selected || selected.value !== 'card') return;
                                    var digits = numInput.value.replace(/\D/g, '');
                                    var brand = detectBrand(digits);
                                    if (!luhnCheck(digits)) {
                                        e.preventDefault();
                                        numError.textContent = 'El número de tarjeta no es válido.';
                                        numError.style.display = '';
                                        numInput.focus();
                                        return;
                                    }
                                    if (!brand) {
                                        e.preventDefault();
                                        numError.textContent = 'Solo se aceptan Visa, Mastercard, Amex y Discover.';
                                        numError.style.display = '';
                                        numInput.focus();
                                        return;
                                    }
                                    numError.style.display = 'none';
                                });
                            }
                        })();
                        </script>
                        @endpush
                    @else
                        <div class="auth-prompt">
                            <p>Inicia sesión para completar tu compra</p>
                            <a href="{{ route('login') }}" class="btn btn-primary btn-full">Iniciar Sesión</a>
                            <a href="{{ route('register') }}" class="btn btn-outline btn-full" style="margin-top: 0.5rem;">Crear Cuenta</a>
                        </div>
                    @endauth
                </div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon"><x-icon name="bag" :size="32" :width="1.5" /></div>
                <h3>Tu carrito está vacío</h3>
                <p>Explora nuestros productos y agrega algo al carrito</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Ver Productos</a>
            </div>
        @endif
    </div>
</section>
@endsection

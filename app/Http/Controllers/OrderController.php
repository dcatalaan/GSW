<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Historial de pedidos del usuario
     */
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Detalle de un pedido
     */
    public function show(Request $request, string $orderNumber)
    {
        $order = $request->user()
            ->orders()
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'trackingEvents'])
            ->firstOrFail();

        $reviewedIds = \App\Models\Review::where('user_id', $request->user()->id)
            ->whereIn('product_id', $order->items->pluck('product_id'))
            ->pluck('product_id')
            ->toArray();

        return view('orders.show', compact('order', 'reviewedIds'));
    }

    /**
     * Crear pedido a partir del carrito
     */
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:card,transfer,cash',
            'card_number' => 'exclude_unless:payment_method,card|required|string|max:23',
            'card_name' => 'exclude_unless:payment_method,card|required|string|max:100',
            'card_expiry' => 'exclude_unless:payment_method,card|required|string|size:5',
            'card_cvc' => 'exclude_unless:payment_method,card|required|string|min:3|max:4',
            'receipt' => 'exclude_unless:payment_method,transfer|required|image|max:4096',
        ]);

        $user = $request->user();
        $sessionId = session()->getId();

        // Obtener items del carrito
        $cartItems = CartItem::getCartItems($user->id, $sessionId);

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'El carrito está vacío');
        }

        // Validar tarjeta: marca + Luhn + vencimiento
        $cardBrand = null;
        $cardLast4 = null;
        if ($request->payment_method === Order::PAY_CARD) {
            $digits = preg_replace('/\D/', '', (string) $request->input('card_number', ''));

            if (!Order::luhnCheck($digits)) {
                return back()->withInput()->with('error', 'El número de tarjeta no es válido. Verifica e intenta de nuevo.');
            }

            $cardBrand = Order::detectCardBrand($digits);
            if ($cardBrand === 'Desconocida') {
                return back()->withInput()->with('error', 'No se reconoce la marca de la tarjeta. Solo se aceptan Visa, Mastercard, Amex y Discover.');
            }

            if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $request->card_expiry)) {
                return back()->withInput()->with('error', 'Fecha de vencimiento inválida. Usa el formato MM/AA.');
            }

            [$mm, $yy] = explode('/', $request->card_expiry);
            $expiry = \Carbon\Carbon::create(2000 + (int) $yy, (int) $mm, 1)->endOfMonth();
            if ($expiry->isPast()) {
                return back()->withInput()->with('error', 'La tarjeta está vencida.');
            }

            $cardLast4 = substr($digits, -4);
        }

        try {
            $order = DB::transaction(function () use ($user, $cartItems, $request, $cardBrand, $cardLast4) {
                $subtotal = $cartItems->sum(function ($item) {
                    return $item->product->price * $item->quantity;
                });

                $tax = $subtotal * 0.13; // IVA 13% El Salvador
                $total = $subtotal + $tax;

                // Comprobante de transferencia
                $receiptPath = null;
                if ($request->payment_method === Order::PAY_TRANSFER && $request->hasFile('receipt')) {
                    $receiptPath = $request->file('receipt')->store('receipts', 'public');
                }

                // Estado del pago según método
                $paymentStatus = match ($request->payment_method) {
                    Order::PAY_CARD => Order::PAYMENT_PAID,       // cobro simulado aprobado
                    Order::PAY_TRANSFER => Order::PAYMENT_PENDING, // pendiente de verificar comprobante
                    default => Order::PAYMENT_PENDING,             // efectivo: se paga al recibir
                };

                // Crear el pedido
                $order = $user->orders()->create([
                    'order_number' => Order::generateOrderNumber(),
                    'status' => Order::STATUS_PENDING,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'shipping_address' => $request->shipping_address,
                    'payment_method' => $request->payment_method,
                    'payment_status' => $paymentStatus,
                    'card_brand' => $cardBrand,
                    'card_last4' => $cardLast4,
                    'receipt_image' => $receiptPath,
                    'notes' => $request->notes,
                ]);

                $order->trackingEvents()->create([
                    'title' => 'Pedido recibido',
                    'description' => 'Tu pedido fue registrado y está pendiente de confirmación.',
                ]);

                // Códigos fiscales de la factura electrónica
                \App\Services\InvoiceService::generateCodes($order);

                if ($paymentStatus === Order::PAYMENT_PAID) {
                    $order->trackingEvents()->create([
                        'title' => 'Pago confirmado',
                        'description' => "Pago con tarjeta {$cardBrand} terminada en {$cardLast4} aprobado.",
                    ]);
                }

                // Crear items del pedido y actualizar stock
                foreach ($cartItems as $cartItem) {
                    $order->items()->create([
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->product->price,
                        'total' => $cartItem->product->price * $cartItem->quantity,
                    ]);

                    // Reducir stock
                    $cartItem->product->decrement('stock', $cartItem->quantity);
                }

                // Vaciar el carrito
                CartItem::where('user_id', $user->id)->delete();

                return $order;
            });

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Pedido creado exitosamente. Número: ' . $order->order_number);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el pedido. Intente de nuevo.');
        }
    }

    /**
     * Panel de administración de pedidos
     */
    public function adminIndex(Request $request)
    {
        $query = Order::with('user', 'items.product');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Actualizar estado de un pedido (admin)
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $newStatus = $request->status;
        $order->update(['status' => $newStatus]);

        // Eventos y datos de rastreo según el nuevo estado
        if ($newStatus === Order::STATUS_PROCESSING) {
            $order->trackingEvents()->create([
                'title' => 'Pedido en preparación',
                'description' => 'Tus productos están siendo empacados.',
            ]);
        }

        if ($newStatus === Order::STATUS_SHIPPED) {
            $courier = Order::COURIERS[array_rand(Order::COURIERS)];
            $trackingNumber = 'GSW-' . strtoupper(substr(md5($order->id . time()), 0, 10));
            $order->update([
                'courier' => $courier,
                'tracking_number' => $trackingNumber,
                'shipped_at' => now(),
                'estimated_delivery' => now()->addDays(2),
            ]);
            $order->trackingEvents()->create([
                'title' => "Enviado con {$courier}",
                'description' => "Guía {$trackingNumber}. Entrega estimada: " . now()->addDays(2)->format('d/m/Y') . ".",
            ]);
        }

        if ($newStatus === Order::STATUS_DELIVERED) {
            $order->update(['delivered_at' => now()]);
            $order->trackingEvents()->create([
                'title' => 'Pedido entregado',
                'description' => 'El paquete fue entregado en la dirección indicada.',
            ]);
        }

        if ($newStatus === Order::STATUS_CANCELLED) {
            $order->trackingEvents()->create([
                'title' => 'Pedido cancelado',
                'description' => 'El pedido fue cancelado.',
            ]);
        }

        return back()->with('success', 'Estado del pedido actualizado');
    }
}

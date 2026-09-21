<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'subtotal',
        'tax',
        'total',
        'shipping_address',
        'billing_address',
        'payment_method',
        'payment_status',
        'card_brand',
        'card_last4',
        'receipt_image',
        'courier',
        'tracking_number',
        'estimated_delivery',
        'shipped_at',
        'delivered_at',
        'invoice_uuid',
        'invoice_control',
        'invoice_seal',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'estimated_delivery' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Estados del pedido
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';

    const PAY_CARD = 'card';
    const PAY_TRANSFER = 'transfer';
    const PAY_CASH = 'cash';

    /** Paqueterías disponibles (ficticias) */
    const COURIERS = ['Express SV', 'Cargo Rápido', 'Envíos Nacionales'];

    /** Datos de la cuenta bancaria ficticia para transferencias */
    const BANK_NAME = 'Banco Agrícola';
    const BANK_ACCOUNT = '123456789';
    const BANK_HOLDER = 'Alexander Lopez';

    /**
     * Usuario que realizó el pedido
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Items del pedido
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Eventos de rastreo del pedido
     */
    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('created_at');
    }

    /**
     * Generar número de pedido único
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = self::where('order_number', 'like', "GSW-{$date}-%")
                         ->orderByDesc('order_number')
                         ->first();

        if ($lastOrder) {
            $sequence = (int) substr($lastOrder->order_number, -4) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf("GSW-%s-%04d", $date, $sequence);
    }

    /**
     * Obtener el label del estado
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_PROCESSING => 'En proceso',
            self::STATUS_SHIPPED => 'Enviado',
            self::STATUS_DELIVERED => 'Entregado',
            self::STATUS_CANCELLED => 'Cancelado',
            default => $this->status,
        };
    }

    /**
     * Label del método de pago
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            self::PAY_CARD => 'Tarjeta',
            self::PAY_TRANSFER => 'Transferencia',
            self::PAY_CASH => 'Efectivo',
            'cod' => 'Contra entrega',
            default => (string) $this->payment_method,
        };
    }

    /**
     * Detectar la marca de una tarjeta por sus primeros dígitos (BIN/IIN).
     * Visa: 4 · Mastercard: 51-55 y 2221-2720 · Amex: 34/37 · Discover: 6
     */
    public static function detectCardBrand(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number);

        if (preg_match('/^4/', $digits)) return 'Visa';
        if (preg_match('/^(5[1-5]|2[2-7])/', $digits)) return 'Mastercard';
        if (preg_match('/^3[47]/', $digits)) return 'Amex';
        if (preg_match('/^6/', $digits)) return 'Discover';

        return 'Desconocida';
    }

    /**
     * Validar número de tarjeta con el algoritmo de Luhn
     */
    public static function luhnCheck(string $number): bool
    {
        $digits = preg_replace('/\D/', '', $number);
        $len = strlen($digits);
        if ($len < 13 || $len > 19) return false;

        $sum = 0;
        $alt = false;
        for ($i = $len - 1; $i >= 0; $i--) {
            $d = (int) $digits[$i];
            if ($alt) {
                $d *= 2;
                if ($d > 9) $d -= 9;
            }
            $sum += $d;
            $alt = !$alt;
        }

        return $sum % 10 === 0;
    }

    /**
     * Progreso del envío 0-100 según tiempo transcurrido (para el mapa en vivo)
     */
    public function getShippingProgressAttribute(): int
    {
        if ($this->status === self::STATUS_DELIVERED) return 100;
        if (!$this->shipped_at || !$this->estimated_delivery) return 0;

        $total = $this->estimated_delivery->timestamp - $this->shipped_at->timestamp;
        if ($total <= 0) return 100;

        $elapsed = now()->timestamp - $this->shipped_at->timestamp;
        return (int) max(0, min(100, round(($elapsed / $total) * 100)));
    }

    /**
     * Punto de control actual del paquete según el progreso
     */
    public function getCurrentCheckpointAttribute(): string
    {
        if ($this->status === self::STATUS_DELIVERED) return 'Entregado';
        $p = $this->shipping_progress;
        if ($p <= 0) return 'En bodega de origen';
        if ($p < 35) return 'Centro de distribución';
        if ($p < 70) return 'En ruta de reparto';
        if ($p < 100) return 'Cerca de tu dirección';
        return 'En reparto final';
    }
}

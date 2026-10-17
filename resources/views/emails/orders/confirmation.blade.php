<x-mail::message>
# Pedido confirmado

Hola {{ $order->user->name }},

Recibimos tu pedido **{{ $order->order_number }}** y ya está en proceso. Te avisaremos cuando sea enviado.

<x-mail::table>
| Producto | Cant. | Precio |
|:---------|------:|-------:|
@foreach($order->items as $item)
| {{ $item->product->name ?? 'Producto' }} | {{ $item->quantity }} | ${{ number_format($item->total, 2) }} |
@endforeach
</x-mail::table>

**Subtotal:** ${{ number_format($order->subtotal, 2) }}
**IVA (13%):** ${{ number_format($order->tax, 2) }}
**Total pagado:** ${{ number_format($order->total, 2) }}

**Dirección de envío:**
{{ $order->shipping_address }}

<x-mail::button :url="route('orders.show', $order->order_number)">
Ver mi pedido
</x-mail::button>

Gracias por comprar en {{ config('app.name') }}.
</x-mail::message>

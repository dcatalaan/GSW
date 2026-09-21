<?php

namespace App\Services;

use App\Helpers\NumeroALetras;
use App\Models\Order;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Construye los datos del DTE (factura electrónica, demostración)
 * y genera los códigos fiscales del pedido.
 */
class InvoiceService
{
    /**
     * Generar y persistir los códigos fiscales de un pedido
     */
    public static function generateCodes(Order $order): Order
    {
        if (!$order->invoice_uuid) {
            $order->invoice_uuid = (string) Str::uuid();
        }
        if (!$order->invoice_control) {
            $order->invoice_control = 'DTE-01-GSW001-' . str_pad((string) $order->id, 15, '0', STR_PAD_LEFT);
        }
        if (!$order->invoice_seal) {
            $order->invoice_seal = strtoupper(substr(sha1($order->invoice_uuid . $order->invoice_control), 0, 40));
        }
        $order->save();

        return $order->fresh();
    }

    /**
     * Armar el arreglo de datos para la vista del DTE
     */
    public static function buildData(Order $order): array
    {
        $order->loadMissing(['items.product', 'user']);
        $user = $order->user;
        $emisor = config('invoice.emisor');

        $items = $order->items->map(function ($item, $idx) {
            return [
                'n' => $idx + 1,
                'qty' => $item->quantity,
                'desc' => $item->product->name ?? 'Producto',
                'unit' => (float) $item->price,
                'discount' => 0,
                'total' => (float) $item->total,
            ];
        })->toArray();

        $qrText = 'https://gswstore.local/dte/verificar/' . $order->invoice_uuid;
        $qrSvg = QrCode::format('svg')->size(120)->margin(0)->generate($qrText);

        return [
            'order' => $order,
            'emisor' => $emisor,
            'receptor' => [
                'nombre' => $user->billing_name ?: $user->name,
                'documento' => $user->doc_number ?: '—',
                'tipo_doc' => $user->doc_type ?: '—',
                'nrc' => $user->nrc ?: '—',
                'correo' => $user->email,
                'telefono' => $user->phone ?: '—',
                'direccion' => $order->shipping_address,
                'ciudad' => $user->city ?: 'EL SALVADOR',
                'actividad' => 'Compra de productos al por menor',
            ],
            'items' => $items,
            'subtotal' => (float) $order->subtotal,
            'iva' => (float) $order->tax,
            'total' => (float) $order->total,
            'letras' => NumeroALetras::convertir((float) $order->total),
            'condicion' => $order->payment_status === Order::PAYMENT_PAID ? 'CONTADO' : 'A CRÉDITO',
            'metodo_pago' => $order->payment_method_label,
            'fecha_emision' => $order->created_at->format('d-m-Y'),
            'fecha_procesado' => $order->created_at->format('d-m-Y H:i:s'),
            'qr_svg' => $qrSvg,
        ];
    }
}

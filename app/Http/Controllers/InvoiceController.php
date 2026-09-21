<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Descargar factura electrónica en PDF
     */
    public function download(Request $request, string $orderNumber)
    {
        $order = $request->user()
            ->orders()
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $order = InvoiceService::generateCodes($order);
        $data = InvoiceService::buildData($order);

        $pdf = Pdf::loadView('invoices.dte', $data)
            ->setPaper('letter', 'portrait');

        return $pdf->download('DTE-' . $order->order_number . '.pdf');
    }
}

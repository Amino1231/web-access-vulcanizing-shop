<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class CustomerReceiptController extends Controller
{
    public function __invoke(Order $order)
    {
        abort_unless((int) $order->customer_id === (int) Auth::id(), 403);

        $order->load([
            'customer:id,name,email,phone',
            'tenant:id,name,phone,address,email',
            'items:id,order_id,product_name,variant_label,sku,quantity,unit_price,line_total',
        ]);

        return Pdf::loadView('pdf.receipt', ['order' => $order])
            ->setPaper('a4', 'portrait')
            ->download('Receipt-' . $order->order_number . '.pdf');
    }
}
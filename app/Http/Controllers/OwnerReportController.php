<?php

namespace App\Http\Controllers;

use App\Services\SalesReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerReportController extends Controller
{
    public function sales(Request $request)
    {
        $user = Auth::user();
        $tenant = $user?->currentTenant();

        abort_unless($tenant, 403);

        $service = new SalesReportService(
            tenantId: $tenant->id,
            range:    $request->query('range', 'month'),
            from:     $request->query('from'),
            to:       $request->query('to'),
        );

        $pdf = Pdf::loadView('pdf.owner.sales-report', [
            'tenant'          => $tenant,
            'service'         => $service,
            'label'           => $service->label,
            'start'           => $service->start,
            'end'             => $service->end,
            'kpis'            => $service->kpis(),
            'topProducts'     => $service->topProducts(),
            'topCustomers'    => $service->topCustomers(),
            'salesByEmployee' => $service->salesByEmployee(),
            'sales'           => $service->allSales(),
        ])->setPaper('a4', 'portrait');

        $filename = sprintf(
            'sales-report-%s-to-%s.pdf',
            $service->start->format('Ymd'),
            $service->end->format('Ymd'),
        );

        return $pdf->download($filename);
    }
}
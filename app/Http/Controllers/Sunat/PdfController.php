<?php

namespace App\Http\Controllers\Sunat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sale;

class PdfController extends Controller
{
    public function single(Request $request)
    {
        
    }

    public function batch(Request $request)
    {
        $since = Carbon::parse($request->since) ?? now();
        $until = Carbon::parse($request->until) ?? now();

        $sales = Sale::with('saleDetails.product', 'customer.identificationDocument', 'serie.voucherType')
            ->whereBetween('sale.date_issue', [$since, $until])
            ->where('sale.serie_id', 2)
            ->orderBy('sale.document_number', 'asc')
            ->get();
        
        $pdf = Pdf::loadView('pdf.invoice', compact('sales'))->setPaper('A4', 'portrait');
        return $pdf->stream();
    }
}
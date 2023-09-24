<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Serie;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $serie_id = $request->serie_id ?? 1;
        $customer_id = $request->customer_id ?? 1;
        $type_of_payment_id = $request->type_of_payment_id ?? 1;
        $user_id = 1;
        $details = collect($request->details);

        $serie = Serie::find($serie_id);
        $serie->current_number = $serie->current_number + 1;
        $serie->save();

        $now = now();

        $total = 0;
        $subtotal = 0;
        $total_igv = 0;

        foreach ($details as $detail) {
            $detail = (object)$detail;
            $total += $detail->total;
            $subtotal += $detail->total / (1.18);
            $total_igv += $detail->total - ($detail->total / (1.18));
        }

        $sale = Sale::create([
            'document_number' => $serie->current_number,
            'date_issue' => $now,
            'date_due' => $now,

            'global_discount' => 0,
            'item_discount' => 0,
            'total_discount' => 0,

            'subtotal' => $subtotal,
            'total_igv' => $total_igv,
            'total_exonerated' => 0,
            'total_unaffected' => 0,
            'total_free' => 0,
            'total_taxed' => $total_igv,
            'total' => $total,

            // 'received_money',
            // 'change',

            'type_of_payment_id' => (int)$type_of_payment_id,
            'serie_id' => $serie_id,
            'customer_id' => $customer_id,
            'user_id' => $user_id
        ]);

        
        SaleDetail::insert($details->map(function ($detail) use ($sale) {
            $detail = (object)$detail;
            return [
                'discount' => 0,
                'price' => $detail->is_weighable ? $detail->total : $detail->sale_price,
                'unit_value' => $detail->is_weighable ? $detail->total : $detail->sale_price,
                'quantity' => $detail->quantity,

                'purchase_price' => $detail->purchase_price,

                'total_igv' => $detail->total - ($detail->total / (1.18)),
                'subtotal' => $detail->total / (1.18),
                'total' => $detail->total,

                'sale_id' => $sale->id,
                'product_id' => $detail->id,
            ];
        })->toArray());

        $sale->serie->voucherType;
        $sale->customer;

        $sale->sale_details = $details;

        return response()->json($sale, 202);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }
}

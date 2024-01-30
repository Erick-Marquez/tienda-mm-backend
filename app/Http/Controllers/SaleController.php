<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Serie;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rows = $request['rows'] ?? 5;
        $page = $request['page'] + 1 ?? 1;
        $search = $request['filters']['global']['value'] ?? null;

        $query = Sale::with('serie')->whereHas('serie', function ($q) {
            return $q->where('voucher_type_id', 1);
        });

        if (isset($search)) {
            $matches = [];
            $regex = '/([N][0-9][0-9][0-9])-([0-9])+/';
            if (preg_match($regex, $search, $matches)) {
                [$serie, $number] = explode('-', $search);
                $query->whereHas('serie', function ($q) use ($serie) {
                    return $q->where('serie', $serie);
                });
                $query->where('document_number', $number);
            }   
        }


        $query->orderBy('document_number', 'DESC');

        $sales = $query->paginate($rows, ['*'], 'page', $page);

        return $sales;
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
            'total_taxed' => $subtotal,
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

    public function convertInvoice(Request $request)
    {
        return $request;
    }

    public function batchConvertInvoice(Request $request)
    {
        $since = Carbon::parse($request->since) ?? now();
        $until = Carbon::parse($request->until) ?? now();
        $serie_id = 2; // BOLETA
        $user_id = 1; // USER ADMIN

        $details = DB::table('sale_details as sd')
            ->join('sales as s', 's.id', 'sd.sale_id')
            ->join('products as p', 'p.id', 'sd.product_id')
            ->whereBetween('s.date_issue', [$since, $until])
            ->where('p.is_invoiceable', true)
            ->orderBy('date_issue', 'asc')
            ->get([
                's.date_issue',

                'sd.*'
            ]);

        $days = $details->groupBy('date_issue');


        foreach ($days as $day => $saleDetails) {
            
            $ventas = collect([]);

            $total = 0;
            $items = collect([]);

            foreach ($saleDetails as $saleDetail) {

                $total += $saleDetail->total;
                $items->push($saleDetail);

                if ($total > 100) {

                    // REALIZAR LA VENTA CON TODO LOS DETALLES
                    $this->insertInvoice($day, $total, $items);

                    $total = 0;
                    $subtotal = 0;
                    $total_igv = 0;
                    $items = collect([]);

                }

            }

            if ($total > 0) {

                // REALIZAR LA VENTA CON TODO LOS DETALLES
                $this->insertInvoice($day, $total, $items);

                $total = 0;
                $subtotal = 0;
                $total_igv = 0;
                $items = collect([]);

            }


        }


        return 'terminado';

        

        // $sales = Sale::with('saleDetails.product')
        //     ->where('serie_id', 1)
        //     ->whereNull('invoice_id')
        //     ->whereBetween('date_issue', [$since, $until])
        //     ->orderBy('id', 'asc')
        //     ->get();

        // foreach ($sales as $sale) {

        //     $total = 0;
        //     $subtotal = 0;
        //     $total_igv = 0;
        //     $details = collect([]);
        //     foreach ($sale->saleDetails as $detail) {
        //         if ($detail->product->is_invoiceable) {
        //             $total += $detail->total;
        //             $subtotal += $detail->total / (1.18);
        //             $total_igv += $detail->total - ($detail->total / (1.18));
        //             $details->push($detail);
        //         }
        //     }

        //     if ($details->count() > 0) {

        //         $serie = Serie::find($serie_id);
        //         $serie->current_number = $serie->current_number + 1;
        //         $serie->save();

        //         $invoice = Sale::create([
        //             'document_number' => $serie->current_number,
        //             'date_issue' => $sale->date_issue,
        //             'date_due' => $sale->date_due,
        
        //             'global_discount' => 0,
        //             'item_discount' => 0,
        //             'total_discount' => 0,
        
        //             'subtotal' => $subtotal,
        //             'total_igv' => $total_igv,
        //             'total_exonerated' => 0,
        //             'total_unaffected' => 0,
        //             'total_free' => 0,
        //             'total_taxed' => $subtotal,
        //             'total' => $total,
        
        //             // 'received_money',
        //             // 'change',
        
        //             'type_of_payment_id' => $sale->type_of_payment_id,
        //             'serie_id' => $serie_id,
        //             'customer_id' => $sale->customer_id,
        //             'user_id' => $user_id
        //         ]);

        //         SaleDetail::insert($details->map(function ($detail) use ($invoice) {
        //             return [
        //                 'discount' => 0,
        //                 'price' => $detail->price,
        //                 'unit_value' => $detail->unit_value,
        //                 'quantity' => $detail->quantity,
        
        //                 'purchase_price' => $detail->purchase_price,
        
        //                 'total_igv' => $detail->total_igv,
        //                 'subtotal' => $detail->subtotal,
        //                 'total' => $detail->total,
        
        //                 'sale_id' => $invoice->id,
        //                 'product_id' => $detail->product_id,
        //             ];
        //         })->toArray());

        //         Sale::where('id', $sale->id)->update([
        //             'invoice_id' => $invoice->id
        //         ]);

        //     }

        // }


        return "terminado";
    }


    public function insertInvoice($day, $total, $details)
    {
        $serie_id = 2;

        $serie = Serie::find($serie_id);
        $serie->current_number = $serie->current_number + 1;
        $serie->save();

        $invoice = Sale::create([
            'document_number' => $serie->current_number,
            'date_issue' => $day,
            'date_due' => $day,

            'global_discount' => 0,
            'item_discount' => 0,
            'total_discount' => 0,

            'subtotal' => round($total / (1.18), 2),
            'total_igv' => round($total - ($total / (1.18)), 2),
            'total_exonerated' => 0,
            'total_unaffected' => 0,
            'total_free' => 0,
            'total_taxed' => round($total / (1.18), 2),
            'total' => $total,

            'type_of_payment_id' => 1,
            'serie_id' => $serie_id,
            'customer_id' => 1,
            'user_id' => 1
        ]);

        SaleDetail::insert($details->map(function ($detail) use ($invoice) {
            return [
                'discount' => 0,
                'price' => $detail->price,
                'unit_value' => $detail->unit_value,
                'quantity' => $detail->quantity,

                'purchase_price' => $detail->purchase_price,

                'total_igv' => $detail->total_igv,
                'subtotal' => $detail->subtotal,
                'total' => $detail->total,

                'sale_id' => $invoice->id,
                'product_id' => $detail->product_id,
            ];
        })->toArray());
    }
}

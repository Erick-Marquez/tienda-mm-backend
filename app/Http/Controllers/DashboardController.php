<?php

namespace App\Http\Controllers;

use DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $now = now()->toDateString();
        $yesterday = now()->subDays(1)->toDateString();

        $general = DB::table('sales')
            ->where('date_issue', $now)
            ->orWhere('date_issue', $yesterday)
            ->groupBy('date_issue')
            ->orderBy('date_issue')
            ->get([
                'date_issue',
                DB::raw('count(total) as sales_count'),
                DB::raw('sum(total) as sales_sum')
            ]);
        
        $general = [
            'sales_count' => [
                'today' => $general[1]->sales_count,
                'yesterday' => $general[0]->sales_count,
                'difference' => $general[1]->sales_count - $general[0]->sales_count
            ],
            'sales_total' => [
                'today' => $general[1]->sales_sum,
                'yesterday' => $general[0]->sales_sum,
                'difference' => round($general[1]->sales_sum - $general[0]->sales_sum, 2)
            ], 
        ];
        
        
        // $products = DB::table('sale_details as sd')
        //     ->leftJoin('sales as s', 's.id', 'sd.sale_id')
        //     ->leftJoin('products as p', 'p.id', 'sd.product_id')
        //     ->where('s.date_issue', $now)
        //     ->where('p.sale_price', '>', 0.3)
        //     ->whereNot('p.internal_code', 'like', '20%')
        //     ->groupBy('product_id')
        //     ->orderBy('products_sum', 'desc')
        //     ->take(5)
        //     ->get([
        //         DB::raw('sum(sd.quantity) as products_sum'),
        //         'p.name'
        //     ]);

        $chart = DB::table('sales as s')
            ->where('s.date_issue', $now)
            ->groupBy(DB::raw("DATE_FORMAT(s.created_at, '%H')"))
            ->get([
                DB::raw("DATE_FORMAT(s.created_at, '%H') AS 'interval'"),
                DB::raw("SUM(s.total) AS sales_total"),
            ]);
        $ARRAY_HOUR_INTERVAL = [
            ['03', '04', '05'],
            ['06', '07', '08'],
            ['09', '10', '11'],
            ['12', '13', '14'],
            ['15', '16', '17'],
            ['18', '19', '20'],
            ['21', '22', '23'],
            ['24', '01', '02']
        ];
        $chart_final = collect([]);

        for ($i=0; $i < count($ARRAY_HOUR_INTERVAL); $i++) { 
            $chart_final->push(round($chart->whereBetween('interval', $ARRAY_HOUR_INTERVAL[$i])
            ->sum('sales_total'), 2));
        }

        return response()->json([
            'general' => $general,
            'chart' => $chart_final,
            // 'popular_products' => $products
        ], 200);
    }
}

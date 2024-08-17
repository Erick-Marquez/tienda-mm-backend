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

        $products = DB::table('sale_details')
            ->leftJoin('sales', 'sales.id', 'sale_details.sale_id')
            ->leftJoin('products', 'products.id', 'sale_details.product_id')
            ->where('products.is_weighable', true)
            ->whereIn('sales.date_issue', [$now, $yesterday])
            ->groupBy('sales.date_issue')
            ->orderBy('sales.date_issue')
            ->get([
                'sales.date_issue',
                DB::raw('sum(sale_details.quantity) as product_count')
            ]);
        
        $todayCount = $general->where('date_issue', $now)->sum('sales_count');
        $todaySum = $general->where('date_issue', $now)->sum('sales_sum');
        $todayProductCount = $products->where('date_issue', $now)->sum('product_count');

        $yesterdayCount = $general->where('date_issue', $yesterday)->sum('sales_count');
        $yesterdaySum = $general->where('date_issue', $yesterday)->sum('sales_sum');
        $yesterdayProductCount = $products->where('date_issue', $yesterday)->sum('product_count');

        
        $general = [
            'sales_count' => [
                'today' => $todayCount,
                'yesterday' => $yesterdayCount,
                'difference' => $todayCount - $yesterdayCount
            ],
            'sales_total' => [
                'today' => $todaySum,
                'yesterday' => $yesterdaySum,
                'difference' => round($todaySum - $yesterdaySum, 2)
            ], 
            'products_count' => [
                'today' => $todayProductCount,
                'yesterday' => $yesterdayProductCount,
                'difference' => round($todayProductCount - $yesterdayProductCount, 2)
            ]
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

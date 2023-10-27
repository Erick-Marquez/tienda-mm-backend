<?php

namespace App\Http\Controllers\Sunat;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Summary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        $rows = $request['rows'] ?? 5;
        $page = $request['page'] + 1 ?? 1;
        $summaries = Summary::orderBy('date_issue', 'DESC')
            ->paginate($rows, ['*'], 'page', $page);

        return $summaries;
    }

    public function store(Request $request)
    {

        return $request;
    }

    public function getDocuments(Request $request)
    {
        $date_issue = Carbon::parse($request['date_of_reference'])->toDateString();

        //* GET Sales
        $sales = Sale::where('date_issue', $date_issue)
            // ->where('recibo_tipo_documento', '03')
            // ->where('recibo_facturador_estado', FacturadorService::PENDING_CODE)
            // ->where('recibo_serie_id', '!=', 'W001') // Serie Falsa
            ->orderBy('document_number','ASC')
            ->take(500)
            ->get([
                'date_issue',
                'total'
            ]);


        return response()->json([
            'sales_count' => $sales->count(),
            'sales_sum' => round($sales->sum('total'), 2)
        ], 200);
    }
}
<?php

namespace App\Http\Controllers\Sunat;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Summary;
use App\Services\Facturacion\Summary as FacturacionSummary;
use App\Services\Facturacion\Sunat;
use Carbon\Carbon;
use Exception;
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
        $since = Carbon::parse($request->since) ?? now();
        $until = Carbon::parse($request->until) ?? now();

        $sales = Sale::whereBetween('date_issue', [$since, $until])
            ->where('serie_id', 2)
            ->where('sunat_state', Sunat::PENDING_CODE)
            ->orderBy('document_number','ASC')
            ->get(['id', 'date_issue']);

        $days = $sales->groupBy('date_issue');

        $summaries = collect([]);

        foreach ($days as $day => $salesDay) {

            //* CREATE resumen
            $date = date('Y-m-d');
            $numeration = Summary::where('date_issue', $date)->count() + 1;
            $identifier = join('-', ['RC', Carbon::parse($date)->format('Ymd'), $numeration]);

            $summary = Summary::create([
                'date_issue' => $date,
                'date_of_reference' => $day,
                
                'type' => '1',
                'identifier' => $identifier,
    
                'user_id' => 1
            ]);

            $summary->sales()->attach($salesDay->pluck('id'));

            Sale::whereIn('id', $salesDay->pluck('id'))->update([
                'sunat_state' => Sunat::PENDING_SUMMARY_CODE
            ]);

            $summaries->push($summary);
        }

        return response()->json($summaries, 200);
    }

    public function getDocuments(Request $request)
    {
        $date_issue = Carbon::parse($request['date_of_reference'])->toDateString();

        //* GET Sales
        $sales = Sale::where('date_issue', $date_issue)
            ->where('serie_id', 2)
            ->where('sunat_state', Sunat::PENDING_CODE)
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

    public function send(Request $request)
    {
        $date_issue = Carbon::parse($request['date_issue'])->toDateString();

        $dataSummaries = Summary::with('sales.serie', 'sales.customer')
            ->where('date_issue', [$date_issue])
            ->get();

        $responseSunat = collect([]);

        foreach ($dataSummaries as $dataSummary) {
            
            $summary = new FacturacionSummary();
            $summary->sendSummaryToSunat($dataSummary->toArray());
            $response = $summary->getResponse();
    
            Summary::find($dataSummary->id)
                ->update([
                    'ticket' => $response['ticket'] ?? '',
                    'sunat_code' => $response['code'],
                    'sunat_state' => $response['state_code'],
                    'sunat_response' => $response['description'],
                    'sunat_notes' => json_encode($response['notes']),
                ]);
            
            Sale::whereIn('id', $dataSummary->sales->modelKeys())->update([
                'sunat_state' => $response['state_code']
            ]);
            
            $responseSunat->push($response);
        }

        return response()->json($responseSunat, 200);
    }

    public function ticket(Request $request)
    {
        $date_issue = Carbon::parse($request['date_issue'])->toDateString();

        $dataSummaries = Summary::with('sales')
            ->where('date_issue', [$date_issue])
            ->where('sunat_state', '!=', Sunat::ACCEPTED_CODE)
            ->get();
        
        
        $responseSunat = collect([]);

        foreach ($dataSummaries as $dataSummary) {
            
            $summary = new FacturacionSummary();
            $summary->sendTicketToSunat($dataSummary->ticket, $dataSummary->date_issue);
            $response = $summary->getResponse();

            $update = [
                'sunat_code' => $response['code'],
                'sunat_state' => $response['state_code'],
                'sunat_response' => $response['description'],
                'sunat_notes' => json_encode($response['notes'])
            ];

            if (key_exists('path_xml', $response)) $update['sunat_path_xml'] = $response['path_xml'];
            if (key_exists('path_cdr', $response)) $update['sunat_path_cdr'] = $response['path_cdr'];
            if (key_exists('filename', $response)) $update['sunat_filename'] = $response['filename'];

            Summary::find($dataSummary->id)
                ->update($update);
            
            Sale::whereIn('id', $dataSummary->sales->modelKeys())->update([
                'sunat_state' => $response['state_code']
            ]);
            
            $responseSunat->push($response);
        }

        return response()->json($responseSunat, 200);
    }


}
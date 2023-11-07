<?php

namespace App\Services\Facturacion\Inputs;

class SummaryInput
{
    /**
     * @param array $data
     *
     * @return object
     */
    public static function format($data)
    {
        return (object)[
            'date_of_reference' => $data['date_of_reference'],
            'date_of_issue' => $data['date_issue'],
            'identifier' => $data['identifier'],
            'summary_status_type_id' => $data['type'],
            'documents' => SummaryDocumentInput::arrayFormat($data['sales'])
        ];
    }
}
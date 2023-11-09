<?php

namespace App\Services\Facturacion\Inputs;

class SummaryDocumentInput
{
    /**
     * @param array $data
     *
     * @return object
     */
    public static function format($document)
    {
        return (object)[
            'series' => $document['serie']['serie'],
            'number' => $document['document_number'],
            'document_type_id' => '03',
            'currency_type_id' => 'PEN',

            'customer' => (object)[
                'identity_document_type_id' => $document['customer']['identification_document_id'],
                'number' => $document['customer']['document']
            ],

            
            'total_exportation' => 0,
            'total_taxed' => $document['total_taxed'] ?? 0,
            'total_unaffected' => $document['total_unaffected'] ?? 0,
            'total_exonerated' => $document['total_exonerated'] ?? 0,
            'total_free' => $document['total_free'] ?? 0, 
            'total_igv' => $document['total_igv'] ?? 0,
            'total_isc' => 0,
            'total_other_taxes' => 0,
            'total_plastic_bag_taxes' => 0,
            'total_ivap' => 0,
            'total_charge' => 0,
            'total' => $document['total'] ?? 0,

            'perception' => null
        ];
    }

    public static function arrayFormat($documents)
    {
        return array_map(function ($document) {
            return self::format($document);
        }, $documents);
    }
}
<?php

namespace App\Services\Facturacion\Inputs;

use App\Services\Facturacion\Helpers\Number\NumberLetter;

class InvoiceInput
{
    /**
     * @param array $data
     *
     * @return object
     */
    public static function format($data)
    {
        return (object)[
            'operation_type_id' => $data['codigo_tipo_operacion'] ?? '0101',
            'document_type_id' => $data['codigo_tipo_documento'],
            'series' => $data['serie_documento'],
            'number' => $data['numero_documento'],
            'date_of_issue' => $data['fecha_de_emision'],
            'time_of_issue' => $data['hora_de_emision'] ?? '00:00:00',
            'date_of_due' => $data['fecha_de_vencimiento'],
            'currency_type_id' => $data['codigo_tipo_moneda'],

            'purchase_order' => $data['numero_orden_de_compra'] ?? null,

            'method_of_payment' => $data['forma_de_pago'] ?? 'Contado',
            'payment_fees' =>  self::paymentFees($data['cuotas'] ?? []),

            'customer' => CustomerInput::format($data['datos_del_cliente_o_receptor']),

            'total_prepayment' => $data['totales']['total_anticipos'] ?? 0,
            'total_charge' => $data['totales']['total_cargos'] ?? 0,
            'total_exportation' => $data['totales']['total_exportacion'] ?? 0,
            'total_free' => $data['totales']['total_operaciones_gratuitas'] ?? 0,
            'total_taxed' => $data['totales']['total_operaciones_gravadas'] ?? 0,
            'total_unaffected' => $data['totales']['total_operaciones_inafectas'] ?? 0,
            'total_exonerated' => $data['totales']['total_operaciones_exoneradas'] ?? 0,
            'total_igv' => $data['totales']['total_igv'] ?? 0,
            'total_base_isc' => $data['totales']['total_base_isc'] ?? 0,
            'total_isc' => $data['totales']['total_isc'] ?? 0,
            'total_base_other_taxes' => $data['totales']['total_base_otros_impuestos'] ?? 0,
            'total_other_taxes' => $data['totales']['total_otros_impuestos'] ?? 0,
            'total_plastic_bag_taxes' => $data['totales']['total_impuestos_bolsa_plastica'] ?? 0,
            'total_taxes' => $data['totales']['total_impuestos'] ?? 0,
            'total_value' => $data['totales']['total_valor'] ?? 0,
            'total' => $data['totales']['total_venta'] ?? 0,

            'items' => ItemInput::arrayFormat($data['items']),

            'charges' => self::charges($data['cargos'] ?? []),
            'discounts' => self::discounts($data['descuentos'] ?? []),
            'prepayments' => self::prepayments($data['anticipos'] ?? []),
            'guides' => self::guides($data['guias'] ?? []),
            'related' => self::related($data['relacionados'] ?? []),
            'perception' => self::perception($data['percepcion'] ?? null),
            'detraction' => self::detraction($data['detraccion'] ?? null),

            'legends' => self::legends($data['leyendas'] ?? [], $data['totales']['total_venta'] ?? 0),
            'payments' => self::payments($data['pagos'] ?? [], $data['fecha_de_emision']),
        ];
    }

    private static function paymentFees($paymentFees)
    {
        return array_map(function ($paymentFee) {
            return (object)[
                'amount' => $paymentFee['monto'],
                'date_of_payment' => $paymentFee['fecha_de_pago'],
            ];
        }, $paymentFees);
    }

    private static function charges($charges)
    {
        return array_map(function ($charge) {
            return (object)[
                'charge_type_id' => $charge['codigo'],
                'description' => $charge['descripcion'],
                'factor' => $charge['factor'],
                'amount' => $charge['monto'],
                'base' =>  $charge['base'],
            ];
        }, $charges);
    }

    private static function discounts($discounts)
    {
        return array_map(function ($discount) {
            return (object)[
                'discount_type_id' => $discount['codigo'],
                'description' => $discount['descripcion'],
                'factor' => $discount['factor'],
                'amount' => $discount['monto'],
                'base' =>  $discount['base'],
            ];
        }, $discounts);
    }

    private static function prepayments($prepayments)
    {
        return array_map(function ($prepayment) {
            return (object)[
                'number' => $prepayment['numero'],
                'document_type_id' => $prepayment['codigo_tipo_documento'],
                'amount' => $prepayment['monto'],
                'total' => $prepayment['total']
            ];
        }, $prepayments);
    }

    private static function guides($guides)
    {
        return array_map(function ($guide) {
            return (object)[
                'number' => $guide['numero'],
                'document_type_id' => $guide['codigo_tipo_documento'],
            ];
        }, $guides);
    }
    
    private static function related($relateds)
    {
        return array_map(function ($related) {
            return (object)[
                'number' => $related['numero'],
                'document_type_id' => $related['codigo_tipo_documento'],
                'amount' => $related['monto']
            ];
        }, $relateds);
    }

    private static function perception($perception)
    {
        return $perception == null 
            ? null
            : (object)[
                'code' => $perception['codigo'],
                'percentage' => $perception['porcentaje'],
                'amount' => $perception['monto'],
                'base' => $perception['base'],
            ];
    }

    private static function detraction($detraction)
    {
        return $detraction == null
            ? null 
            : (object)[
                'detraction_type_id' => $detraction['codigo_tipo_detraccion'],
                'percentage' => $detraction['porcentaje'],
                'amount' => $detraction['monto'],
                'payment_method_id' => $detraction['codigo_metodo_pago'],
                'bank_account' => $detraction['cuenta_bancaria'],
            ];
    }

    private static function legends($legends, $total)
    {
        $legends = array_map(function ($legend) {
            return (object)[
                'code' => $legend['codigo'],
                'value' => $legend['valor']
            ];
        }, $legends);

        array_unshift($legends, (object)[
            'code' => 1000,
            'value' => NumberLetter::convertToLetter($total)
        ]);
        return $legends;
    }

    private static function payments($payments, $date_of_issue)
    {
        return array_map(function ($payment) use ($date_of_issue) {
            return (object)[
                'date_of_payment' => $date_of_issue ?? null,
                'payment_method_type_id' => $payment['codigo_metodo_pago'],
                'reference' => $payment['referencia'] ?? null,
                'payment' => $payment['monto'] ?? 0,
            ];
        }, $payments);
    }
}
<?php

namespace App\Services\Facturacion\Inputs;

class ItemInput
{
    public static function format($item)
    {
        return (object)[
            'item' => (object)[
                'description' => $item['descripcion'],
                'internal_id' => $item['codigo_interno'] ?? null,
                'item_code' => $item['codigo_producto_sunat'] ?? null,
                'item_code_gs1' => $item['codigo_producto_gsl'] ?? null,
                'unit_type_id' => $item['unidad_de_medida'] ?? 'NIU',
                'amount_plastic_bag_taxes' => $item['total_impuestos_bolsa_plastica'] ?? 0,
            ],

            'quantity' => $item['cantidad'],
            'unit_value' => $item['valor_unitario'],
            'price_type_id' => $item['codigo_tipo_precio'] ?? '01',
            'unit_price' => $item['precio_unitario'],

            'affectation_igv_type_id' => $item['codigo_tipo_afectacion_igv'],
            'total_base_igv' => $item['total_base_igv'],
            'percentage_igv' => $item['porcentaje_igv'] ?? 18,
            'total_igv' => $item['total_igv'],

            'system_isc_type_id' => $item['codigo_tipo_sistema_isc'] ?? null, //
            'total_base_isc' => $item['total_base_isc'] ?? 0, //
            'percentage_isc' => $item['porcentaje_isc'] ?? 0, //
            'total_isc' => $item['total_isc'] ?? 0, //

            'total_base_other_taxes' => $item['total_base_otros_impuestos'] ?? 0, //
            'percentage_other_taxes' => $item['porcentaje_otros_impuestos'] ?? 0, //
            'total_other_taxes' => $item['total_otros_impuestos'] ?? 0, //
            'total_plastic_bag_taxes' => $item['total_impuestos_bolsa_plastica'] ?? 0, //

            'total_taxes' => $item['total_impuestos'],
            'total_value' => $item['total_valor_item'],
            // 'total' => $item['total_item'],

            'attributes' => self::setAttributes($item['datos_adicionales'] ?? []),
            'discounts' => self::setDiscounts($item['descuentos'] ?? []),
            'charges' => self::setCharges($item['cargos'] ?? []),
        ];
    }

    public static function arrayFormat($items)
    {
        return array_map(function ($item) {
            return self::format($item);
        }, $items);
    }

    private static function setAttributes($attributes)
    {
        return array_map(function ($attribut) {
            return (object)[
                'attribute_type_id' => $attribut['codigo'],
                'description' => $attribut['descripcion'],
                'value' => $attribut['valor'] ?? null,
                'start_date' => $attribut['fecha_inicio'] ?? null,
                'end_date' => $attribut['fecha_fin'] ?? null,
                'duration' => $attribut['duracion'] ?? null,
            ];
        }, $attributes);
    }

    private static function setDiscounts($discounts)
    {
        return array_map(function ($discount) {
            return (object)[
                'discount_type_id' => $discount['codigo'],
                'factor' => $discount['factor'],
                'amount' => $discount['monto'],
                'base' =>  $discount['base'],
            ];
        }, $discounts);
    }

    private static function setCharges($charges)
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
}
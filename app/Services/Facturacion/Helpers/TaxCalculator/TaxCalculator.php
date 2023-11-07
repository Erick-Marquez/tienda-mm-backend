<?php

namespace App\Services\Facturacion\Helpers\TaxCalculator;

class TaxCalculator
{

    const PRICE_WITH_VAT = 'INC_IGV';
    const PRICE_WITHOUT_VAT = 'SIN_IGV';

    /**
     * @param string $priceType
     * @param float $globalDiscount
     * @param array $items
     *
     * @return array
     */
    public static function calculateTaxes($priceType, $igv, $globalDiscount, $items)
    {
        $calculatedItems = [];
        $base = 0;
        $total_exportation = 0;
        $total_free = 0;
        $total_taxed = 0;
        $total_unaffected = 0;
        $total_exonerated = 0;
        $total_igv = 0;
        $total_base_isc = 0;
        $total_isc = 0;
        $total_base_other_taxes = 0;
        $total_other_taxes = 0;
        $total_plastic_bag_taxes = 0;
        $total_taxes = 0;
        $total_value = 0;
        $total = 0;
        if ($priceType == self::PRICE_WITH_VAT) {
            foreach ($items as $item) {
                $calculatedItem = self::calculateTaxItem($priceType, $item, $igv);
                array_push($calculatedItems, $calculatedItem);

                switch ($item['codigo_tipo_afectacion_igv']) {
                    case '10':
                        $total_taxed += $calculatedItem['total_base_igv'];
                        $total_igv += $calculatedItem['total_igv'];
                        $total_taxes += $calculatedItem['total_taxes'];
                        $total_value += $calculatedItem['total_base_igv'];
                        $total += $calculatedItem['total_base_igv'] + $calculatedItem['total_igv'];
                        $base += $total_value;
                        break;
                    case '32':
                        $total_free += $calculatedItem['total_base_igv'];
                        $total_igv += $calculatedItem['total_igv'];
                        $total_taxes += $calculatedItem['total_taxes'];
                        $total_value += 0;
                        $total += 0;
                        $base += 0;
                        break;
                    default:
                        # code...
                        break;
                }
            }
            if ($globalDiscount > 0) {
                $total -= $globalDiscount;            
                $total_value = round($total / (1 + $igv), 2);
                $total_taxes = round($total - $total_value, 2);
                $total_taxed = $total_value;
                $total_igv = $total_taxes;
            } else {           
                $total_value = round($total / (1 + $igv), 2);
                $total_taxes = round($total - $total_value, 2);
                $total_taxed = $total_value;
                $total_igv = $total_taxes;
            }

        } elseif ($priceType == self::PRICE_WITHOUT_VAT) {
            // TODO: Without VAT (UseCase)
            # code...
        }

        return [
            'base' => $base,
            'total_exportation' => $total_exportation,
            'total_free' => $total_free,
            'total_taxed' => $total_taxed,
            'total_unaffected' => $total_unaffected,
            'total_exonerated' => $total_exonerated,
            'total_igv' => $total_igv,
            'total_base_isc' => $total_base_isc,
            'total_isc' => $total_isc,
            'total_base_other_taxes' => $total_base_other_taxes,
            'total_other_taxes' => $total_other_taxes,
            'total_plastic_bag_taxes' => $total_plastic_bag_taxes,
            'total_taxes' => $total_taxes,
            'total_value' => $total_value,
            'total' => $total,
            'calculated_items' => $calculatedItems
        ];
    }

    /**
     * @param array $item
     *
     * @return array
     */
    private static function calculateTaxItem($priceType, $item, $igv)
    {
        if ($priceType == self::PRICE_WITH_VAT) {

            switch ($item['codigo_tipo_afectacion_igv']) {
                case '10':
                    $base = round(($item['precio'] * $item['cantidad']) / (1 + $igv), 2);
                    $unit_price = round($item['precio'] - ($item['descuento']/$item['cantidad']), 2);


                    $unit_value = round($item['precio'] / (1 + $igv), 2);
                    $total_value = round(($unit_price * $item['cantidad']) / (1 + $igv), 2);
                    $total_taxes = round(($unit_price * $item['cantidad']) - $total_value, 2);
                    $total_base_igv = $total_value;
                    $total_igv = $total_taxes;
                    break;
                case '32':
                    $base = round(($item['precio'] * $item['cantidad']), 2);
                    $unit_price = round($item['precio'] - ($item['descuento']/$item['cantidad']), 2);


                    $unit_value = 0;
                    $total_value = round(($unit_price * $item['cantidad']), 2);
                    $total_taxes = round(($unit_price * $item['cantidad']) - $total_value, 2);
                    $total_base_igv = $total_value;
                    $total_igv = $total_taxes;
                    break;
                default:
                    # code...
                    break;
            }

        } elseif ($priceType == self::PRICE_WITHOUT_VAT) {
            // TODO: Without VAT (UseCase)
            # code...

        }
        
        return [
            'base' => $base,
            'total_value' => $total_value,
            'unit_price' => $unit_price,
            'total_taxes' => $total_taxes,
            'total_base_igv' => $total_base_igv,
            'total_igv' => $total_igv,
            'unit_value' => $unit_value,
        ];
    }
}
<?php

namespace App\Services\Facturacion\Inputs;

use App\Services\Facturacion\Helpers\String\RemoveSpecialCharacter;

class CustomerInput
{
    public static function format($data)
    {
        return (object)[
            'identity_document_type_id' => $data['codigo_tipo_documento_identidad'],
            'number' => $data['numero_documento'],
            'name' => RemoveSpecialCharacter::replace($data['apellidos_y_nombres_o_razon_social']),
            'trade_name' => RemoveSpecialCharacter::replace($data['nombre_comercial'] ?? null),
            'country_id' => $data['codigo_pais'] ?? null,
            'ubigeo' => $data['ubigeo'] ?? null,
            'address' => RemoveSpecialCharacter::replace($data['direccion'] ?? null),
            'email' => $data['correo_electronico'] ?? null,
            'telephone' => $data['telefono'] ?? null,
        ];
    }
}
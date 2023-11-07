<?php

namespace App\Services\Facturacion\Inputs;

class CompanyInput
{
    public $name;
    public $trade_name;
    public $number;

    public $establishment;

    public $is_demo;

    public $soap_username;
    public $soap_password;
    
    public $certificate;

    public function __construct()
    {
        $this->name = env('COMPANY_NAME', 'GALARZA CRISTOBAL YANNE ARAZELI');
        $this->trade_name = env('COMPANY_TRADE_NAME', 'BODEGA MAMAMIA');
        $this->number = env('COMPANY_RUC', '10157516286');

        $this->is_demo = env('IS_DEMO', true);


        $this->soap_username = env('SOAP_USERNAME', 'MODDATOS');
        $this->soap_password = env('SOAP_PASSWORD', 'moddatos');

        $this->establishment = $this->setEstablishment();

        $this->certificate = env('COMPANY_CERTIFICATE', 'certificate.pem');
    }

    private function setEstablishment()
    {
        return (object)[
            'code' => env('ESTABLISHMENT_CODE', '0000'),

            'urbanization' => null,

            'district_id' => env('ESTABLISHMENT_DISTRICT_ID', '140126'),
            'province_description' => env('ESTABLISHMENT_PROVINCE_DESCRIPTION', 'Lima'),
            'department_description' => env('ESTABLISHMENT_DEPARTMENT_DESCRIPTION', 'Lima'),
            'district_description' => env('ESTABLISHMENT_DISTRICT_DESCRIPTION', 'San Martín de Porres'),
            'address' => env('ESTABLISHMENT_ADDRESS', 'Jr. Capitán Quiñones 300'),

            'country_id' => env('ESTABLISHMENT_COUNTRY_ID', 'PE'),

            'telephone' => env('ESTABLISHMENT_TELEPHONE', '906443937'),
            'email' => env('ESTABLISHMENT_EMAIL', 'bodegasmamamia@gmail.com'),
        ];
    }
}
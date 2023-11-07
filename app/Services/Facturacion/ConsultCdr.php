<?php

namespace App\Services\Facturacion;

use App\Services\Facturacion\WS\Client\WsClient;

class ConsultCdr extends Sunat
{
    public function setData($data) {
        $endpoint = $this::FE_CONSULTA_CDR;
        $this->wsClient = new WsClient($this::FE_CONSULTA_CDR . "?wsdl");
        $this->wsClient->setCredentials($this->company->soap_username, $this->company->soap_password);
        $this->wsClient->setService($endpoint);
    }
}
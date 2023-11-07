<?php

namespace App\Services\Facturacion\WS\Services;

/**
 * Class SunatEndpoints.
 */
final class SunatEndpoints
{
    /**
     *  FACTURACION SERVICES.
     */
    /**
     *  FACTURACION SERVICES.
     */

    const FE_BETA = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl';
    const FE_HOMOLOGACION = 'https://www.sunat.gob.pe/ol-ti-itcpgem-sqa/billService';
    const FE_PRODUCCION = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService?wsdl';
    const FE_PRODUCCION_ALTERNATE = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService?wsdle';
    const FE_CONSULTA_CDR = 'https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService';

    /**
     * GUIA DE REMISION SERVICES.
     */
    const GUIA_BETA = 'https://e-beta.sunat.gob.pe/ol-ti-itemision-guia-gem-beta/billService';
    const GUIA_PRODUCCION = 'https://e-guiaremision.sunat.gob.pe/ol-ti-itemision-guia-gem/billService';

    /**
     * RETENCION Y PERCEPCION SERVICES.
     */
    const RETENCION_BETA = 'https://e-beta.sunat.gob.pe/ol-ti-itemision-otroscpe-gem-beta/billService';
    const RETENCION_PRODUCCION = 'https://e-factura.sunat.gob.pe/ol-ti-itemision-otroscpe-gem/billService';

    /**
     * WSDL Uri.
     */
    const WSDL_ENDPOINT = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl';
    const WSDL_ENDPOINT_PRODUCCION  = 'https://e-factura.sunat.gob.pe/ol-it-wsconsvalidcpe/billValidService?wsdl';
}

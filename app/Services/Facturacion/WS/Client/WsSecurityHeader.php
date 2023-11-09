<?php

namespace App\Services\Facturacion\WS\Client;

use SoapHeader;
use SoapVar;

/**
 * Class WSSESecurityHeader.
 */
class WsSecurityHeader extends SoapHeader
{
    const WSS_NAMESPACE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    public function __construct($username, $password)
    {
        $security = new SoapVar(
            [new SoapVar(
                [
                    new SoapVar($username, XSD_STRING, null, null, 'Username', self::WSS_NAMESPACE),
                    new SoapVar($password, XSD_STRING, null, null, 'Password', self::WSS_NAMESPACE),
                ],
                SOAP_ENC_OBJECT,
                null,
                null,
                'UsernameToken',
                self::WSS_NAMESPACE
            )],
            SOAP_ENC_OBJECT
        );
        parent::__construct(self::WSS_NAMESPACE, 'Security', $security, false);
    }
}

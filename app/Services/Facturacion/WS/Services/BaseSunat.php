<?php

namespace App\Services\Facturacion\WS\Services;

use App\Services\Facturacion\WS\Zip\ZipFileDecompress;
use App\Services\Facturacion\WS\Zip\ZipFly;
use App\Services\Facturacion\WS\Client\WsClient;
use App\Services\Facturacion\WS\Reader\DomCdrReader;
use App\Services\Facturacion\WS\Response\BillResult;
use App\Services\Facturacion\WS\Response\CdrResponse;
use App\Services\Facturacion\WS\Response\Error;
use App\Services\Facturacion\WS\Validator\XmlErrorCodeProvider;

/**
 * Class BaseSunat.
 */
class BaseSunat
{
    const NUMBER_PATTERN = '/[^0-9]+/';

    /**
     * @var ZipFly
     */
    private $compressor;

    /**
     * @var ZipFileDecompress
     */
    private $decompressor;

    /**
     * @var DomCdrReader
     */
    private $cdrReader;

    /**
     * @var WsClient
     */
    private $client;

    /**
     * @var XmlErrorCodeProvider
     */
    private $codeProvider;

    /**
     * BaseSunat constructor.
     */
    public function __construct()
    {
        $this->compressor = new ZipFly();
        $this->decompressor = new ZipFileDecompress();
        $this->cdrReader = new DomCdrReader();
        $this->codeProvider = new XmlErrorCodeProvider();
    }

    /**
     * @return WsClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param WsClient $client
     *
     * @return BaseSunat
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get error from Fault Exception.
     *
     * @param \SoapFault $fault
     *
     * @return Error
     */
    protected function getErrorFromFault(\SoapFault $fault)
    {
        $error = $this->getErrorByCode($fault->faultcode, $fault->faultstring);

        if (empty($error->getMessage())) {
            $error->setMessage(isset($fault->detail) ? $fault->detail->message : $fault->faultstring);
        }

        return $error;
    }

    /**
     * @param string $code
     * @param string $optional Intenta obtener el codigo de este parametro sino $codigo no es válido.
     *
     * @return Error
     */
    protected function getErrorByCode($code, $optional = '')
    {
        $error = new Error();
        $error->setCode($code);
        $code = preg_replace(self::NUMBER_PATTERN, '', $code);
        $message = '';

        if (empty($code) && $optional) {
            $code = preg_replace(self::NUMBER_PATTERN, '', $optional);
        }

        if ($code) {
            $message = $this->getMessageError($code);
            $error->setCode($code);
        }

        return $error->setMessage($message);
    }

    /**
     * @param string $filename
     * @param string $xml
     *
     * @return string
     */
    protected function compress($filename, $xml)
    {
        return $this->compressor->compress($filename, $xml);
    }

    /**
     * @param $zipContent
     *
     * @return CdrResponse
     */
    protected function extractResponse($zipContent)
    {
        $xml = $this->getXmlResponse($zipContent);

        return $this->cdrReader->getCdrResponse($xml);
    }

    /**
     * @param $code
     *
     * @return string
     */
    protected function getMessageError($code)
    {
        if (empty($this->codeProvider)) {
            return '';
        }

        return $this->codeProvider->getValue($code);
    }

    protected function isExceptionCode($code)
    {
        $value = intval($code);

        return $value >= 100 && $value <= 1999;
    }

    protected function loadErrorByCode(BillResult $result, $code)
    {
        $error = $this->getErrorByCode($code);

        if (empty($error->getMessage()) && $result->getCdrResponse()) {
            $error->setMessage($result->getCdrResponse()->getDescription());
        }

        $result
            ->setSuccess(false)
            ->setError($error);
    }

    private function getXmlResponse($content)
    {
        $filter = function ($filename) {
            return 'xml' === strtolower($this->getFileExtension($filename));
        };
        $files = $this->decompressor->decompress($content, $filter);
        $filesCount = is_countable($files) ? count($files) : 0;
        return $filesCount == 0 ? '' : $files[0]['content'];
    }

    private function getFileExtension($filename)
    {
        $lastDotPos = strrpos($filename, '.');
        if (!$lastDotPos) {
            return '';
        }

        return substr($filename, $lastDotPos + 1);
    }
}

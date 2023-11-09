<?php

namespace App\Services\Facturacion;

use App\Services\Facturacion\Exceptions\InternalErrorException;
use App\Services\Facturacion\Exceptions\SunatErrorException;
use App\Services\Facturacion\Helpers\Storage\StorageDocument;
use App\Services\Facturacion\Helpers\Xml\XmlFormat;
use App\Services\Facturacion\Inputs\CompanyInput;
use App\Services\Facturacion\Templates\Template;
use App\Services\Facturacion\WS\Client\WsClient;
use App\Services\Facturacion\WS\Services\ConsultCdrService;
use App\Services\Facturacion\WS\Services\ExtService;
use App\Services\Facturacion\WS\Signed\XmlSigned;
use Carbon\Carbon;
use Exception;

abstract class Sunat
{
    protected $company;

    protected $pathCertificate;

    protected $signer;
    protected $wsClient;
    protected $sender;

    protected $response;

    protected $type;
    protected $document;
    protected $filename;
    protected $path;

    protected $xmlUnsigned;
    protected $xmlSigned;


    const FE_BETA = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
    const FE_PRODUCCION = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';
    const FE_CONSULTA_CDR = 'https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService';

    const ACCEPTED = 'ACEPTADO';  //* A                           
    const REJECTED = 'RECHAZADO';  //* R                         
    const INTERNAL_ERROR = 'ERROR INTERNO';  //* IE             
    const SUNAT_ERROR = 'ERROR SUNAT';  //* SE                   
    const PENDING_TICKET = 'PENDIENTE CONSULTA TICKET';  //* PT 
    const PENDING = 'PENDIENTE';  //* P
    const PENDING_SUMMARY = 'PENDIENTE ENVIO RESUMEN';  //* PS
    
    const ACCEPTED_CODE = 'A';
    const REJECTED_CODE = 'R';
    const INTERNAL_ERROR_CODE = 'IE';
    const SUNAT_ERROR_CODE = 'SE';
    const PENDING_TICKET_CODE = 'PT';
    const PENDING_CODE = 'P';
    const PENDING_SUMMARY_CODE = 'PS';


    public function __construct()
    {
        $this->company = new CompanyInput();

        $this->signer = new XmlSigned();
        $this->wsClient = new WsClient();


        if ($this->company->is_demo) {
            $soapUsername = $this->company->number . 'MODDATOS';
            $soapPassword = 'moddatos';
        } else {
            $soapUsername = $this->company->soap_username;
            $soapPassword = $this->company->soap_password;
        }

        $endpoint = ($this->company->is_demo) ? $this::FE_BETA : $this::FE_PRODUCCION;

        $this->wsClient->setCredentials($soapUsername, $soapPassword);
        $this->wsClient->setService($endpoint);

        $this->setPathCertificate();
    }

    private function setPathCertificate()
    {
        if ($this->company->is_demo) {
            $this->pathCertificate = app_path('Services' . DIRECTORY_SEPARATOR .
                'Facturacion' . DIRECTORY_SEPARATOR .
                'WS' . DIRECTORY_SEPARATOR .
                'Signed' . DIRECTORY_SEPARATOR .
                'Resources' . DIRECTORY_SEPARATOR .
                'certificate.pem');
        } else {
            $this->pathCertificate = storage_path('app' . DIRECTORY_SEPARATOR .
                'certificates' . DIRECTORY_SEPARATOR . $this->company->certificate);
        }
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function sendDocumentToSunat($data)
    {
        try {
            $this->setData($data);
            $this->createXmlUnsigned();
            $this->signXmlUnsigned();
            $this->senderXmlSignedBill();
        } catch (InternalErrorException $ex) {
            $this->response = [
                'sent' => false,
                'code' => $ex->getCode(),
                'state_code' => $this::INTERNAL_ERROR_CODE,
                'state' => $this::INTERNAL_ERROR,
                'description' => $ex->getMessage(),
                'notes' => []
            ];
        } catch (SunatErrorException $ex) {
            $this->response = [
                'sent' => true,
                'code' => $ex->getCode(),
                'state_code' => $this::REJECTED_CODE,
                'state' => $this::REJECTED,
                'description' => $ex->getMessage(),
                'notes' => []
            ];
        } catch (Exception $ex) {
            $this->response = [
                'sent' => false,
                'code' => $ex->getCode(),
                'state_code' => $this::INTERNAL_ERROR_CODE,
                'state' => $this::INTERNAL_ERROR,
                'description' => $ex->getMessage(),
                'notes' => []
            ];
        }
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function sendSummaryToSunat($data)
    {
        try {
            $this->setData($data);
            $this->createXmlUnsigned();
            $this->signXmlUnsigned();
            $this->senderXmlSignedSummary();
        } catch (InternalErrorException $ex) {
            $this->response = [
                'sent' => false,
                'code' => $ex->getCode(),
                'state_code' => $this::INTERNAL_ERROR_CODE,
                'state' => $this::INTERNAL_ERROR,
                'description' => $ex->getMessage(),
                'ticket' => null,
                'notes' => []
            ];
        } catch (SunatErrorException $ex) {
            $this->response = [
                'sent' => true,
                'code' => $ex->getCode(),
                'state_code' => $this::REJECTED_CODE,
                'state' => $this::REJECTED,
                'description' => $ex->getMessage(),
                'ticket' => null,
                'notes' => []
            ];
        } catch (Exception $ex) {
            $this->response = [
                'sent' => false,
                'code' => $ex->getCode(),
                'state_code' => $this::INTERNAL_ERROR_CODE,
                'state' => $this::INTERNAL_ERROR,
                'description' => $ex->getMessage(),
                'ticket' => null,
                'notes' => []
            ];
        }
    }

    /**
     * @param string $ticket
     * @param string $dateOfIssue
     *
     * @return void
     */
    public function sendTicketToSunat($ticket, $dateOfIssue)
    {
        try {
            $extService = new ExtService();
            $extService->setClient($this->wsClient);
            $res = $extService->getStatus($ticket);
            if ($res->isSuccess()) {
                $cdrResponse = $res->getCdrResponse();

                $code = $cdrResponse->getCode();
                $id = $cdrResponse->getId();
                $description = $cdrResponse->getDescription();
                $notes = $cdrResponse->getNotes();

                $this->path = Carbon::parse($dateOfIssue)->format('Y/m/');
                $this->filename = $this->company->number.'-'.$id;

                StorageDocument::uploadStorage($this->path.'cdr', 'R-'.$this->filename, 'zip', $res->getCdrZip());

                if ((int)$code === 0) {
                    $this->response = [
                        'sent' => true,
                        'code' => $code,
                        'state_code' => $this::ACCEPTED_CODE,
                        'state' => $this::ACCEPTED,
                        'description' => $description,
                        'notes' => $notes,
                        'path_xml' => $this->path.'signed/'. $this->filename. '.xml',
                        'path_cdr' => $this->path.'cdr/R-'. $this->filename. '.zip',
                        'filename' => $this->filename
                    ];
                } else {
                    $this->response = [
                        'sent' => true,
                        'code' => $code,
                        'state_code' => $this::REJECTED_CODE,
                        'state' => $this::REJECTED,
                        'description' => $description,
                        'notes' => $notes,
                        'path_xml' => $this->path.'signed/'. $this->filename. '.xml',
                        'path_cdr' => $this->path.'cdr/R-'. $this->filename. '.zip',
                        'filename' => $this->filename
                    ];
                }
            } else {
                $code = $res->getError()->getCode();
                $message = $res->getError()->getMessage();
                throw new SunatErrorException('Code: '.$code.'; Description: '.$message, $code === 'HTTP' ? 500 : $code);
            }
        } catch (InternalErrorException $ex) {
            $this->response = [
                'sent' => false,
                'code' => $ex->getCode(),
                'state_code' => $this::INTERNAL_ERROR_CODE,
                'state' => $this::INTERNAL_ERROR,
                'description' => $ex->getMessage(),
                'ticket' => $ticket,
                'notes' => []
            ];
        } catch (SunatErrorException $ex) {
            $this->response = [
                'sent' => true,
                'code' => $ex->getCode(),
                'state_code' => $this::REJECTED_CODE,
                'state' => $this::REJECTED,
                'description' => $ex->getMessage(),
                'ticket' => $ticket,
                'notes' => []
            ];
        } catch (Exception $ex) {
            $this->response = [
                'sent' => false,
                'code' => $ex->getCode(),
                'state_code' => $this::INTERNAL_ERROR_CODE,
                'state' => $this::INTERNAL_ERROR,
                'description' => $ex->getMessage(),
                'ticket' => $ticket,
                'notes' => []
            ];
        }
    }

    /**
     * @param string $ticket
     * @param string $dateOfIssue
     *
     * @return void
     */
    public function consultCdrStatus($dateOfIssue, $type, $serie, $number)
    {
        $this->setData([]);
        try {
            $consultCdrService = new ConsultCdrService();
            $consultCdrService->setClient($this->wsClient);
            $res = $consultCdrService->getStatusCdr($this->company->number, $type, $serie, $number);
            if ($res->isSuccess()) {
                $cdrResponse = $res->getCdrResponse();

                $code = $cdrResponse->getCode();
                $description = $cdrResponse->getDescription();
                $notes = $cdrResponse->getNotes();

                $this->path = Carbon::parse($dateOfIssue)->format('Y/m/');
                $this->filename = join('-', [$this->company->number, $type, $serie, $number]);

                StorageDocument::uploadStorage($this->path.'cdr', 'R-'.$this->filename, 'zip', $res->getCdrZip());

                if ((int)$code === 0) {
                    $this->response = [
                        'sent' => true,
                        'code' => $code,
                        'state_code' => $this::ACCEPTED_CODE,
                        'state' => $this::ACCEPTED,
                        'description' => $description,
                        'notes' => $notes,
                        'path_xml' => $this->path.'signed/'. $this->filename. '.xml',
                        'path_cdr' => $this->path.'cdr/R-'. $this->filename. '.zip',
                        'filename' => $this->filename
                    ];
                } else {
                    $this->response = [
                        'sent' => true,
                        'code' => $code,
                        'state_code' => $this::REJECTED_CODE,
                        'state' => $this::REJECTED,
                        'description' => $description,
                        'notes' => $notes,
                        'path_xml' => $this->path.'signed/'. $this->filename. '.xml',
                        'path_cdr' => $this->path.'cdr/R-'. $this->filename. '.zip',
                        'filename' => $this->filename
                    ];
                }
            } else {
                $code = $res->getError()->getCode();
                $message = $res->getError()->getMessage();
                throw new SunatErrorException('Code: '.$code.'; Description: '.$message, $code === 'HTTP' ? 500 : $code);
            }
        } catch (InternalErrorException $ex) {
            $this->response = [
                'sent' => false,
                'code' => $ex->getCode(),
                'state_code' => $this::INTERNAL_ERROR_CODE,
                'state' => $this::INTERNAL_ERROR,
                'description' => $ex->getMessage(),
                'notes' => []
            ];
        } catch (SunatErrorException $ex) {
            $this->response = [
                'sent' => true,
                'code' => $ex->getCode(),
                'state_code' => $this::REJECTED_CODE,
                'state' => $this::REJECTED,
                'description' => $ex->getMessage(),
                'notes' => []
            ];
        } catch (Exception $ex) {
            $this->response = [
                'sent' => false,
                'code' => $ex->getCode(),
                'state_code' => $this::INTERNAL_ERROR_CODE,
                'state' => $this::INTERNAL_ERROR,
                'description' => $ex->getMessage(),
                'notes' => []
            ];
        }
    }

    /**
     * 
     * @return void
     * 
     * @throws InternalErrorException
     */
    private function createXmlUnsigned()
    {
        try {
            $this->xmlUnsigned = XmlFormat::format(Template::xml($this->type, $this->company, $this->document));
            StorageDocument::uploadStorage($this->path.'unsigned', $this->filename, 'xml', $this->xmlUnsigned);
        } catch (\Exception $ex) {
            throw new InternalErrorException($ex->getMessage().' in file '.$ex->getFile().' on line '.$ex->getLine());
        }
    }

    /**
     * 
     * @return void
     * 
     * @throws InternalErrorException
     */
    private function signXmlUnsigned()
    {
        try {
            $this->signer->setCertificateFromFile($this->pathCertificate);
            $this->xmlSigned = $this->signer->signXml($this->xmlUnsigned);
            StorageDocument::uploadStorage($this->path.'signed', $this->filename, 'xml', $this->xmlSigned);
        } catch (\Exception $ex) {
            throw new InternalErrorException($ex->getMessage().' in file '.$ex->getFile().' on line '.$ex->getLine());
        }
    }

    private function senderXmlSigned()
    {
        $this->sender->setClient($this->wsClient);
        return $this->sender->send($this->filename, $this->xmlSigned);
    }

    private function senderXmlSignedBill()
    {
        $res = $this->senderXmlSigned();

        if ($res->isSuccess()) {
            $cdrResponse = $res->getCdrResponse();

            StorageDocument::uploadStorage($this->path.'cdr', 'R-'.$this->filename, 'zip', $res->getCdrZip());

            $code = $cdrResponse->getCode();
            $description = $cdrResponse->getDescription();
            $notes = $cdrResponse->getNotes();

            if ($code === 'ERROR_CDR') {
                throw new SunatErrorException('Code: '.$code.'; Description: '.$description, 500);
            }
            if ($code === 'HTTP') {
                throw new SunatErrorException('Code: '.$code.'; Description: '.$description, 500);
            }
            if ((int)$code === 0) {
                $this->response = [
                    'sent' => true,
                    'code' => $code,
                    'state_code' => $this::ACCEPTED_CODE,
                    'state' => $this::ACCEPTED,
                    'description' => $description,
                    'notes' => $notes,
                    'path_xml' => $this->path.'signed/'. $this->filename. '.xml',
                    'path_cdr' => $this->path.'cdr/R-'. $this->filename. '.zip',
                    'filename' => $this->filename
                ];
            } else {
                $this->response = [
                    'sent' => true,
                    'code' => $code,
                    'state_code' => $this::REJECTED_CODE,
                    'state' => $this::REJECTED,
                    'description' => $description,
                    'notes' => $notes,
                    'path_xml' => $this->path.'signed/'. $this->filename. '.xml',
                    'path_cdr' => $this->path.'cdr/'. $this->filename. '.zip',
                    'filename' => $this->filename
                ];
            }

        } else {
            $code = $res->getError()->getCode();
            $message = $res->getError()->getMessage();
            throw new SunatErrorException('Code: '.$code.'; Description: '.$message, $code === 'HTTP' ? 500 : $code);
        }
    }

    public function senderXmlSignedSummary()
    {
        $res = $this->senderXmlSigned();
        if ($res->isSuccess()) {
            $ticket = $res->getTicket();
            $this->response = [
                'sent' => true,
                'code' => null,
                'state_code' => $this::PENDING_TICKET_CODE,
                'state' => $this::PENDING_TICKET,
                'description' => 'Ticket Pendiente',
                'ticket' => $ticket,
                'notes' => [],
                'path_xml' => $this->path.'signed/'. $this->filename. '.xml',
                'filename' => $this->filename
            ];
        } else {
            $code = $res->getError()->getCode();
            $message = $res->getError()->getMessage();
            throw new SunatErrorException('Code: '.$code.'; Description: '.$message, $code === 'HTTP' ? 500 : $code);
        }
    }

    /**
     * @param array $data
     *
     * @return void
     * 
     * @throws InternalErrorException
     */
    abstract public function setData($data);
}
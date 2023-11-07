<?php

namespace App\Services\Facturacion;

use App\Services\Facturacion\Exceptions\InternalErrorException;
use App\Services\Facturacion\Inputs\InvoiceInput;
use App\Services\Facturacion\WS\Services\BillSender;
use Carbon\Carbon;

class Invoice extends Sunat
{
    /**
     * @param array $data
     *
     * @return void
     * 
     * @throws InternalErrorException
     */
    public function setData($data)
    {
        try {
            $this->type = 'invoice';
            $this->document = InvoiceInput::format($data);
            $this->path = Carbon::parse($this->document->date_of_issue)->format('Y/m/');
            $this->filename = join('-', [$this->company->number, $this->document->document_type_id, $this->document->series, $this->document->number]);
            $this->sender = new BillSender();
        } catch (\Exception $ex) {
            throw new InternalErrorException($ex->getMessage().' in file '.$ex->getFile().' on line '.$ex->getLine(), 0, $ex);
        }
    }
}
<?php

namespace App\Services\Facturacion;

use App\Services\Facturacion\Exceptions\InternalErrorException;
use App\Services\Facturacion\Inputs\SummaryInput;
use App\Services\Facturacion\WS\Services\SummarySender;
use Carbon\Carbon;

class Summary extends Sunat
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
            $this->type = 'summary';
            $this->document = SummaryInput::format($data);
            $this->path = Carbon::parse($this->document->date_of_issue)->format('Y/m/');
            $this->filename = join('-', [$this->company->number, $this->document->identifier]);
            $this->sender = new SummarySender();
        } catch (\Exception $ex) {
            throw new InternalErrorException($ex->getMessage().' in file '.$ex->getFile().' on line '.$ex->getLine());
        }
    }
}
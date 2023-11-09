<?php

namespace App\Services\Facturacion\Templates;

class Template
{
    static function xml($template, $company, $document)
    {
        view()->addLocation(__DIR__.'/xml');
        return view($template, compact('company', 'document'))->render();
    }
}
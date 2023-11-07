<?php

namespace App\Services\Facturacion\Helpers\String;

class RemoveSpecialCharacter
{
    const SPECIAL_CHARACTER = [
        'á' => 'a',
        'Á' => 'A',
        'ä' => 'a',
        'Ä' => 'A',
        'à' => 'a',
        'À' => 'A',
        'â' => 'a',
        'Â' => 'A',
        'å' => 'a',
        'Å' => 'A',
        


        'é' => 'e',
        'É' => 'E',
        'ë' => 'e',
        'Ë' => 'E',
        'è' => 'e',
        'È' => 'E',
        'ê' => 'e',
        'Ê' => 'E',

        'í' => 'i',
        'Í' => 'I',
        'ï' => 'i',
        'Ï' => 'I',
        'ì' => 'i',
        'Ì' => 'I',
        'î' => 'i',
        'Î' => 'I',

        'ó' => 'o',
        'Ó' => 'O',
        'ö' => 'o',
        'Ö' => 'O',
        'ò' => 'o',
        'Ò' => 'O',
        'ô' => 'o',
        'Ô' => 'O',

        'ú' => 'u',
        'Ú' => 'U',
        'ü' => 'u',
        'Ü' => 'U',
        'ù' => 'u',
        'Ù' => 'U',
        'û' => 'u',
        'Û' => 'U',

        'ñ' => 'n',
        'Ñ' => 'N',

        '.' => ' ',
        ',' => ' ',
        '-' => ' ',
        '+' => ' ',
        '/' => ' ',
    ];

    public static function replace($string)
    {
        [ $arraySearch, $arrayReplace ] = self::getArrayToReplace();
        return str_replace($arraySearch, $arrayReplace, trim($string));
    }

    private static function getArrayToReplace()
    {
        $arraySearch = [];
        $arrayReplace = [];
        foreach (self::SPECIAL_CHARACTER as $key => $value) {
            $arraySearch[] = $key;
            $arrayReplace[] = $value;
        }
        return [ $arraySearch, $arrayReplace ];
    }
}
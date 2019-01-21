<?php

namespace App\Helpers\Http;

use Artisaninweb\SoapWrapper\SoapWrapper;

class SoapRequest
{

    private static $client;

    public static function i()
    {
        if (!(self::$client instanceof SoapWrapper)) {

            self::$client = new SoapWrapper;
        }

        return self::$client;
    }

}

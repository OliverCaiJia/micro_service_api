<?php

namespace App\Helpers\Http;

use \GuzzleHttp\Client;

class HttpClient
{

    private static $client;
    private static $config = [
        'connect_timeout' => 30,
        'timeout' => 30
    ];

    public static function i($config = [])
    {
        if (!(self::$client instanceof Client))
        {
            $config = array_merge(self::$config, $config);
            self::$client = new Client($config);
        }

        return self::$client;
    }

}

<?php

namespace App\Services\Core\Tools;

use App\Services\AppService;

class ToolsService extends AppService
{
    public static $services;
    public static function i()
    {

        if (!(self::$services instanceof static))
        {
            self::$services = new static();
        }

        return self::$services;
    }

}



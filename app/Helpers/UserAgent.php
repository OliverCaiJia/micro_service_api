<?php

namespace App\Helpers;

use Jenssegers\Agent\Agent;

class UserAgent
{

    private static $ua;


    public static function i()
    {
        if (!(self::$ua instanceof Agent))
        {
            self::$ua = new Agent();
        }

        return self::$ua;
    }

}

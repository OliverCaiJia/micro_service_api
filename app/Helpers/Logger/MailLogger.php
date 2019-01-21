<?php

namespace App\Helpers\Logger;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Processor\WebProcessor;

class MailLogger
{

    private static $nativeMailer;

    /*
     * NativeMailer
     */

    public static function getMailer()
    {
        if (!(self::$nativeMailer instanceof MonologLogger)) {
            self::$nativeMailer = new MonologLogger('sdzj');
            self::$nativeMailer->pushHandler(self::getNativeMailerHandler());
            self::$nativeMailer->pushProcessor(new WebProcessor());
        }
        return self::$nativeMailer;
    }

    private static function getNativeMailerHandler()
    {
        $handler = new NativeMailerHandler('tech-report@sudaizhijia.com', 'SDZJ-Log', 'tech-report@sudaizhijia.com');
        return $handler;
    }

}

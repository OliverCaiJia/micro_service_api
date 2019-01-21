<?php

namespace App\Helpers\Logger;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\WebProcessor;

class SLogger
{

    private static $streamLogger;

    public static function getStream()
    {
        if (!(self::$streamLogger instanceof MonologLogger))
        {
            $extraFields = [
                'url' => 'REQUEST_URI',
                'ip' => 'REMOTE_ADDR',
                'http_method' => 'REQUEST_METHOD',
                'server' => 'SERVER_NAME',
                'referrer' => 'HTTP_REFERER',
                'ua' => 'HTTP_USER_AGENT',
                'query'=> 'QUERY_STRING',
                'ser_ip' => 'SERVER_ADDR'
            ];
            self::$streamLogger = new MonologLogger('sdzj');
            self::$streamLogger->pushHandler(self::getStreamHandler());
            self::$streamLogger->pushProcessor(new WebProcessor(null, $extraFields));
            self::$streamLogger->setTimezone(new \DateTimeZone('PRC'));
        }
        return self::$streamLogger;
    }

    private static function getStreamHandler()
    {
        $logpath = storage_path() . '/logs/api.sudaizhijia-' . date('Y-m-d') . '.log';
        $handler = new StreamHandler($logpath, MonologLogger::INFO, true, 0777);
        return $handler;
    }

}

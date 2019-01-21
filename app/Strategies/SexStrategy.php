<?php
namespace  App\Strategies;

use App\Helpers\DateUtils;
use App\Strategies\AppStrategy;

/**
 * Class SexStrategy
 * @package App\Strategies
 * 性别公共策略
 */
class SexStrategy extends AppStrategy
{
    public static function intToStr($sex = null)
    {
        $i = DateUtils::toInt($sex);
        if($i == 0) return '男';
        elseif($i == 1) return '女';
        else return '';
    }
    public static function strToInt($str)
    {
        $s = trim($str);
        if($s == '男') return 0;
        elseif($s == '女') return 1;
        else return 0;
    }
    public static function getKVOption()
    {
        return [
            0 => '男',
            1 => '女',
        ];
    }
    public static function getArrayOption()
    {
        return [
            ['int' => 0, 'str' => '男'],
            ['int' => 1, 'str' => '女'],
        ];
    }
}
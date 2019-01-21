<?php

namespace App\Services\Core\Oneloan\Youli\YouliConfig;

use Illuminate\Support\Facades\Log;

class YouliConfig
{
    //前置机地址
    const URL = PRODUCTION_ENV ? 'https://insurance.youzhuanhua.com' : 'http://insurance.beta.youzhuanhua.com/index/index';

    //渠道号
    const CHANNEL_ID = 'dsfsaZc';

    /**
     * 获取参数
     *
     * @param $params
     * @return array
     */
    public static function getParams($params)
    {
        $income = self::getSalary($params);
        $ishouse = 2;
        $iscar = 2;
        if(isset($params['house_info']) && $params['house_info'] != '000')
        {
            $ishouse = 1;
        }

        if(isset($params['car_info']) && $params['car_info'] != '000')
        {
            $iscar = 1;
        }

        return [
            'name' => isset($params['name']) ? $params['name'] : '',
            'mobile' => isset($params['mobile']) ? $params['mobile'] : '',
            'idCard' => isset($params['certificate_no']) ? $params['certificate_no'] : '',
            'loanAmount' => isset($params['money']) ? $params['money'] : '',
            'income' => $income,
            'ishouse' => $ishouse,
            'iscar' => $iscar,
        ];
    }

    /**
     * 收入
     *
     * @param $params
     * @return string
     */
    public static function getSalary($params)
    {
        switch ($params['salary'])
        {
            case '001':
                $income = '2000';
                break;
            case '002':
                $income = '3500';
                break;
            case '003':
                $income = '7500';
                break;
            case '004':
                $income = '10000';
                break;
            case '101':
                $income = '2000';
                break;
            case '102':
                $income = '2500';
                break;
            case '103':
                $income = '3500';
                break;
            case '104':
                $income = '4500';
                break;
            case '105':
                $income = '7500';
                break;
            case '106':
                $income = '10000';
                break;
            default:
                $income = '2000';
        }

        return $income;
    }

}
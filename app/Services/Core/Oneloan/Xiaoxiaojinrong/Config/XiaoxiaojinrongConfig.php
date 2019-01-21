<?php

namespace App\Services\Core\Oneloan\Xiaoxiaojinrong\Config;

class XiaoxiaojinrongConfig
{
    // 正式环境地址
    const URL = 'https://www.ssddw.com/cooper/org/thirdData/';
    // 测试环境地址
    const UAT_URL = 'http://330114c5.nat123.cc/cooper/org/thirdData/';
    // 商户号
    const CODE = 'dfs10170';
    //渠道号
    const CHANNEL_NUM = 'sdr';
    //第二版渠道号
    const CHANNEL_NUM_B = 'sereg';


    /**
     * 获取请求地址
     * @return string
     */
    public static function getUrl()
    {
        return PRODUCTION_ENV ? static::URL . static::CHANNEL_NUM : static::UAT_URL . static::CHANNEL_NUM;
    }

    /**
     * 第二版地址
     * @return string
     */
    public static function getUrlByChannelNumB()
    {
        return PRODUCTION_ENV ? static::URL . static::CHANNEL_NUM_B : static::UAT_URL . static::CHANNEL_NUM_B;
    }

    /**
     * 获取毫秒时间戳
     *
     * @return string
     */
    public static function getMillionTime()
    {
        //获取毫秒时间
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec * 1000);
        $millisecond = str_pad($msec, 3, '0', STR_PAD_RIGHT);
        $milliTime = date("YmdHis") . $millisecond;

        return $milliTime;
    }

    /**
     * 获取签名
     * @param string $mobile
     * @param $code
     * @return string
     */
    public static function getSign($mobile = '', $code)
    {
        $milliTime = XiaoxiaojinrongConfig::getMillionTime();

        return md5($mobile . '&' . $milliTime . $code);
    }

    /**
     * 获取车产情况
     * @param $type
     * @return int
     */
    public static function getCarType($type = '')
    {
        if ($type == '000') {
            //无车
            return 2;
        } elseif ($type == '001') {
            // 有车贷
            return 3;
        } elseif ($type == '002') {
            // 无车贷
            return 4;
        }
    }

    /**
     * 获取工资发放方式
     * @param $type
     * @return int
     */
    public static function getSalaryExtend($type = '')
    {
        if ($type == '001') {
            return 1;
        } elseif ($type == '002') {
            return 2;
        }
    }


    /**
     * 对返回结果进行处理
     *
     * @param string $code
     * @return mixed|string
     */
    public static function getMessage($code = '')
    {
        $msgArr = [
            '000' => '接收成功',
            '001' => '缺少必要参数',
            '002' => '签名有误',
            '003' => '申请重复',
            '004' => '接收异常',
            '008' => '未找到对应的城市',
        ];

        return isset($msgArr[$code]) ? $msgArr[$code] : '无错误信息';
    }
}


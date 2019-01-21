<?php

namespace App\Services\Core\Oneloan\Hengchang\Hengyidai\HengyidaiConfig;

/**
 *  恒昌配置
 */
class HengyidaiConfig
{
    //地址
    const URL = PRODUCTION_ENV ? 'http://crm.credithc.com' : 'http://118.26.10.57:8086';
    //用户名
    const USERNAME = 'yx';
    //密码
    const PASSWORD = '3uUwG3WVCHMx';
    //渠道号
    const QUDAOHAO = 28;
    //加密key
    const JPASSWORD = 'Zr6QWNZCcBXV';
    //code
    const HENGYIDAI_CODE = 'Y_001';

    const PAD_METHOD = 'pkcs5';

    protected static function pad_or_unpad($str, $ext)
    {
        if (is_null(self::PAD_METHOD)) {
            return $str;
        } else {
            $func_name = __CLASS__ . '::' . self::PAD_METHOD . '_' . $ext . 'pad';
            if (is_callable($func_name)) {
                $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
                return call_user_func($func_name, $str, $size);
            }
        }
        return $str;
    }

    protected static function pad($str)
    {
        return self::pad_or_unpad($str, '');
    }

    protected static function unpad($str)
    {
        return self::pad_or_unpad($str, 'un');
    }

    /**
     * 加密
     *
     * @param $str
     * @return string
     */
    public static function encrypt($str)
    {
        $str = self::pad($str);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');

        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

        mcrypt_generic_init($td, self::JPASSWORD, $iv);
        $cyper_text = mcrypt_generic($td, $str);
        $rt = base64_encode($cyper_text);
        //$rt = bin2hex($cyper_text);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $rt;
    }

    /**
     * 解密
     *
     * @param $str
     * @return mixed
     */
    public static function decrypt($str)
    {
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');

        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

        mcrypt_generic_init($td, self::JPASSWORD, $iv);
        //$decrypted_text = mdecrypt_generic($td, self::hex2bin($str));
        $decrypted_text = mdecrypt_generic($td, base64_decode($str));
        $rt = $decrypted_text;
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return self::unpad($rt);
    }

    public static function hex2bin($hexdata)
    {
        $bindata = '';
        $length = strlen($hexdata);
        for ($i = 0; $i < $length; $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }
        return $bindata;
    }

    public static function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public static function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text))
            return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;
        return substr($text, 0, -1 * $pad);
    }

    /**
     * 房产信息, 000无房, 001有房贷, 002无房贷
     * @param array $params
     * @return string
     */
    public static function formatHouseInfo($params = [])
    {
        if ('000' == $params['house_info']) {
            $houseInfo = '无';
        } else {
            $houseInfo = '有';
        }

        return $houseInfo;
    }

    /**
     * 月收入范围, 001:2000以下，002:2000-5000,003:5000-1万，004：1万以上
     * @param array $params
     * @return int
     */
    public static function formatSalary($params = [])
    {
        switch ($params['salary']) {
            case '001':
                $salaryVal = 2000;
                break;
            case '002':
                $salaryVal = bcdiv(bcadd(2000, 5000), 2);
                break;
            case '003':
                $salaryVal = bcdiv(bcadd(5000, 10000), 2);
                break;
            case '004':
                $salaryVal = 10000;
                break;
            default:
                $salaryVal = 0;
        }

        return intval($salaryVal);
    }

    /**
     * 职业, 001上班族, 002公务员, 003私营业主
     * @param array $params
     * @return string
     */
    public static function formatOccupation($params = [])
    {
        switch ($params['occupation']) {
            case '001':
                $occupationVal = '上班族';
                break;
            case '002':
                $occupationVal = '公务员';
                break;
            case '003':
                $occupationVal = '私营业主';
                break;
            default:
                $occupationVal = '';
        }

        return $occupationVal;
    }

    /**
     * 工作单位时间
     * 工作时间, 001 6个月内, 002 12个月内, 003 1年以上
     * @param $params
     * @return int
     */
    public static function formatWorkHours($params = [])
    {
        switch ($params['work_hours']) {
            case '001':
                $workingage = 2;
                break;
            case '002':
                $workingage = 4;
                break;
            case '003':
                $workingage = 16;
                break;
            default:
                $workingage = 8;
        }

        return $workingage;
    }

    /**
     * 跟人身份
     *
     * @param array $params
     * @return int
     */
    public static function identity($params = [])
    {
        switch ($params['occupation']) {
            case '001':
                $identity = 4;
                break;
            case '003':
                $identity = 2;
                break;
            default:
                $identity = 8;
        }

        return $identity;
    }

    /**
     * 社保公积金情况
     *
     * @param array $params
     * @return int
     */
    public static function fund($params = [])
    {
        if ($params['accumulation_fund'] != '000' && $params['social_security'] == 1) //有社保有公积金
        {
            $socialsecurityfund = 2;
        } elseif ($params['accumulation_fund'] == '000' && $params['social_security'] == 1) { //有社保无公积金
            $socialsecurityfund = 4;
        } elseif ($params['accumulation_fund'] != '000' && $params['social_security'] == 0) { //无社保有公积金
            $socialsecurityfund = 8;
        } else { //无社保无公积金
            $socialsecurityfund = 1;
        }

        return $socialsecurityfund;
    }

    /**
     * 处理返回信息
     *
     * @param $responseCode
     * @return string
     */
    public static function getMessage($responseCode)
    {
        switch ($responseCode)
        {
            case '0':
                $message = '调用成功';
                break;
            case '-1':
                $message = '用户名/密码错误';
                break;
            case '-2':
                $message = 'IP受限';
                break;
            case '-3':
                $message = '参数为空或格式错误';
                break;
            case '-4':
                $message = '数据重复发送';
                break;
            case '-5':
                $message = '与系统数据重复';
                break;
            case '-100':
                $message = '服务器错误';
                break;
            default:
                $message = '未知';
        }

        return $message;
    }
}

<?php

namespace App\Services\Core\Oneloan\Houbenjinrong\HoubenjinrongConfig;

use Illuminate\Support\Facades\Log;

class HoubenjinrongConfig
{
    //加密的key
    const ENCRYPT_KEY = 'yhdjZGfcsd56HFyc3R1ldx4';

    //前置机地址
    const FRONT_URL = PRODUCTION_ENV ? 'http://221.133.24.6:38080' : 'http://116.23.184.237:8851';

    //电销服务器地址
    const TELE_URL = PRODUCTION_ENV ? 'http://10.10.82.100' : 'http://192.168.13.2';

    //供应商ID 4=>8
    const SUPPLIER_ID = '4';

    /**
     * 加密方法
     *
     * @param string $input 输入数据
     * @param int $base64encode 是否base64加密输出结果 1是 0否
     * @return string
     */
    public static function encrypt($input, $base64encode = 1)
    {//数据加密
        $size = mcrypt_get_block_size(MCRYPT_BLOWFISH, 'ecb'); //$this->mode  8
        $input = self::pkcs5_pad($input, $size);
        $key = str_pad(base64_decode(self::ENCRYPT_KEY), 24, '0');

        $td = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        if ($base64encode) {
            $data = base64_encode($data);
        }

        return $data;
    }

    /**
     * 解密方法
     * @param string $encrypted 要解密的数据
     * @param int $base64decode 是否先base64解码
     * @return bool|string
     */
    public static function decrypt($encrypted, $base64decode = 1)
    {//数据解密
        if ($base64decode) {
            $encrypted = base64_decode($encrypted);
        }
        $key = str_pad(base64_decode(self::ENCRYPT_KEY), 24, '0');
        $td = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

        $ks = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, $encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $y = self::pkcs5_unpad($decrypted);

        return $y;
    }

    public static function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public static function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    public static function PaddingPKCS7($data)
    {
        $block_size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);
        return $data;
    }

    /**
     * 整理参数
     *
     * @param array $params
     * @return array
     */
    public static function getParams($params = [])
    {
        $professionType = self::professionType($params);
        $incomeRange = self::incomeRange($params);
        $arr = [
            'name' => $params['name'],
            'mobile' => $params['mobile'],
            'birthday' => $params['birthday'],
            'cityCode' => $params['city_code'],
            'cardNo' => $params['certificate_no'],
            'professionType' => $professionType,
            'incomeRange' => $incomeRange,
            'incomeType' => ($params['salary_extend'] == '001') ? '1' : '2',
            'isWelfare' => ($params['social_security'] == 1) ? '1' : '2',
            'isHousingFund' => ($params['accumulation_fund'] == '000') ? '2' : '1',
            'isHasCar' => ($params['car_info'] == '001' || $params['car_info'] == '002') ? '1' : '2',
            'isCarLoan' => ($params['car_info'] == '001') ? '1' : '2',
            'isHasHouse' => ($params['house_info'] == '000') ? '2' : '1',
            'isInsurance' => ($params['has_insurance'] > 0) ? '1' : '2',
            'custSex' => ($params['sex'] == 1) ? '01' : '02',
            'isAtom' => (isset($params['is_micro'])) ? $params['is_micro'] : 0,
        ];

        return $arr;
    }

    /**
     * 月收入
     *
     * @param array $params
     * @return string
     */
    public static function incomeRange($params = [])
    {
        switch ($params['salary']) {
            case '001':
                $incomeRange = '4';
                break;
            case '004':
                $incomeRange = '1';
                break;
            default:
                $incomeRange = '2';
        }

        return $incomeRange;
    }

    /**
     * 职业类型
     *
     * @param array $params
     * @return string
     */
    public static function professionType($params = [])
    {
        switch ($params['occupation']) {
            case '001':
                $professionType = '1';
                break;
            case '002':
                $professionType = '3';
                break;
            case '003':
                $professionType = '4';
                break;
            default:
                $professionType = '5';
        }

        return $professionType;
    }

    /**
     * 根据返回code 进行处理
     *
     * @param $responseCode
     * @return string
     */
    public static function getMessage($responseCode)
    {
        switch ($responseCode) {
            case '0000':
                $message = '处理成功';
                break;
            case '0001':
                $message = '系统中不存在该供应商ID';
                break;
            case '0002':
                $message = '该批次已导入';
                break;
            case '0003':
                $message = '导入的数据为空';
                break;
            case '0004':
                $message = '列表为空,无需导入';
                break;
            case '9999':
                $message = '处理失败';
                break;
            default:
                $message = '未知错误';
        }

        return $message;
    }


}
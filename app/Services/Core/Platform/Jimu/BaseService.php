<?php
namespace App\Services\Core\Platform\Jimu;

use App\Helpers\Http\HttpClient;
use App\Services\Core\Platform\PlatformService;
use GuzzleHttp\Exception\ClientException as GuzzleRequestException;

class BaseService extends PlatformService{

    const BASE_URL = 'https://loan-m.jimu.com'; //online
    const PARTNER = 'sdzj';
    const SALT = '6E7EE5A8DC2643FBBF0512EC1C36CF88';  //online

    //公钥  online
    const  PUBLIC_KEY = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA231hDkyh9pKH6LunEWtV1k
KNXvZvjido53lRfY4Idd2INWONG7MquVbDhfUh7ytSAu23BDptQm52G+kbzhRAE7Jd
XPsPy2vRIUVQHbaP3HN+aqwh7V6a9D6i0/MAxm0nnUL23qzI+KfmDsINBzfPDUQl3l
dLTrzNgD1uL5QqISg7zfP58GXLizIDfErvGyIL/w9234zjXaBVHN1p566fExLtvRP0
G0XjPe1Yapp+XwVimG5dFmaqRRZlP1FgYApwPA+jyN5yx23xOIl/LjR6gNbqJh9g65
/NgWVYyZZa1gvfwYOuoPiLvx1hVy3yx5OTcANi0Enk1X23cKYrnJtxeHodlfIJjE5j
fwIDAQAB
-----END PUBLIC KEY-----';

    /*
     * @desc    验证签名     接口请求必有参数
     * @param   timestamp   string  时间戳
     * @param   partner     string  第三方标识
     * @param   sign        string  签名
     * */

    public function Valid($data) {
        $sign      = $data['sign'];    //签名
        $timestamp = $data['timestamp'];  //毫秒时间戳
        $partner   = $data['partner'];  //第三方唯一标识
        $salt      = BaseService::SALT;
        //进行字典序排序
        $tmpArr    = array($partner, $salt, $timestamp);
        sort($tmpArr, SORT_STRING);
        $tmpStr    = implode($tmpArr, '&');
        $tmpStr    = md5($tmpStr);

        if ($tmpStr == $sign) {
            return true;
        } else {
            return false;
        }
    }

    function StrToBin($str) {
        //1.列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        //2.unpack字符
        foreach ($arr as &$v) {
            $temp = unpack('H*', $v);
            $v    = base_convert($temp[1], 16, 2);
            unset($temp);
        }

        return join(' ', $arr);
    }

    function BinToStr($str) {
        $arr = explode(' ', $str);
        foreach ($arr as &$v) {
            $v = pack("H" . strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
        }

        return join('', $arr);
    }

    public function encrypt($input, $key) {
        $pu_key = openssl_pkey_get_public($key);
        $len    = strlen($input);
        $offSet = 117;
        $start  = 0;
        if ($len - $offSet > 0) {
            $str = '';
            $i   = 1;
            while ($len - $start > 0) {
                if (empty($start))
                    $start  = 0;
                $string = substr($input, $start, $offSet);
                openssl_public_encrypt($string, $encrypted, $key);
                $str .= $encrypted;
                $i++;
                $start  = ($i - 1) * $offSet;
            }
        }else {
            openssl_public_encrypt($input, $encrypted, $key); //公钥加密
            $str = $encrypted;
        }
        return base64_encode($str);
    }

    /*
     * $desc    用户信息接口
     * */

    public static function JiMu($data)
    {
        //转化成json格式
        $userJson  = json_encode($data);
        //必传参数
        $needData  = BaseService::threeParam();
        //获得公钥
        $publicKey = openssl_pkey_get_public(BaseService::PUBLIC_KEY);
        //分段加密
        $dataArray = str_split($userJson, 117);  //将json字符串以单位117长度分成数组
        $crypted   = '';
        //循环对每单位进行加密
        foreach ($dataArray as $subData) {
            $subCrypted = '';
            openssl_public_encrypt($subData, $subCrypted, $publicKey);
            $crypted.= $subCrypted;
        }
        $content  = base64_encode($crypted);
        $postData = [
            'content'   => $content,
            'timestamp' => $needData['timestamp'],
            'partner'   => $needData['partner'],
            'sign'      => $needData['sign']
        ];
        //调用积木盒子接口
        $url      = BaseService::BASE_URL . '/3rd/sdzj/service.do';
        $result = BaseService::curl($postData ,$url);
        return $result;
    }
    
    public static function curl($dataParam , $url , $type='POST')
    {
        $data = [
            'form_params' => $dataParam
        ];
        $promise = HttpClient::i([ 'verify' => false])->request($type,$url,$data);
        $result   = $promise->getBody()->getContents();
        return \GuzzleHttp\json_decode($result,true);
    }

    //解密方法
    public function decrypt($data) {
        $decrypted  = '';
        $data       = base64_decode($data);
//        $privateKey = openssl_pkey_get_private($this->private_key);
        $privateKey = openssl_pkey_get_private($this->pri);
        $dataArray  = str_split($data, 128);
        foreach ($dataArray as $subData) {
            $subDecrypted = '';
            openssl_private_decrypt($subData, $subDecrypted, $privateKey);
            $decrypted .= $subDecrypted;
        }
        echo $decrypted;
        die;
        return json_decode($decrypted, true);
    }

    /*
     * @desc    必须传递参数
     * */

    public static function threeParam()
    {
        //对数据进行处理
        $timestamp = BaseService::msectime();
        $partner   = BaseService::PARTNER;
        $salt      = BaseService::SALT;

        //加密处理
        $needData = BaseService::getSign($timestamp, $partner, $salt);

        return $needData;
    }

    /*
     * @desc    时间以毫秒返回
     * */

    private static function msectime() {
        list($tmp1, $tmp2) = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
    }

    /*
     * @desc    加密处理得到签名sign
     * */

    private static function getSign($timestamp, $partner, $salt) {
        //进行字典序排序
        $tmpArr = array($partner, $timestamp , $salt);
        //sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr, '&');
        $tmpStr = md5($tmpStr);

        $needData = [
            'timestamp' => (string) $timestamp,
            'partner'   => $partner,
            'sign'      => $tmpStr
        ];
        return $needData;
    }

    public static function renderJson($code = 0, $message = '', $data = []) {
        header("Content-Type:application/json;charset=UTF-8");
        $arrInfo['code']    = $code;
        $arrInfo['message'] = $message;
        $arrInfo['data']    = $data;
        $output             = Json::encode($arrInfo);
        exit($output);
    }
}

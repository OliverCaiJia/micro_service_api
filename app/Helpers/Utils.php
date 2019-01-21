<?php

namespace App\Helpers;

class Utils
{

    /**
     * 获取浏览器名称
     * @return string
     */
    public static function getBrowser()
    {
        $agent = $_SERVER["HTTP_USER_AGENT"];
        //ie11判断
        if (strpos($agent, 'MSIE') !== false || strpos($agent, 'rv:11.0')) {
            return "ie";
        } else if (strpos($agent, 'Firefox') !== false) {
            return "firefox";
        } else if (strpos($agent, 'Chrome') !== false) {
            return "chrome";
        } else if (strpos($agent, 'Opera') !== false) {
            return 'opera';
        } else if ((strpos($agent, 'Chrome') == false) && strpos($agent, 'Safari') !== false) {
            return 'safari';
        } else if (strpos($agent, 'MicroMessenger') !== false) {
            return 'wechat';
        } else {
            return 'unknown';
        }
    }

    /**
     * 获取浏览器版本
     * @return string
     */
    public static function getBrowserVer()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE\s(\d+)\..*/i', $agent, $regs)) {
            return $regs[1];
        } elseif (preg_match('/FireFox\/(\d+)\..*/i', $agent, $regs)) {
            return $regs[1];
        } elseif (preg_match('/Opera[\s|\/](\d+)\..*/i', $agent, $regs)) {
            return $regs[1];
        } elseif (preg_match('/Chrome\/(\d+)\..*/i', $agent, $regs)) {
            return $regs[1];
        } elseif ((strpos($agent, 'Chrome') == false) && preg_match('/Safari\/(\d+)\..*$/i', $agent, $regs)) {
            return $regs[1];
        } elseif (preg_match('/MicroMessenger\/(\d+)\..*/i', $agent, $regs)) {
            return $regs[1];
        } else {
            return 'unknown';
        }
    }

    /**
     * 判断是否微信浏览器
     * @return type
     */
    public static function isWechatBrowser()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        return (strpos($user_agent, "MicroMessenger") !== false);
    }


    /**
     * 判断是iOS
     * @return type
     */
    public static function isiOS()
    {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '' ;
        if (strpos($user_agent, 'iPhone') || strpos($user_agent, 'iPad') || strpos($user_agent, 'iPod')) {
            return true;
        }
        return false;
    }

    /**
     * 判断是iOS
     * @return type
     */
    public static function isMAPI()
    {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (strpos($user_agent, 'mapi')) {
            return true;
        }
        return false;
    }

    /**
     * 判断是Android
     * @return type
     */
    public static function isAndroid()
    {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (strpos($user_agent, 'Android')) {
            return true;
        }
        return false;
    }

    /**
     * 获取访问域名
     * @return type
     */
    public static function getHostUrl($request_url = null)
    {
        $request_url = empty($request_url) ? 'http://localhost' : $request_url;
        $parsed_url = parse_url($request_url);
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        return "$scheme$host$port";
    }

    /**
     * 获取IP地址
     */
    public static function ipAddress($type = 0)
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
            $cip = $_SERVER["REMOTE_ADDR"];
        } else {
            $cip = "127.0.0.1";
        }
        return $cip;
    }

    /**
     * @abstract 获取html代码中的img的src
     * @return array
     */
    public static function getHtmlImageSrc($html)
    {
        if (!$html)
            return array();

        $preg_partern = '/<img.+?src=\"?(.+?\.(jpg|gif|bmp|bnp|png))\"?.+?>/i';
        $match = array();
        preg_match_all($preg_partern, $html, $match);
        return $match[1];
    }

    /**
     * @abstract 替换html代码里面的img标签
     * @param type $html
     * @param type $replace default ''
     * @return string
     */
    public static function replaceHtmlImage($html, $replace = '')
    {
        if (!$html)
            return '';

        $preg_partern = '/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+?>/i';
        return preg_replace($preg_partern, $replace, $html);
    }

    /**
     * 生成随机密码
     */
    public static function createPassword($pw_length = 8)
    {
        $randpwd = '';
        for ($i = 0; $i < $pw_length; $i++) {
            $randpwd .= chr(mt_rand(48, 122));
        }
        return $randpwd;
    }

    /**
     * 计算字utf8符长度
     */
    public static function utf8StrLen($str)
    {
        $count = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $value = ord($str[$i]);
            if ($value > 127) {
                $count++;
                if ($value >= 192 && $value <= 223) {
                    $i++;
                } elseif ($value >= 224 && $value <= 239) {
                    $i = $i + 2;
                } elseif ($value >= 240 && $value <= 247) {
                    $i = $i + 3;
                } else {

                }
            }
            $count++;
        }
        return $count;
    }

    /**
     * 去除特殊符号
     */
    public static function removeSpe($string = "")
    {
        $string = htmlspecialchars_decode($string);
        $search = array("\\\"");
        $replace = array("\"");
        return str_replace($search, $replace, $string);
    }

    /**
     * 删除HTML标签
     */
    public static function removeHTML($string = "")
    {
        $string = html_entity_decode($string, ENT_COMPAT, 'UTF-8');
        $string = stripslashes($string);
        $string = strip_tags($string);
        $search = array(" ", "　", "\t", "\n", "\r");
        $replace = array("", "", "", "", "");
        return str_replace($search, $replace, $string);
    }

    /**
     * @param $param
     * @return string
     * 去除字符串中的空格
     */
    public static function removeSpace($param)
    {
        return isset($param) ? str_replace(" ", "", $param) : '';
    }

    /**
     * @param $param
     * @return mixed|string
     * 去除 空格 - +86
     */
    public static function removeSpaces($param)
    {
        return isset($param) ? preg_replace('/[\s-]*/', '', $param) : '';
    }

    /**
     * @desc DES加密
     * @author wangqingyi
     * @param $str
     * @param $key
     * @return string
     */
    public static function encrypt($str, $key, $toBase64 = true)
    {
        $block = mcrypt_get_block_size('des', 'ecb');

        $pad = $block - (strlen($str) % $block);

        $str .= str_repeat(chr($pad), $pad);

        if ($toBase64) {
            return base64_encode(mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB));
        } else {
            return mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
        }
    }

    /**
     * @desc DES解密
     * @author wangqingyi
     * @param $str
     * @param $key
     * @return string
     */
    public static function decrypt($str, $key, $toBase64 = true)
    {
        if ($toBase64) {
            $str = mcrypt_decrypt(MCRYPT_DES, $key, base64_decode($str), MCRYPT_MODE_ECB);
        } else {
            $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
        }

        $block = mcrypt_get_block_size('des', 'ecb');

        $pad = ord($str[($len = strlen($str)) - 1]);

        return substr($str, 0, strlen($str) - $pad);

    }

    /**
     * @des AES加密
     * @param $encryptStr
     * @param string $localIV AES算法的初始变量iv
     * @param string $encryptKey AES算法的密码password
     * @return string
     */
    public static function AesEncrypt($encryptStr, $localIV = '', $encryptKey = '', $toBase64 = true)
    {

        //Open module
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);

        //print "module = $module <br/>" ;

        mcrypt_generic_init($module, $encryptKey, $localIV);

        //Padding
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($encryptStr) % $block); //Compute how many characters need to pad
        $encryptStr .= str_repeat(chr($pad), $pad); // After pad, the str length must be equal to block or its integer multiples

        //encrypt
        $encrypted = mcrypt_generic($module, $encryptStr);

        //Close
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);

        if (!$toBase64) {
            return $encrypted;
        }
        return base64_encode($encrypted);

    }

    /**
     * @des AES解密
     * @param $encryptStr
     * @param string $localIV
     * @param string $encryptKey
     * @return bool|string
     */
    public function AesDecrypt($encryptStr, $localIV = '', $encryptKey = '', $toBase64 = true)
    {
        //Open module
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);

        //print "module = $module <br/>" ;

        mcrypt_generic_init($module, $encryptKey, $localIV);

        if ($toBase64) {
            $encryptedData = base64_decode($encryptStr);
        } else {
            $encryptedData = $encryptStr;
        }

        $encryptedData = mdecrypt_generic($module, $encryptedData);

        return $encryptedData;
    }

    /**
     * @param $query
     * @return array
     * 获取url地址中的参数
     */
    public static function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            if (isset($item[1])) {
                $params[$item[0]] = $item[1];
            }
        }
        return $params ? $params : [];
    }

    /**
     * 生成UUID
     * @return string
     */
    public static function generate_uuid()
    {
        $charId = md5(uniqid(rand(), true));
        $hyphen = chr(45);// "-"
        $uuid = substr($charId, 0, 8) . $hyphen
            . substr($charId, 8, 4) . $hyphen
            . substr($charId, 12, 4) . $hyphen
            . substr($charId, 16, 4) . $hyphen
            . substr($charId, 20, 12);
        return $uuid;
    }

    /**
     * @param $params
     * @return false|int|string
     * 格式化日期为年
     * 2017-08-09  To  2017
     */
    public static function formatDateToYear($params)
    {
        $year = date('Y', strtotime($params));
        return $year ? $year : 0;
    }

    /**
     * @param $params
     * @return false|int|string
     * 格式化日期为月
     * 08/09
     */
    public static function formatDateToMonthDay($param)
    {
        return date('m', strtotime($param)) . '/' . date('d', strtotime($param));
    }

    /**
     * @param $params
     * @return false|int|string
     * 格式化日期为天
     * 2017-08-09  To  9
     */
    public static function formatDateToDays($params)
    {
        $days = date('j', strtotime($params));
        return $days ? $days : 0;
    }

    /**
     * @param $date
     * @param int $length
     * @return mixed
     * 当前日期的前几个月 与 后几个月
     */
    public static function getLastTime($date, $beforeLength = 0, $afterLength = 0)
    {
        $beforeYear = $afterYear = date('Y', $date);
        $month = date('n', $date);
        //正常
        $before = $month - $beforeLength;
        $after = $month + $afterLength;
        //前几个月
        if ($before < 0) {
            $before = $beforeLength - $month;
            $beforeYear--;
        }
        //后几个月
        if ($after > 12) {
            $after = $month + $afterLength - 12;
            $afterYear++;
        }
        $data['before'] = date('Y-m', strtotime($beforeYear . '-' . $before));
        $data['after'] = date('Y-m', strtotime($afterYear . '-' . $after));

        return $data;
    }

    /**
     * @param $mobile
     * @return mixed
     * 手机号格式转化
     *  135****4523
     */
    public static function formatMobile($mobile)
    {
        return substr_replace($mobile, '****', 3, 4);
    }

    /** url安全的base64编码
     * @param $string
     * @return mixed|string
     */
    public static function urlsafe_base64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }

    /** url安全的base64解码
     * @param $string
     * @return bool|string
     */
    public static function urlsafe_base64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    /** 根据身份证件号获取用户年龄
     * @param $string
     * @return bool|string
     */
    public static function getAgeByID($id)
    {
        if(empty($id)) return '';

        $date=strtotime(substr($id,6,8));
        //获得出生年月日的时间戳
        $today=strtotime('today');
        //获得今日的时间戳
        $diff=floor(($today-$date)/86400/365);
        //strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age=strtotime(substr($id,6,8).' +'.$diff.'years')>$today?($diff+1):$diff;

        return $age;
    }
    
    /**根据身份证获取性别
     * @param $cid
     * @return string
     */
    function getSex($cid) {
        //根据身份证号，自动返回性别
        if(empty($cid)) return '';
        $sexint = (int)substr($cid,16,1);
        return $sexint % 2 === 0 ? '女' : '男';
    }
    /*
     * 获取当前毫秒级时间戳
     */
   public static function getMicrotime() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
    /**
     *更具生日获取你年龄
     */
    public static function getAge($birthday)
    {
        $age = strtotime($birthday);
        if($age === false){
            return false;
        }
        list($y1,$m1,$d1) = explode("-",date("Y-m-d",$age));
        $now = strtotime("now");
        list($y2,$m2,$d2) = explode("-",date("Y-m-d",$now));
        $age = $y2 - $y1;
        if((int)($m2.$d2) < (int)($m1.$d1))
            $age -= 1;
        return $age;
    }

    /**
     * 通过身份证号查询出性别与生日 1为男 0为女
     * @param $certificate_no
     * @return mixed
     */
    public static function getAgeAndBirthDayByCard($certificate_no)
    {
        $data= ['birthday' => '1970-01-01', 'sex' => 1];
        if (strlen($certificate_no) == 15 || strlen($certificate_no) == 18) {
            $data['birthday'] = strlen($certificate_no)==15 ? ('19' . substr($certificate_no, 6, 6)) : substr($certificate_no, 6, 8);
            $data['birthday'] = substr($data['birthday'], 0, 4) . '-' . substr($data['birthday'], 4, 2) . '-' . substr($data['birthday'], 6);
            $data['sex'] = substr($certificate_no, (strlen($certificate_no)==15 ? 0 : -2), 1) % 2 ? '1' : '0';
        }

        return $data;
    }

}

<?php

namespace App\Strategies;

use App\Constants\CreditConstant;
use App\Constants\UserConstant;
use App\Helpers\DateUtils;
use App\Helpers\Formater\NumberFormater;
use App\Helpers\Utils;
use App\Models\Factory\UserFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;
use App\Helpers\UserAgent;

/**
 * 用户公共策略
 *
 * Class UserStrategy
 * @package App\Strategies
 */
class UserStrategy extends AppStrategy
{

    /**
     * @param $ctime
     * @return mixed
     * @desc    是否在登录的时候选择身份
     */
    public static function fetchDisplay($ctime)
    {
        $time = strtotime('2016-8-12 00:00:00');
        $ctime = strtotime($ctime);
        if ($ctime <= $time) {
            //显示选择身份页面
            $data['display'] = 1;
        } else {
            $data['display'] = 0;
        }
        return $data;
    }

    /**
     * @desc 生成随机字符串
     * @param $length
     * @return null|string
     */
    public static function getRandChar($length, $format = 'ALL')
    {
        $str = null;
        //$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        switch ($format) {
            case 'ALL':
                $strPol = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            case 'NC':
                $strPol = '0123456789abcdefghijklmnopqrstuvwxyz';
                break;
            case 'CHAR':
                $strPol = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            case 'NUMBER':
                $strPol = '0123456789' . time() . mt_rand(100, 1000000);
                break;
            default :
                $strPol = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                break;
        }
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[mt_rand(0, $max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    /**
     * @desc
     * 判断用户使用的終端類型
     *
     * "id"    "nid"    "name"    "status"    "remark"    "product_type"
     * "1"    "ios_native"    "iOS原生"    "1"    "iOS原生"    "sudaizhijia"
     * "2"    "android_native"    "Android原生"    "1"    "Android原生"    "sudaizhijia"
     * "3"    "h5_web"    "H5.Wechat"    "1"    "H5"    "sudaizhijia"
     * "4"    "event_landing"    "Landing"    "1"    "Landing浏览器"    "sudaizhijia"
     * "5"    "ios_web"    "ios_web"    "1"    "iOS浏览器"    "sudaizhijia"
     * "6"    "android_web"    "android_web"    "1"    "Android浏览器"    "sudaizhijia"
     * "7"    "pc_web"    "pc_web"    "1"    "PC"    "sudaizhijia"
     * "8"    "wechat"    "wechat"    "1"    "微信"    "sudaizhijia"
     */
    public static function version()
    {
        $user_agent = UserAgent::i()->getUserAgent();
        $version = 3; //M
        if ($user_agent) {
            if (strpos($user_agent, 'iPhone') || strpos($user_agent, 'iPad') || strpos($user_agent, 'iPod')) {
                $version = 1;
            } elseif (strpos($user_agent, 'Android')) {
                $version = 2;
            } elseif (strpos($user_agent, 'MicroMessenger')) {
                $version = 8;
            }
        }

        return $version;
    }

    /**
     * @param $indent
     * @param $array1
     * @param $array2
     * @param $array3
     * @param $array4
     * @param $array5
     * @return mixed
     * 用户信息——进度
     */
    public static function fetchProgress($indent, $array1 = [], $array2 = [], $array3 = [], $array4 = [], $array5 = [])
    {
        $datas = array_merge($array1, $array2, $array3, $array4, $array5);
        $datas = array_filter($datas);
        $userInfoCounts = count($datas);

        if ($indent == 1) {
            $progress = bcmul(bcdiv($userInfoCounts, 21, 2), 100);
        } elseif ($indent == 2) {
            $progress = bcmul(bcdiv($userInfoCounts, 25, 2), 100);
        } elseif ($indent == 3) {
            $progress = bcmul(bcdiv($userInfoCounts, 25, 2), 100);
        } elseif ($indent == 4) {
            $progress = bcmul(bcdiv($userInfoCounts, 19, 2), 100);
        } else {
            $progress = CreditConstant::DEFAULT_EMPTY;
        }
        $res['userInfoCounts'] = $userInfoCounts;
        $res['progress'] = $progress;
        return $res;
    }

    /**
     * @param array $array1
     * @param array $array2
     * @param array $array3
     * @param array $array4
     * @param array $array5
     * 普通合并数组
     */
    public static function getMerges($array1 = [], $array2 = [], $array3 = [], $array4 = [], $array5 = [])
    {
        $datas = array_merge($array1, $array2, $array3, $array4, $array5);
        $datas = array_filter($datas);
        $userInfoCounts = count($datas);
        return $userInfoCounts ? $userInfoCounts : 0;
    }

    /**
     * @param $params
     * @return mixed
     * 如果用户名中含有sd_ 就用手机号进行替换
     */
    public static function replaceUsernameSd($params)
    {
        $mobile = Utils::formatMobile($params['mobile']);
        if (preg_match('/sd_*/i', $params['username'])) {
            $data['is_username'] = 0;
            $data['username'] = $mobile;
        } else {
            $data['is_username'] = 1;
            $data['username'] = $params['username'];
        }

        return $data;
    }

    /**
     * @param $data
     * @return mixed
     * 用户信息数据处理
     */
    public static function fetchUserinfo($data = [])
    {
        //账号余额
        $userAccount = $data['userAccount'];
        //账号积分
        $userScore = $data['userScore'];
        //用户信息
        $userProfile = $data['userProfile'];
        $userAuth = $data['userAuth'];
        $userinfo = $data['userinfo'];
        $userId = $data['userId'];

        $info['sex'] = $userProfile['sex'];
        $info['realname'] = $userProfile['realname'];
        $info['userScore'] = intval($userScore);
        $info['userAccount'] = NumberFormater::formatMoney($userAccount);
        $info['indent'] = UserFactory::fetchUserIndent($userId);
        $info['mobile'] = Utils::formatMobile($userAuth['mobile']);
        $info['user_photo'] = isset($userinfo['user_photo']) ? QiniuService::getImgToPhoto($userinfo['user_photo']) : UserConstant::USER_PHOTO_DEFAULT;
        $usernameData = UserStrategy::replaceUsernameSd($userAuth);
        $info['username'] = $usernameData['username'];
        //判断是否修改用户名 0添加，1修改
        $info['is_username'] = $usernameData['is_username'];
        //判断是否签到
        $info['user_sign'] = $data['user_sign'];

        return $info;
    }

    /**账户信息
     * @param array $data
     * @return mixed
     */
    public static function fetchUserautheninfo($data = [])
    {
        //账号余额
        $userAccount = $data['userAccount'];
        //账号积分
        $userScore = $data['userScore'];
        //用户信息
        $userProfile = $data['userProfile'];
        $userAuth = $data['userAuth'];
        $userinfo = $data['userinfo'];
        $userId = $data['userId'];
        //身份证信息
        $idcardInfo = $data['realname'];
        //vip
        $vip = $data['vip'];

        $info['sex'] = $userProfile['sex'];
        $info['userScore'] = intval($userScore);
        $info['userAccount'] = NumberFormater::formatMoney($userAccount);
        $info['indent'] = UserFactory::fetchUserIndent($userId);
        $info['mobile'] = Utils::formatMobile($userAuth['mobile']);
        $info['user_photo'] = isset($userinfo['user_photo']) ? QiniuService::getImgToPhoto($userinfo['user_photo']) : UserConstant::USER_PHOTO_DEFAULT;
        $usernameData = UserStrategy::replaceUsernameSd($userAuth);
        $info['username'] = $usernameData['username'];
        //判断是否修改用户名 0添加，1修改
        $info['is_username'] = $usernameData['is_username'];
        //判断是否签到
        $info['user_sign'] = $data['user_sign'];

        //判断身份证认证状况 0未认证,1已认证
        if ($idcardInfo) {
            $now = time();
            $date = floor((strtotime($idcardInfo['card_endtime']) - $now) / 86400);
            if ($date <= 30 && $date > 0) {
                //30天之内 即将过期
                $info['idcard_sign'] = 2;
            } elseif ($date <= 0) {
                //已过期
                $info['idcard_sign'] = 3;
            } else {
                //已认证
                $info['idcard_sign'] = 1;
            }
            $info['realname'] = $idcardInfo['realname'];
            $info['certificate_no'] = UserIdentityStrategy::encryCertificateNo($idcardInfo['certificate_no']);
            $info['sex'] = SexStrategy::intToStr($idcardInfo['sex']);
            $info['certificate_type'] = UserIdentityStrategy::certificateTypeIntToStr($idcardInfo['certificate_type']);
            $info['idcard_time'] = DateUtils::formatTimeToYmd($idcardInfo['card_starttime']) . '-' . DateUtils::formatTimeToYmd($idcardInfo['card_endtime']);
        } else {
            //未认证
            $info['idcard_sign'] = 0;
        }
        //判断银行卡绑定状态
        if ($data['bankcardCount'] > 0) {
            $info['bankcard_sign'] = 1;
        } else {
            $info['bankcard_sign'] = 0;
        }
        //判断vip
        if ($vip) {
            //vip 用户判定
            $info['vip_sign'] = $vip['status'];
        } else {
            //普通用户
            $info['vip_sign'] = 0;
        }

        return $info;
    }
}

<?php

namespace App\Strategies;

use App\Constants\PaymentConstant;
use App\Constants\UserVipConstant;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\Factory\PaymentFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserOrderFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Factory\UserVipFactory;
use App\Models\Orm\UserVip;
use App\Services\AppService;
use App\Services\Core\Payment\PaymentService;
use App\Services\Core\Store\Qiniu\QiniuService;

/**
 * Class UserVipStrategy
 * @package App\Strategies
 * 会员策略层
 */
class UserVipStrategy extends AppStrategy
{
    /**
     * 会员判断
     *
     * @param $userId
     * @param $terminalType
     * @return array
     */
    public static function isUserVipAgain($userId, $terminalType)
    {
        $params = [];
        if (!empty($userId)) {
            $data = UserVipFactory::getUserVip($userId);
            if (!empty($data)) {
                $loanVipArr = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
                $data['productIds'] = $loanVipArr;
                $data['terminalType'] = $terminalType;
                $params['totalPriceTime'] = date('Y-m-d', strtotime($data['end_time']));
                //date('Y', strtotime($data['end_time'])).'年'.date('m', strtotime($data['end_time'])).'月'.date('d', strtotime($data['end_time'])).'日到期';
                $params['loanVipCount'] = ProductFactory::fetchProductCounts($data);
                $params['creditCount'] = UserReportFactory::getUserReportCount($userId);
                $params['isVipUser'] = 1;
            }
        }

        if (empty($params)) {
            $arr['isVipUser'] = 0;
            //vip产品数
            $arr['loanVipCount'] = '';
            //闪信免费查个数
            $arr['creditCount'] = '';
            //会员到期时间
            $arr['totalPriceTime'] = '';
            //会员动态
            $arr['memberActivity'] = UserVipStrategy::getMemberActivityInfo();
        } else {
            $arr['isVipUser'] = $params['isVipUser'];
            $arr['loanVipCount'] = $params['loanVipCount'];
            $arr['creditCount'] = $params['creditCount'];
            $arr['totalPriceTime'] = DateUtils::formatDateToLeftdata($params['totalPriceTime']);
            $arr['memberActivity'] = [];
        }

        //价格
        $arr['totalPrice'] = UserVipFactory::getVipAmount() . '/年';
        $arr['totalNoPrice'] = UserVipConstant::MEMBER_PRICE . '/年';
        //单纯显示价格
        $arr['totalPriceNum'] = UserVipFactory::getVipAmount() . '';
        $arr['totalNoPriceNum'] = UserVipConstant::MEMBER_PRICE . '';

        return $arr;
    }

    /**
     * 获取会员特权
     *
     * @return array
     */
    public static function getVipPrivilege()
    {
        $vipId = UserVipFactory::getVipTypeId();
        $pids = UserVipFactory::getVipPrivilegeIds($vipId);
        $res = [];
        foreach ($pids as $pid) {
            $arr = UserVipFactory::getVipPrivilegeInfo($pid);
            if ($arr) {
                $arr['img_link'] = QiniuService::getImgs($arr['img_link']);
            }
            unset($arr['created_at']);
            unset($arr['created_id']);
            unset($arr['updated_at']);
            unset($arr['updated_id']);
            unset($arr['status']);
            unset($arr['is_desc']);
            unset($arr['is_description']);
            $res[] = $arr;
        }
        //删除空数组
        $res = array_filter($res);
        $res = array_values($res);

        return $res;
    }

    /**
     * 根据vip类型的不同处理数据
     *
     * @param $message
     * @param $vipType
     * @return array
     */
    public static function getDiffVipTypeDeal($message, $vipType)
    {
        switch ($vipType) {
            case UserVipConstant::VIP_TYPE_NID:
                if (!empty($message) && $message['status'] == 1) {
                    $endTime = strtotime($message['end_time']);
                    if ($endTime > time()) {
                        $timeStamp = $endTime + (PaymentFactory::getVipTime() * 24 * 60 * 60);
                        $data['time'] = date('Y-m-d H:i:s', $timeStamp);
                    } else {
                        $data['time'] = date('Y-m-d H:i:s', UserVipStrategy::getVipExpired());
                    }
                } else {
                    $data['time'] = date('Y-m-d H:i:s', UserVipStrategy::getVipExpired());
                }
                break;
            default:
                $data = [];
        }

        return $data;
    }

    /**
     * 统计数值
     *
     * @param $typeId
     * @return mixed
     */
    public static function getReStatistics($typeId)
    {
        $data = [];
        $ids = UserVipFactory::getPrivilegeId($typeId);
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $lege = UserVipFactory::getPrivilege($id);
                if (!empty($lege) || $lege['type_nid'] == UserVipConstant::MEMBER_COMMON_LOAN_PRODUCT_NID) {
                    $data['count'] = $lege['value'];
                } elseif (!empty($lege) || $lege['type_nid'] == UserVipConstant::MEMBER_VIP_LOAN_PRODUCT_NID) {
                    $data['count'] = $lege['value'];
                }
            }
        }

        return $data;
    }

    /**
     * 是会员返回到期时间
     *
     * @param $userId
     * @return false|string
     */
    public static function isUserVip($userId)
    {
        $time = "";
        if (!empty($userId)) {
            $data = UserVipFactory::getUserVip($userId);
            if (!empty($data)) {
                $time = date('Y', strtotime($data['end_time'])) . '年' . date('m', strtotime($data['end_time'])) . '月' . date('d', strtotime($data['end_time'])) . '日到期';
            }
        }

        return $time;
    }

    /**
     * 获取会员动态信息
     *
     * @return array
     */
    public static function getMemberActivityInfo()
    {
        $userids = UserVipStrategy::getRandUserId();
        $userData = [];
        foreach ($userids as $uid) {
            $users = UserVipFactory::getUser($uid['user_id']);
            if (!empty($users)) {
                $message = UserVipStrategy::getRandContent();
                $userData[] = UserVipStrategy::getMemberActivityData($uid['user_id'], $message, $users);;
            } else {
                $userData = [];
            }

        }

        $len = count($userData);
        if ($len < 10) {
            $limit = 10 - $len;
            $ids = UserVipFactory::getUserLimit($limit);
            foreach ($ids as $id) {
                $user = UserVipFactory::getUser($id['user_id']);
                $message = UserVipStrategy::getRandContent();
                $userData[] = UserVipStrategy::getMemberActivityData($id['user_id'], $message, $user);
            }
        }

        return $userData;
    }

    /**
     * 获取公共数据
     *
     * @param $userId
     * @param $message
     * @param $user
     * @return mixed
     */
    public static function getMemberActivityData($userId, $message, $user)
    {
        $data = UserStrategy::replaceUsernameSd($user);
        $data['photo'] = QiniuService::getImgToPhoto(UserVipFactory::getUserInfo($userId));
        $data['message'] = $message['content'];
        $data['money'] = $message['money'];
        $data['minute'] = UserVipStrategy::getRandMinute();
        $data['uid'] = $userId;

        return $data;
    }

    /**
     * 随机获取时间
     *
     * @return string
     */
    public static function getRandMinute()
    {
        return rand(1, 60) . '分钟前';
    }

    /**
     * 获取语句
     *
     * @return string
     */
    public static function getRandContent()
    {
        $reArr['money'] = "";
        $arr = [
            '通过会员服务疯狂下款',
            '已开通会员',
        ];

        $key = array_rand($arr, 1);

        if ($key == 0) {
            $reArr['money'] = rand(2, 11) . '000元';
        }

        $reArr['content'] = $arr[$key];

        return $reArr;
    }

    /**
     * 获取随机10个用户ID
     *
     * @return mixed
     */
    public static function getRandUserId()
    {
        //获取user_vip总数
        $userVips = UserVipFactory::getUserVipCount();
        if ($userVips > 10) {
            $userIds = UserVipFactory::getUserVipLimit();
        } else {
            //从user_auth中随机获取十个
            $userIds = UserVipFactory::getUserLimit();
        }

        return $userIds;
    }

    /**
     * 获取用户订单一些参数
     *
     * @return array
     */
    public static function getUserOrderOtherParams()
    {
        return [
            'order_type' => UserOrderFactory::getOrderType(),  //订单类型
            'payment_type' => UserOrderFactory::getPaymentType(),  //支付类型
            'amount' => UserVipFactory::getReVipAmount(UserVipConstant::VIP_TYPE_NID), //金额
        ];
    }

    /**
     * 获取vip展示年
     *
     * @return string
     */
    public static function getVipYeer()
    {
        $day = PaymentFactory::getVipTime();
        return number_format(($day / 365), 1);
    }

    /**
     * 获取过期时间
     *
     * @return false|string
     */
    public static function getVipExpired()
    {
        $time = PaymentFactory::getVipTime();
        $lastDay = time() + ($time * 24 * 60 * 60);

        return $lastDay;
    }

    /**
     * 获取vip状态
     *
     * @param $orderStatus
     * @return int
     */
    public static function getVipStatus($orderStatus = 0)
    {
        switch ($orderStatus) {
            case 0:
                $vipStatus = 0;  //禁用
                break;
            case 1:
                $vipStatus = 1;  //使用
                break;
            case 5:
                $vipStatus = 4;  //处理中
                break;
            default:
                $vipStatus = 0;  //禁用
        }

        return $vipStatus;
    }

    /**
     * 获取易宝订单一些参数值
     *
     * @return array
     */
    public static function getYibaoOtherParams()
    {
        return [
            'amount' => UserVipFactory::getReVipAmount(UserVipConstant::VIP_TYPE_NID) * 100,
            'productname' => UserVipConstant::ORDER_DEALER_NAME . ' - ' . UserVipConstant::ORDER_PRODUCT_NAME,
            'productdesc' => UserVipConstant::ORDER_DESC,
            'url_params' => UserVipConstant::ORDER_TYPE,
        ];
    }

    /**
     * 获取会员编号
     *
     * @param int $lastId 最后一个ID
     * @param string $prefix 前缀
     * @param string $name 名称
     * @param int $num 编号数字
     * @return string
     */
    public static function generateId($lastId, $name = 'VIP', $prefix = 'SD', $num = 8)
    {
        //获取毫秒时间
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec * 1000);
        $millisecond = str_pad($msec, 3, '0', STR_PAD_RIGHT);
        $timeLength = date("YmdHis") . $millisecond;

        $length = PaymentConstant::PAYMENT_PRODUCT_NUMBER_LENGTH - strlen(trim($prefix)) - strlen(trim($name)) - strlen(trim($timeLength)) - $num - 2;

        //如果还有多余的长度获取随机字符串
        $str = '';
        if ($length >= 0) {
            $str = PaymentService::i()->getRandString($length);
        } else {
            $name = substr($name, 0, $length);
        }

        //获取数字
        $strNum = sprintf("%0" . $num . "d", ($lastId + 1)); //UserVipFactory::getVipLastId()

        return $prefix . '-' . $name . '-' . $str . $timeLength . $strNum;
    }

    /**
     * 会员等级
     * @param string $vipNid
     * @return int
     */
    public static function getVipGrade($vipNid = '')
    {
        $vipNid = trim($vipNid);
        switch ($vipNid) {
            //普通会员
            case UserVipConstant::VIP_TYPE_NID:
                $vipGrade = 1;
                break;
            default :
                $vipGrade = 0;

        }
        return $vipGrade;

    }
}
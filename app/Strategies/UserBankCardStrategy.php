<?php
/**
 * Created by PhpStorm.
 * User: zengqiang
 * Date: 17-10-26
 * Time: 下午5:02
 */

namespace App\Strategies;

use App\Models\Factory\BankFactory;
use App\Services\Core\Store\Qiniu\QiniuService;

class UserBankCardStrategy extends AppStrategy
{

    /**
     * 加密银行卡号展示
     *
     * @param $cardnum
     * @return string
     */
    public static function formatCardNum($cardnum)
    {
        if (empty($cardnum)) {
            return '';
        }

        return substr($cardnum, 0, 4) . '****************' . substr($cardnum, -4);
    }

    /**
     * 截取银行卡后四位
     *
     * @param $cardnum
     * @return string
     */
    public static function getCardLastNum($cardnum)
    {
        if (empty($cardnum)) {
            return '';
        }

        return substr($cardnum, -4);
    }

    /**
     * 补充一些信息
     *
     * @param array $data
     * @return array
     */
    public static function getBackBankInfo($data = [])
    {
        $params = [];
        foreach ($data['list'] as $key => $val) {
            $bankinfo = BankFactory::fetchBankinfoById($val['bank_id']);
            $params[$key]['user_bank_id'] = $val['id'];
            $params[$key]['bank_name'] = empty($bankinfo['sname']) ? $bankinfo['name'] : $bankinfo['sname'];
            $params[$key]['bank_logo'] = QiniuService::getImgs($bankinfo['litpic']);
            $params[$key]['account'] = UserBankCardStrategy::formatCardNum($val['account']);
            $params[$key]['last_num'] = UserBankCardStrategy::getCardLastNum($val['account']);
            $params[$key]['card_type_name'] = UserBankCardStrategy::getBankCardTypeName($val['card_type']);
            $params[$key]['card_last_status'] = $val['card_last_status'];
            if (empty($data['userBankId'])) {
                $params[$key]['card_last_pay_status'] = $val['card_last_status'];
            } else {
                $params[$key]['card_last_pay_status'] = $val['id'] == $data['userBankId'] ? 1 : 0;
            }

        }

        return $params ? $params : [];
    }

    /**
     * 获取银行卡类型名称
     *
     * @param int $cardType 存银行卡类型
     * @return string
     */
    public static function getBankCardTypeName($cardType)
    {
        switch ($cardType) {
            case 1:
                $cardName = '储蓄卡';
                break;
            case 2:
                $cardName = '信用卡';
                break;
            default:
                $cardName = '';
        }

        return $cardName;
    }

    /**
     * 格式化银行列表银行卡号
     * @param array $params
     * @return array
     */
    public static function getUserbanksinfo($params = [])
    {
        foreach ($params as $key => $val) {
            $params[$key]['account'] = UserBankCardStrategy::formatCardNum($val['account']);
        }

        return $params ? $params : [];
    }

    /**
     * 上次支付银行卡信息
     * @param $data
     * @return array
     */
    public static function getPaymentBank($data)
    {
        $bankinfo = isset($data['bank_id']) ? BankFactory::fetchBankinfoById($data['bank_id']) : [];
        $params['user_bank_id'] = isset($data['id']) ? $data['id'] : 0;
        $params['bank_id'] = isset($data['bank_id']) ? $data['bank_id'] : 0;
        $params['bank_name'] = !empty($bankinfo) ? $bankinfo['sname'] : '';
        $params['bank_logo'] = !empty($bankinfo) ? QiniuService::getImgs($bankinfo['litpic']) : '';
        $params['account'] = isset($data['account']) ? UserBankCardStrategy::formatCardNum($data['account']) : '';
        $params['last_num'] = isset($data['account']) ? UserBankCardStrategy::getCardLastNum($data['account']) : '';
        $params['card_type_name'] = isset($data['card_type']) ? UserBankCardStrategy::getBankCardTypeName($data['card_type']) : '';
        $params['card_last_status'] = isset($data['card_last_status']) ? $data['card_last_status'] : 0;

        return $params ? $params : [];
    }

    /**
     * 天创验证返回错误提示信息
     * @param array $params
     * @return mixed
     */
    public static function getTianErrorMeg($params = [])
    {
        //result Int 认证结果 1 认证成功 2 认证失败 3 未认证 4 已注销
        if ($params['result'] == 1) {
            return $data = ['error_meg' => $params['resultMsg'], 'error_code' => $params['result']];
        } else {
            return $data = ['error_meg' => $params['detailMsg'], 'error_code' => $params['result']];
        }
    }

}
<?php

namespace App\Http\Controllers\V1;

use App\Events\V1\CardApplyEvent;
use App\Events\V1\ShadowCardApplyEvent;
use App\Models\Factory\UserFactory;
use App\Models\Orm\UserAuth;
use Illuminate\Http\Request;
use App\Helpers\UserAgent;
use App\Http\Controllers\Controller;
use App\Models\Factory\CreditcardFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\ShadowFactory;
use App\Helpers\Utils;

class BankCardDataController extends Controller
{
    // 信用卡申请统计
    public function applyCount(Request $request)
    {
        // card_id
        $cardId = $request->input('card_id');
        $url = $request->input('card_link');
        $token = $request->input('token') ?: $request->header('X-Token');
        $shadow_nid = $request->input('shadow_nid');

        if ($token)
        {
            $user = UserAuth::where('accessToken', $token)->first();
        }
        $userId = isset($user) ? $user->sd_user_id : 0;

        // shadow_nid 值设置
        /**
         * 1.登录马甲=shadow_nid 2.未登录马甲=jdt 3.登录速贷=jdt 4.未登录速贷=jdt
         */
        if (!empty($userId))
        {
            // 登录马甲
            if (!empty($shadow_nid))
            {
                // 获取shadow id
                $shadowId = ShadowFactory::getShadowId($userId);
                // 获取shadow nid
                $shadow_nid = ShadowFactory::getShadowNid($shadowId);
            }
            else
            {
                //登录速贷
                $shadow_nid = 'jdt';
            }
        }
        else
        {
            // 未登录状态一律默认jdt
            $shadow_nid = 'jdt';
        }

        // 获取信用卡信息
        $creditcardArr = CreditcardFactory::fetchCreditCard($cardId);

        $data['card_id'] = $cardId;
        $data['bank_id'] = $creditcardArr['bank_id'];
        $data['card_name'] = $creditcardArr['card_name'];
        $data['created_at'] = date('Y-m-d H:i:s', time());
        $data['user_ip'] = Utils::ipAddress();
        $data['user_agent'] = UserAgent::i()->getUserAgent();
        $data['shadow_nid'] = $shadow_nid;
        // 获取跳转url
        if (empty($url)) {
            $url = $creditcardArr['card_h5_link'];
        } else {
            $url = urldecode($url);
        }

        if (empty($userId))
        {
            // 事件处理:信用卡申请点击流水统计 & 申请增量
            if (empty($shadow_nid))
            {
                event(new CardApplyEvent($data));
            }
            else
            {
                event(new ShadowCardApplyEvent($data));
            }
            header('Location:' . $url);
        }
        else
        {
            // 获取渠道id
            $deliveryId = DeliveryFactory::fetchShadowDeliveryId($userId);
            //获取渠道信息
            $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);
            //获取用户名&手机号
            $userInfo = UserFactory::fetchUserNameAndMobile($userId);

            if (empty($creditcardArr) || empty($deliveryArr) || empty($deliveryId)) {
                header('Location:' . $url);
            }

            // 马甲包的情况 如果查不到 直接跳转
            if (!empty($shadow_nid))
            {
                if (empty($shadowId) || empty($shadow_nid)) {
                    header('Location:' . $url);
                }
            }

            // 参数
            $data['channel_id'] = $deliveryArr['id'];
            $data['channel_title'] = $deliveryArr['title'];
            $data['channel_nid'] = $deliveryArr['nid'];
            $data['user_id'] = $userId;
            $data['username'] = $userInfo['username'];
            $data['mobile'] = $userInfo['mobile'];
            $data['shadow_nid'] = $shadow_nid;
            if (!empty($shadow_nid))
            {
                event(new ShadowCardApplyEvent($data));
            }
            else
            {
                // 事件处理:信用卡申请点击流水统计 & 申请增量
                event(new CardApplyEvent($data));
            }

            // 后台跳转卡片H5链接
            header('Location:' . $url);
        }
    }
}
<?php

namespace App\Http\Controllers\Shadow\V1;

use App\Constants\CreditConstant;
use App\Events\V1\AddIntegralEvent;
use App\Events\Shadow\UserShadowEvent;
use App\Http\Controllers\Controller;
use App\Events\V1\UserLoginEvent;
use App\Events\V1\UserRegEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Models\Chain\QuickLogin\DoQuickLoginHandler;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\InviteFactory;
use App\Models\Factory\UserFactory;
use App\Models\Chain\Register\DoRegisterHandler;
use Illuminate\Http\Request;
use App\Strategies\UserStrategy;
use App\Models\Orm\DeliveryCount;

/**
 * Class AuthController
 * @package App\Http\Controllers\V2
 * 登录&注册
 */
class AuthController extends Controller
{
    /**
     * 验证码快捷登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickLogin(Request $request)
    {
        $data = $request->all();
        if (isset($data['channel_fr'])) {
            $delivery = DeliveryCount::where('nid', '=', $data['channel_fr'])->first();
            if (!$delivery) {
                $data['channel_fr'] = 'shadow_default';
            }

            if (strpos($data['channel_fr'], 'shadow') === false)
            {
                $data['channel_fr'] = 'shadow_default';
            }
        }

        #查库检查用户手机号是否存在并且activated 是否为0
        $user = UserFactory::getMobileAndIndent($data['mobile']);
        // 默认是0 非新用户
        $is_new = 0;

        if ($user) {
            #如果用户激活则调用登录责任链
            $login = new DoQuickLoginHandler($data);
            $re = $login->handleRequest();
            if (isset($re['error'])) {
                if ($re['code'] == 403403) {
                    return RestResponseFactory::forbidden($re['error'], 403, $re['error']);
                }
                return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
            }
            #添加登录事件监听
            $event_data = [];
            event(new UserLoginEvent($event_data));
        } else {
            #如果用户未激活调用注册责任链  加积分
            $register = new DoRegisterHandler($data);
            $re = $register->handleRequest();
            if (isset($re['error'])) {
                return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
            }
            #添加注册邀请好友事件监听
            $invite['mobile'] = $data['mobile'];
            #将当前用户的用户id传入事件
            $invite['invite_user_id'] = UserFactory::getIdByMobile($data['mobile']);
            $invite['user_id'] = isset($data['uid']) ? $data['uid'] : '';
            #判断有无uid传值
            if (!empty($invite['user_id'])) {
                #通过uid邀请的(生成code码记录并赋值给sd_invite_code)
                $invite['sd_invite_code'] = InviteFactory::fetchInviteCode($invite['user_id']);
            } else {
                #原生code码邀请
                $invite['sd_invite_code'] = isset($data['sd_invite_code']) ? $data['sd_invite_code'] : '';
            }
            #添加注册渠道数据事件监听
            $count['channel_fr'] = !empty($data['channel_fr']) ? $data['channel_fr'] : 'shadow_default';
            $count['userId'] = $invite['invite_user_id'];
            $count['version'] = isset($data['version']) ? $data['version'] : UserStrategy::version();
            $count['shadow_nid'] = isset($data['shadowNid']) ? $data['shadowNid'] : 'sudaizhijia';
            event(new UserRegEvent(['invite' => $invite, 'count' => $count]));
            $is_new = 1;

            //新注册用户 加积分
            $eventData['typeNid'] = CreditConstant::ADD_INTEGRAL_USER_REGISTER_TYPE;
            $eventData['remark'] = CreditConstant::ADD_INTEGRAL_USER_REGISTER_REMARK;
            $eventData['typeId'] = CreditFactory::fetchIdByTypeNid($eventData['typeNid']);
            $eventData['score'] = CreditFactory::fetchScoreByTypeNid($eventData['typeNid']);
            $eventData['userId'] = $invite['invite_user_id'];
            event(new AddIntegralEvent($eventData));
        }

        //马甲产品监听
        $shadow['is_new'] = $is_new;
        $shadow['user_id'] = UserFactory::getIdByMobile($data['mobile']);
        $shadow['shadow_nid'] = isset($data['shadowNid']) ? $data['shadowNid'] : '';
        $shadow['terminal_type'] = isset($data['version']) ? $data['version'] : UserStrategy::version();
        $shadow['channel_nid'] = !empty($data['channel_fr']) ? $data['channel_fr'] : 'shadow_default';

        event(new UserShadowEvent($shadow));
        return RestResponseFactory::ok($re);
    }
}
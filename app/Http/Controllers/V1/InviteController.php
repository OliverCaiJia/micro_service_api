<?php
namespace App\Http\Controllers\V1;

use App\Constants\ConfigConstant;
use App\Helpers\DateUtils;
use App\Helpers\LinkUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\ConfigFactory;
use App\Models\Factory\InviteFactory;
use App\Models\Factory\UserFactory;
use App\Models\Orm\SystemConfig;
use App\Strategies\InviteStrategy;
use App\Strategies\SmsStrategy;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Constants\InviteConstant;

/**
 * Class InviteController
 * @package App\Http\Controllers\V1
 * 邀请
 */
class InviteController extends Controller
{
    /**
     * @param Request $request
     * 用户邀请信息
     */
    public function fetchInvites(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        //用户名
        $inviteArr['username'] = UserFactory::fetchUserName($userId);
        //额外金额
        $inviteArr['extra_money'] = ConfigFactory::getExtraData(ConfigConstant::CONFIG_EXTRA);
        //分享链接        
        $inviteCode = InviteFactory::fetchInviteCode($userId);
        $inviteArr['share_link'] = LinkUtils::shareLanding($inviteCode);
        //短信内容
        $inviteArr['sms_content'] = SmsStrategy::getSmsContent($inviteArr['share_link']);
        return RestResponseFactory::ok($inviteArr);
    }

    /**
     * @return mixed
     * 生成二维码
     */
    public function fetchQrcode(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        $sizeArr = $request->all();
        //邀请码
        $inviteCode = InviteFactory::fetchInviteCode($userId);
        //扫码链接
        $landig = LinkUtils::shareLanding($inviteCode);
        return QrCode::size($sizeArr['size'])->generate($landig);
    }

    /**
     * @param Request $request
     * 用户邀请流水
     */
    public function fetchInviteLog(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;

        //邀请流水
        $logArr = InviteFactory::fetchInviteLogData($data, $userId);
        $logArr = InviteStrategy::toStatusStr($logArr);

        return RestResponseFactory::ok($logArr);
    }

    /**
     * @param Request $request
     * 短信邀请
     */
    public function createSmsInvite(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        $data = $request->all();
        $data['userId'] = $userId;
        //SLogger::getStream()->info('发短信', $data);
        //判断手机号是否已经注册
        $userArr = UserFactory::fetchUserByMobile($data['mobile']);
        //查询短信邀请流水表
        $inviteLog = InviteFactory::fetchInviteLog($data);

        if (empty($userArr) && empty($inviteLog)) {
            //生成短信邀请流水
            $inviteCode = InviteFactory::fetchInviteCode($userId);
            $datas['userId'] = $userId;
            $datas['invite_user_id'] = 0;
            $datas['mobile'] = $data['mobile'];
            $datas['sd_invite_code'] = $inviteCode;
            $datas['from'] = InviteConstant::INVITE_FROM_SHARE;
            $datas['status'] = InviteConstant::INVITE_ING;
            InviteFactory::createInviteLog($datas);
        }
        return RestResponseFactory::ok(RestUtils::getStdObj());
    }


}
<?php

namespace App\Http\Controllers\V2;

use App\Constants\CreditConstant;
use App\Constants\UserConstant;
use App\Constants\UserVipConstant;
use App\Events\V1\AddIntegralEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\AccountFactory;
use App\Models\Factory\BankcardFactory;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserSignFactory;
use App\Models\Factory\UserVipFactory;
use App\Services\Core\Store\Qiniu\Qiniu\QiniuUpload;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Services\Core\WangYiYunDun\CloudShield\CloudShieldService;
use App\Strategies\UserStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    function index(Request $request)
    {
        return RestResponseFactory::ok(null, 'OK');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 修改用户名
     */
    public function updateUsername(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['username'] = $request->input('username');

        //判断用户名的唯一性
        $username = UserFactory::fetchUsernameByName($data);
        if ($username) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1120), 1120);
        }else{
            echo 11;die;
            CloudShieldService::TextMain($data['userId'],$data['username']);
        }

        $res = UserFactory::updateUsername($data);
        if (!$res) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 修改用户头像
     */
    public function uploadPhoto(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        // 上传文件
        $file = $request->file('file');

        // 目标文件夹
        $data['prefix'] = UserConstant::USER_PHOTO_PREFIX;
        // 文件名称
        $filename_path = QiniuUpload::uploadFile($file, $data);

        if ($filename_path) {
            $image = CloudShieldService::PhotoMain(QiniuService::QINIU_URL . $filename_path);
            if ($image == 0) {
                $data['userPhoto'] = $filename_path;
                //保存头像
                $info = UserFactory::createOrUpdatePhoto($data);
                if ($info) {
                    $datas['photo'] = UserFactory::fetchUserPhotoById($data['userId']);

                    //添加加积分事件
                    //首次设置用户名加积分
                    $eventData['typeNid'] = CreditConstant::ADD_INTEGRAL_USER_PHOTO_TYPE;
                    $eventData['remark'] = CreditConstant::ADD_INTEGRAL_USER_PHOTO_REMARK;
                    $eventData['typeId'] = CreditFactory::fetchIdByTypeNid($eventData['typeNid']);
                    $eventData['score'] = CreditFactory::fetchScoreByTypeNid($eventData['typeNid']);
                    $eventData['userId'] = $data['userId'];
                    event(new AddIntegralEvent($eventData));

                    return RestResponseFactory::ok($datas);
                }
            }
            else {
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2000), 2000);
            }

        }

        return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2000), 2000);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 用户账户信息
     */
    public function fetchUserinfo(Request $request)
    {
        //通過Token獲取用戶ID
        $userId = $request->user()->sd_user_id;
        $data['userId'] = $userId;
        //账号余额
        $data['userAccount'] = AccountFactory::fetchBalance($userId);
        //账号积分
        $data['userScore'] = CreditFactory::fetchCredit($userId);
        //用户信息
        $data['userProfile'] = UserFactory::fetchRealNameAndSex($userId);
        //用户信息
        $data['userAuth'] = UserFactory::fetchUserNameAndMobile($userId);
        //用户头像
        $data['userinfo'] = UserFactory::fetchPhotoById($userId);
        //用户是否签到
        $data['user_sign'] = UserSignFactory::fetchUserSignByUserId($userId);
        //查询身份证认证情况
        $data['alive_status'] = 1;
        $alive = UserIdentityFactory::fetchUserAliveStatusById($data);
        $data['realname'] = empty($alive) ? [] : UserIdentityFactory::fetchIdcardAuthenInfo($userId);
        //查询绑卡情况
        $params['userId'] = $userId;
        $data['bankcardCount'] = UserBankCardFactory::fetchUserBanksCount($params);
        //会员状态
        $vipTypeId = UserVipFactory::getVipTypeId();
        $data['vip'] = UserVipFactory::getVIPInfo($data['userId'], $vipTypeId);
        //会员特权个数
        //特权类型主id
        $priData['priTypeId'] = UserVipFactory::fetchVipPrivilegeIdByNid(UserVipConstant::VIP_PRIVILEGE_UPGRADE);
        //根据会员查询对应的特权列表ids
        $priData['privilegeIds'] = UserVipFactory::getVipPrivilegeIds($vipTypeId);
        $data['vipPrivilegeCount'] = UserVipFactory::fetchVipPrivilegeCount($priData);

        $info = UserStrategy::fetchUserautheninfo($data);

        return RestResponseFactory::ok($info);
    }

}

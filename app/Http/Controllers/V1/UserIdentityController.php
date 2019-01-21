<?php

namespace App\Http\Controllers\V1;

use App\Constants\UserIdentityConstant;
use App\Helpers\LinkUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\UserIdentity\Alive\DoAliveHandler;
use App\Models\Chain\UserIdentity\FaceAlive\DoFaceAliveHandler;
use App\Models\Chain\UserIdentity\IdcardBack\DoIdcardBackHandler;
use App\Models\Chain\UserIdentity\IdcardFront\DoIdcardFrontHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Services\Core\Validator\TianChuang\TianChuangService;
use App\Strategies\UserIdentityStrategy;
use Illuminate\Http\Request;

/**
 * Class UserAuthenController
 * @package APP\Http\Controllers\V1
 * 用户身份信息认证
 */
class UserIdentityController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 调取face++获取身份证正面信息
     */
    public function fetchFaceidToCardfrontInfo(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        // 身份证正面图片以及身份证正面头像图片
        $data['card_front'] = $request->file('cardFront');
        $data['card_photo'] = $request->file('cardPhoto');
        //责任链
        $realname = new DoIdcardFrontHandler($data);
        $res = $realname->handleRequest();

        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }

        return RestResponseFactory::ok($res);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 修改face返回的用户信息
     */
    public function updateUserRealnameByIdcardFront(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['realname'] = $request->input('realname', '');
        $data['sex'] = $request->input('sex', 0);
        $data['certificateNo'] = $request->input('certificateNo', '');
        //face++认证通过
        $data['status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FACE;
        //修改sd_user_realname表信息
        $realname = UserIdentityFactory::updateUserRealnameByIdcardFront($data);
        if (!$realname) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        return RestResponseFactory::ok(RestUtils::getStdObj());

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 调取face++获取身份证反面信息
     */
    public function fetchFaceidToCardbackInfo(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        // 反面身份证图片
        $data['card_back'] = $request->file('cardBack');
        //验证正面信息是否获取，提示先获取正面信息
        $data['face_status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FACE;
        $front = UserIdentityFactory::fetchIdcardinfoById($data);
        if (!$front) {
            //请先完成身份证正面扫描
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(12009), 12009);
        }
        //身份证反面信息责任链
        $tianCheck = new DoIdcardBackHandler($data);
        $res = $tianCheck->handleRequest();

        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 天创验证身份证合法信息
     */
    public function checkIdcardFromTianchuang(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;

        $data['face_status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FACE;
        $realname = UserIdentityFactory::fetchIdcardinfoById($data);
        //抱歉用户不存在
        if (!$realname) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(11128), 11128);
        }
        //天创验证
        $data['tianCheck'] = TianChuangService::authPersonalIdCard($realname['certificate_no'], $realname['realname']);
        //天创验证错身份证信息误提示
        $res = UserIdentityStrategy::getTianErrorMeg($data['tianCheck']);
        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }
        //天创验证流水数据处理
        $data['realname'] = $realname;
        $params = UserIdentityStrategy::getUserRealnameLogData($data);
        $log = UserIdentityFactory::createUserRealnameLog($params);
        ////抱歉，用户信息匹配失败
        if (!$log) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(11129), 11129);
        }
        // 认证状态 2天创通过
        $data['status'] = UserIdentityConstant::AUTHENTICATION_STATUS_TIAN;
        //根据用户id修改状态status值
        $realname = UserIdentityFactory::updateStatusById($data);
        //修改状态失败
        if (!$realname) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 活体认证
     */
    public function verifyFaceidToIdcard(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //有缘对比 最佳照片与全景照片
        $data['image_best'] = $request->file('imageBest');
        $data['image_env'] = $request->file('imageEnv');
        $data['delta'] = $request->input('delta', '');

        $data['face_status'] = UserIdentityConstant::AUTHENTICATION_STATUS_TIAN;
        $realname = UserIdentityFactory::fetchIdcardinfoById($data);
        //该用户以验证过身份信息
        if (!$realname || empty($realname['card_front']) || empty($realname['card_photo'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(11128), 11128);
        }
        //活体认证需要数据
        $data = UserIdentityStrategy::getAliveNeedDatas($data, $realname);
        //活体验证记录责任链
        $alive = new DoAliveHandler($data);
        $res = $alive->handleRequest();
        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }
        //查询活体扫描图片
        $aliveData['alive_status'] = 0;
        $aliveData['userId'] = $data['userId'];
        $aliveImg = UserIdentityFactory::fetchUserAliveStatusById($aliveData);
        $data['image_best_url'] = QiniuService::getImgToFace($aliveImg['alive_photo_near']);
        $data['image_best'] = $aliveImg['alive_photo_near'];
        $data['image_env_url'] = QiniuService::getImgToFace($aliveImg['alive_photo_far']);
        $data['image_env'] = $aliveImg['alive_photo_far'];

        //face++认证活体同步数据记录
        $faceAlive = new DoFaceAliveHandler($data);
        $faceRes = $faceAlive->handleRequest();
        if (isset($faceRes['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $faceRes['error'], $faceRes['code'], $res['error']);
        }

        return RestResponseFactory::ok($faceRes['info']);
    }

}
<?php

namespace App\Models\Chain\UserIdentity\IdcardFront;

use App\Constants\UserIdentityConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Services\Core\Validator\FaceId\FaceIdService;
use App\Strategies\UserIdentityStrategy;
use App\Models\Chain\UserIdentity\IdcardFront\CheckFaceByDetectAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 2.调用face++获取身份证正面信息
 */
class FetchFaceidToCardfrontInfoAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '验证失败，请使用身份证照片！', 'code' => 10003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 2.调用face++获取身份证正面信息
     */
    public function handleRequest()
    {
        if ($this->fetchFaceidToCardfrontInfo($this->params) == true) {
            $this->setSuccessor(new CheckFaceByDetectAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 2.调用face++获取身份证正面信息
     */
    private function fetchFaceidToCardfrontInfo($params)
    {
        $image = QiniuService::getImgs($params['card_front']);
        $faceinfo = FaceIdService::fetchOcriIdcardToInfo($image);
        //检查用户名&身份证是否已存在，并且身份证在有效期内，一张身份证只能被使用一次
//        $realname = UserIdentityFactory::fetchUserRealnameByIdcard($faceinfo);
//        if ($realname && strtotime($realname['card_endtime']) >= time()) {
//            $this->error = ['error' => RestUtils::getErrorMessage(12008), 'code' => 12008];
//            return false;
//        }
        //验证身份证号是否存在
        $params['id_card_number'] = $faceinfo['id_card_number'];
        $isInfo = UserIdentityFactory::checkUseByIdCard($params);
        $certificateBackup = UserIdentityFactory::fetchCertificateBackupById($params['userId']);
        if ($isInfo) {
            $this->error = ['error' => RestUtils::getErrorMessage(12008), 'code' => 12008];
            return false;
        } elseif ($certificateBackup && $faceinfo['id_card_number'] != $certificateBackup) {
            //已过期身份 再次扫描只能是同一个身份证号
            $this->error = ['error' => RestUtils::getErrorMessage(12010), 'code' => 12010];
            return false;
        }

        //身份证识别精度判断
        if (!$faceinfo['legality']) {
            return false;
        } elseif ($faceinfo['legality']['Temporary ID Photo'] > UserIdentityConstant::ID_CARD_LEGALITY_VALUE) {
            //不支持临时身份证
            $this->error = ['error' => RestUtils::getErrorMessage(12007), 'code' => 12007];
            return false;
        }
        $idcardLegality = $faceinfo['legality']['ID Photo'];

        if (!$faceinfo || !$faceinfo['legality'] || $idcardLegality < UserIdentityConstant::ID_CARD_LEGALITY_VALUE) {
            return false;
        }
        //face++返回正面数据验证
        $errorData = UserIdentityStrategy::getIdcardFrontErrorMeg($faceinfo);
        if (isset($errorData['error'])) {
            $this->error = $errorData;
            return false;
        }

        $this->params['faceinfo'] = $faceinfo;
        return true;
    }

}

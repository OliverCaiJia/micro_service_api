<?php

namespace App\Models\Chain\UserIdentity\IdcardBack;

use App\Constants\UserIdentityConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Services\Core\Validator\FaceId\FaceIdService;
use App\Models\Chain\UserIdentity\IdcardBack\CreateUserRealnamLogAction;
use App\Strategies\UserIdentityStrategy;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 2.调用face++获取身份证反面信息
 */
class FetchFaceidToCardbackInfoAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '验证失败，请使用身份证照片！', 'code' => 10002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 2.调用face++获取身份证反面信息
     */
    public function handleRequest()
    {
        if ($this->fetchFaceidToCardfrontInfo($this->params) == true) {
            $this->setSuccessor(new CreateUserRealnamLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 2.调用face++获取身份证反面信息
     */
    private function fetchFaceidToCardfrontInfo($params)
    {
        $image = QiniuService::getImgs($params['card_back']);
        $faceinfo = FaceIdService::fetchOcriIdcardToInfo($image);
        //身份证识别精度判断
        $idcardLegality = isset($faceinfo['legality']['ID Photo']) ? $faceinfo['legality']['ID Photo'] : $faceinfo['legality']['Temporary ID Photo'];

        if (!$faceinfo || !$faceinfo['legality'] || $idcardLegality < UserIdentityConstant::ID_CARD_LEGALITY_VALUE) {
            return false;
        }
        //face++返回反面数据验证
        $errorData = UserIdentityStrategy::getIdcardBackErrorMeg($faceinfo);
        if (isset($errorData['error'])) {
            $this->error = $errorData;
            return false;
        }

        $this->params['faceinfo'] = $faceinfo;
        return true;
    }

}

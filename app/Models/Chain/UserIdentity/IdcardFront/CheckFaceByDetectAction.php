<?php

namespace App\Models\Chain\UserIdentity\IdcardFront;

use App\Constants\UserIdentityConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Services\Core\Validator\FaceId\FaceIdService;
use App\Models\Chain\UserIdentity\IdcardFront\CreateUserRealnamLogAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 3.验证头像是否被遮挡
 */
class CheckFaceByDetectAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '头像不清晰，请重新扫描！', 'code' => 10004);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 验证头像是否被遮挡
     */
    public function handleRequest()
    {
        if ($this->checkFaceByDetect($this->params) == true) {
            $this->setSuccessor(new CreateUserRealnamLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     * 验证头像是否被遮挡
     */
    private function checkFaceByDetect($params = [])
    {
        $data['image'] = QiniuService::getImgs($params['card_front']);
        //此接口检测一张照片中的人脸，并且将检测出的人脸保存到FaceID平台里，便于后续的人脸比对
        $res = FaceIdService::detect($data);
        $face = isset($res['faces']) ? $res['faces'] : [];
        //返回错误信息
        //quality  每一个人脸都会有一个质量判断的分数。
        //quality_threshold	表示人脸质量基本合格的一个阈值，超过该阈值的人脸适合用于人脸比对。
        //只能识别出人脸，人脸被大面积遮挡的时候可以识别出，小面积遮挡识别不出，做范围限制没有意义
        $quality = UserIdentityConstant::ID_CARD_QUALITY_VALUE;
        if (empty($face)) {
            return false;
        } elseif ((float)$face[0]['quality'] < (float)$face[0]['quality_threshold']) {
            return false;
        }

        return true;
    }

}

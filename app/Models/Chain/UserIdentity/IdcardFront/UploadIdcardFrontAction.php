<?php

namespace App\Models\Chain\UserIdentity\IdcardFront;

use App\Constants\UserConstant;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Store\Qiniu\Qiniu\QiniuUpload;
use App\Models\Chain\UserIdentity\IdcardFront\UploadIdcardPhotoAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 1.获取app端传的图片，并上传至七牛
 */
class UploadIdcardFrontAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '图片获取失败，请重试！', 'code' => 10001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.获取app端传的图片，并上传至七牛
     */
    public function handleRequest()
    {
        if ($this->uploadIdcardFront($this->params) == true) {
            $this->setSuccessor(new UploadIdcardPhotoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 获取app端传的图片，并上传至七牛,上传身份证正面照
     */
    private function uploadIdcardFront($params =[])
    {
        // 目标文件夹
        $data['prefix'] = UserConstant::USER_ID_CARD_PREFIX;
        // 身份证正面文件名
        $data['filename'] = 'sd_idcard_front_' . date('YmdHis') . '-' . $params['userId'];
        // 文件名称
        $data['file'] = $params['card_front'];
        $filename_path = QiniuUpload::customUploadFile($data);
        $this->params['card_front'] = $filename_path;
        //上传七牛失败
        if (!$filename_path) {
            return false;
        }

        return true;
    }

}

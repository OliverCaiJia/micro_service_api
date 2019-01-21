<?php

namespace App\Models\Chain\UserIdentity\IdcardFront;

use App\Constants\UserConstant;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Store\Qiniu\Qiniu\QiniuUpload;
use App\Models\Chain\UserIdentity\IdcardFront\FetchFaceidToCardfrontInfoAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 2.获取app端传的图片，并上传至七牛.上传身份证大头像
 */
class UploadIdcardPhotoAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '图片获取失败，请重试！', 'code' => 10002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 2.获取app端传的图片，并上传至七牛.上传身份证大头像
     */
    public function handleRequest()
    {
        if ($this->uploadIdcardPhoto($this->params) == true) {
            $this->setSuccessor(new FetchFaceidToCardfrontInfoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 获取app端传的图片，并上传至七牛,上传身份证大头像
     */
    private function uploadIdcardPhoto($params = [])
    {
        // 目标文件夹
        $data['prefix'] = UserConstant::USER_ID_CARD_PREFIX;
        // 身份证正面文件名
        $data['filename'] = 'sd_idcard_photo_' . date('YmdHis') . '-' . $params['userId'];
        // 文件名称
        $data['file'] = $params['card_photo'];
        $filename_path = QiniuUpload::customUploadFile($data);
        $this->params['card_photo'] = $filename_path;
        //上传七牛失败
        if (!$filename_path) {
            return false;
        }

        return true;
    }

}

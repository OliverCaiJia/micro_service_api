<?php

namespace App\Models\Chain\UserIdentity\Alive;

use App\Constants\UserConstant;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Store\Qiniu\Qiniu\QiniuUpload;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Models\Chain\UserIdentity\Alive\UploadMegliveEnvAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 1.上传最佳照片
 */
class UploadMegliveBestAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '最佳照片上传失败！', 'code' => 10001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 上传最佳照片
     */
    public function handleRequest()
    {
        if ($this->uploadMegliveBest($this->params) == true) {
            $this->setSuccessor(new UploadMegliveEnvAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 上传最佳照片
     */
    private function uploadMegliveBest($params)
    {
        // 目标文件夹
        $data['prefix'] = UserConstant::USER_ALIVE_PREFIX;
        // 活体认证最佳照片
        $data['filename'] = 'sd_alive_best_' . date('YmdHis') . '-' . $params['userId'];
        // 文件名称
        $data['file'] = $params['image_best'];
        $filename_path = QiniuUpload::customUploadFile($data);
        $this->params['image_best'] = $filename_path;
        //上传七牛失败
        if (!$filename_path) {
            return false;
        }

        return true;
    }

}

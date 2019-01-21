<?php

namespace App\Services\Core\Store\Qiniu\Qiniu;

use App\Helpers\RestResponseFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class QiniuUpload extends QiniuService
{

    /**
     * 上传图片到七牛
     * @param $file
     * @param $field
     * @param $prefix
     * @param $model
     * @return \Illuminate\Http\JsonResponse
     */
    public static function uploadImg($file, $field, $prefix, $model)
    {
        if (!empty($file)) {
            if (!$file->isValid()) {
                return back();
            }
            // 需要填写你的 Access Key 和 Secret Key
            $accessKey = config('sudai.qiniu.ak');
            $secretKey = config('sudai.qiniu.sk');
            // 构建鉴权对象
            $auth = new Auth($accessKey, $secretKey);
            // 要上传的空间
            $bucket = config('sudai.qiniu.bucket');
            // 生成上传 Token
            $token = $auth->uploadToken($bucket);
            // 要上传文件的本地路径
            $filePath = $file->getRealPath();
            // 上传到七牛后保存的文件名
            $date = date('YmdHis') . '-' . rand(100, 1000);
            // 存储路径
            $local_path = self::ENV_QINIU_PATH . date('Ymd') . '/' . $prefix . $date . '.' . $file->getClientOriginalExtension();
            // 初始化 UploadManager 对象并进行文件的上传。
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传。
            list($ret, $err) = $uploadMgr->putFile($token, $local_path, $filePath);
            if ($err !== null) {
                return response()->json(['ResultData' => '失败', 'info' => '失败']);
            } else {
                $model->$field = $local_path;
            }
        }
    }

    /**
     *  异步上传图片
     * @param type $file
     * @param type $prefix
     * @return boolean|string
     */
    public static function uploadFile($file, $data = [])
    {
        if (!empty($file)) {
            if (!$file->isValid()) {
                return false;
            }
            //扩展名
            $ext = empty($file->getClientOriginalExtension()) ? 'jpg' : $file->getClientOriginalExtension();
            // 需要填写你的 Access Key 和 Secret Key
            $accessKey = config('sudai.qiniu.ak');
            $secretKey = config('sudai.qiniu.sk');
            // 构建鉴权对象
            $auth = new Auth($accessKey, $secretKey);
            // 要上传的空间
            $bucket = config('sudai.qiniu.bucket');
            // 生成上传 Token
            $token = $auth->uploadToken($bucket);
            // 要上传文件的本地路径
            $filePath = $file->getRealPath();
            // 上传到七牛后保存的文件名
            $date = date('YmdHis') . '-' . $data['userId'];
            // 存储路径
            $local_path = self::ENV_QINIU_PATH . date('Ymd') . '/' . $data['prefix'] . '/' . $date . '.' . $ext;
            // 初始化 UploadManager 对象并进行文件的上传。
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传。
            list($ret, $err) = $uploadMgr->putFile($token, $local_path, $filePath);
            if (empty($err)) {
                return $local_path;
            }
        }
        return false;
    }

    /**
     *  自定义异步上传图片
     * @param type $file
     * @param type $prefix
     * @return boolean|string
     */
    public static function customUploadFile($data = [])
    {
        $file = $data['file'];
        if (!empty($file)) {
            if (!$file->isValid()) {
                return false;
            }
            //扩展名
            $ext = empty($file->getClientOriginalExtension()) ? 'jpg' : $file->getClientOriginalExtension();
            // 需要填写你的 Access Key 和 Secret Key
            $accessKey = config('sudai.qiniu.ak');
            $secretKey = config('sudai.qiniu.sk');
            // 构建鉴权对象
            $auth = new Auth($accessKey, $secretKey);
            // 要上传的空间
            $bucket = config('sudai.qiniu.bucket');
            // 生成上传 Token
            $token = $auth->uploadToken($bucket);
            // 要上传文件的本地路径
            $filePath = $file->getRealPath();
            // 上传到七牛后保存的文件名
            $date = $data['filename'];
            // 存储路径
            $local_path = self::ENV_QINIU_PATH . date('Ymd') . '/' . $data['prefix'] . '/' . $date . '.' . $ext;
            // 初始化 UploadManager 对象并进行文件的上传。
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传。
            list($ret, $err) = $uploadMgr->putFile($token, $local_path, $filePath);
            if (empty($err)) {
                return $local_path;
            }
        }
        return false;
    }

}

<?php

namespace App\Services\Core\Store\Qiniu\Qiniu;

use App\Services\AppService;
use App\Services\Core\Store\Qiniu\qiniu\Qiniu_RS_GetPolicy;
use App\Services\Core\Store\Qiniu\qiniu\Qiniu_RS_PutPolicy;

/**
 * Class QiniuService
 * @package App\Services\Core\Qiniu
 * 七牛
 */
class QiniuService extends AppService
{

    private static $instance;
    private $bucket;
    private $prefix;
    private $domain;

    public function __construct()
    {
        $this->bucket = config('sudai.qiniu.bucket');
        $this->prefix = config('sudai.qiniu.prefix');
        $this->domain = config('sudai.qiniu.domain');
    }

    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function upToken()
    {

        $putPolicy = new Qiniu_RS_PutPolicy($this->bucket);
        $upToken = $putPolicy->Token(null);

        return $upToken;
    }

    public function baseUrl($key)
    {
        $baseUrl = Qiniu_RS_MakeBaseUrl($this->domain, $key);
        return $baseUrl;
    }

    public function privateUrl($key)
    {
        $baseUrl = Qiniu_RS_MakeBaseUrl($this->domain, $key);
        $getPolicy = new Qiniu_RS_GetPolicy();
        $privateUrl = $getPolicy->MakeRequest($baseUrl, null);
        return $privateUrl;
    }

    /**
     * @desc 根据前缀批量获取keys
     * @param $marker 为服务器上次导出时返回的标记，没有可以不填
     */
    public function listPrefix($prefix = '', $marker = '', $limit = '')
    {
        require_once "qiniu/rsf.php";

        $client = new Qiniu_MacHttpClient(null);

        list($items, $markerOut, $err) = Qiniu_RSF_ListPrefix($client, $this->bucket, $prefix, $marker, $limit);

        if ($err != null)
        {
            if ($err === Qiniu_RSF_EOF)
            {
                // 最后结果, 没有后续数据
                return array('items' => $items, 'marker' => $markerOut);
            }
            else
            {
                // 错误记录日志
                $message = "Qiniu::listPrefix error: " . var_export($err, true);
                JLog::log($message, 'qiniu');
                return false;
            }
        }
        else
        {
            // 数据未去完，还有后续结果
            return array('items' => $items, 'marker' => $markerOut);
        }
    }

    public function stat($key)
    {
        $client = new Qiniu_MacHttpClient(null);

        list($ret, $err) = Qiniu_RS_Stat($client, $this->bucket, $key);

        if ($err !== null)
        {
            $message = "Qiniu::stat error: " . var_export($err, true);
            JLog::log($message, 'qiniu');
            return false;
        }

        return $ret;
    }

    /**
     * @desc 批量获取信息
     */
    public function batchStat($keyArray)
    {
        foreach ($keyArray as $k => $v) {
            $entries[$k] = new Qiniu_RS_EntryPath($this->bucket, $v);
        }

        $client = new Qiniu_MacHttpClient(null);

        list($ret, $err) = Qiniu_RS_BatchStat($client, $entries);
        if ($err !== null)
        {
            $message = "Qiniu::batchStat error: " . var_export($err, true);
            JLog::log($message, 'qiniu');
            return false;
        }

        return $ret;
    }

    public function copy($key, $dst)
    {
        $client = new Qiniu_MacHttpClient(null);

        $err = Qiniu_RS_Copy($client, $this->bucket, $key, $this->bucket, $dst);

        if ($err !== null)
        {
            $message = "Qiniu::copy error: " . var_export($err, true);
            JLog::log($message, 'qiniu');
            return false;
        }

        return true;
    }

    public function move($key, $newKey)
    {
        $client = new Qiniu_MacHttpClient(null);

        $err = Qiniu_RS_Move($client, $this->bucket, $key, $this->bucket, $newKey);

        if ($err !== null)
        {
            $message = "Qiniu::move error: " . var_export($err, true);
            JLog::log($message, 'qiniu');
            return false;
        }

        return true;
    }

    public function delete($key)
    {
        $client = new Qiniu_MacHttpClient(null);

        $err = Qiniu_RS_Delete($client, $this->bucket, $key);

        if ($err !== null)
        {
            $message = "Qiniu::delete error: " . var_export($err, true);
            JLog::log($message, 'qiniu');
            return false;
        }

        return true;
    }

    /**
     * @desc 批量删除
     */
    public function batchDelete($keyArray)
    {
        foreach ($keyArray as $k => $v) {
            $entries[$k] = new Qiniu_RS_EntryPath($this->bucket, $v);
        }

        $client = new Qiniu_MacHttpClient(null);

        list($ret, $err) = Qiniu_RS_BatchDelete($client, $entries);

        if ($err !== null)
        {
            $message = "Qiniu::batchDelete error: " . var_export($err, true);
            JLog::log($message, 'qiniu');
            return false;
        }

        return $ret;
    }

    public function put($key, $string)
    {
        require_once "qiniu/io.php";

        $upToken = $this->upToken();

        list($ret, $err) = Qiniu_Put($upToken, $key, $string, null);

        if ($err !== null)
        {
            $message = "Qiniu::put error: " . var_export($err, true);
            JLog::log($message, 'qiniu');
            return false;
        }

        return true;
    }

    public function putFile($key, $filename)
    {
        require_once "qiniu/io.php";

        $upToken = $this->upToken();

        $putExtra = new Qiniu_PutExtra();
        $putExtra->Crc32 = 1;

        list($ret, $err) = Qiniu_PutFile($upToken, $key, $filename, $putExtra);

        if ($err !== null)
        {
            $message = "Qiniu::putFile error: " . var_export($err, true);
            JLog::log($message, 'qiniu');
            return false;
        }
        JLog::log($ret, 'qiniu', true);
        return true;
    }

    public function imgInfoUrl($key)
    {
        require_once "qiniu/fop.php";

        $baseUrl = $this->baseUrl($key);

        $imgInfo = new Qiniu_ImageInfo;
        $imgInfoUrl = $imgInfo->makeRequest($baseUrl);

        return $imgInfoUrl;
    }

    public function imgInfoPrivateUrl($key)
    {
        $imgInfoUrl = $this->imgInfoUrl($key);

        $getPolicy = new Qiniu_RS_getPolicy();
        $imgInfoPrivateUrl = $getPolicy->MakeRequest($imgInfoUrl, null);

        return $imgInfoPrivateUrl;
    }

    public function imgExifUrl($key)
    {
        require_once "qiniu/fop.php";

        $baseUrl = $this->baseUrl($key);

        $imgExif = new Qiniu_Exif();
        $imgExifUrl = $imgExif->MakeRequest($baseUrl);

        return $imgExifUrl;
    }

    public function imgExifPrivateUrl($key)
    {
        $imgExifUrl = $this->imgExifUrl($key);

        $getPolicy = new Qiniu_RS_GetPolicy();
        $imgExifPrivateUrl = $getPolicy->MakeRequest($imgExifUrl, null);

        return $imgExifPrivateUrl;
    }

    public function imgViewUrl($key, $width, $height, $quality = '', $format = '')
    {
        require_once "qiniu/fop.php";

        $baseUrl = $this->baseUrl($key);

        $imgView = new Qiniu_ImageView();
        $imgView->Mode = 1;
        $imgView->Width = $width;
        $imgView->Height = $height;
        $imgView->quality = $quality;
        $imgView->format = $format;
        $imgViewUrl = $imgView->MakeRequest($baseUrl);

        return $imgViewUrl;
    }

    public function imgViewPrivateUrl($key, $width, $height)
    {
        $imgViewUrl = $this->imgViewUrl($key, $width, $height);

        $getPolicy = new Qiniu_RS_GetPolicy();
        $imgViewPrivateUrl = $getPolicy->MakeRequest($imgViewUrl, null);

        return $imgViewPrivateUrl;
    }

}

<?php

namespace App\Services\Core\Store\Qiniu;

use App\Constants\UserConstant;
use App\Models\Orm\Platform;
use App\Models\Orm\PlatformProduct;
use App\Services\AppService;

/**
 * Class QiniuService
 * @package App\Services\Core\Qiniu
 * 七牛
 */
class QiniuService extends AppService
{

    const QINIU_URL = 'http://obd7ty4wc.bkt.clouddn.com/';

    /**
     * @return mixed
     * 获取七牛的地址
     */
    public static function getQiniuUrl()
    {
        return config('sudai.qiniu.baseurl', self::QINIU_URL);
    }

    /**
     * @param $key
     * @return string
     * 资讯图片  有默认图片
     */
    public static function getInfoImgs($param)
    {
        //图片不存在给默认值
        if ($param)
        {
            $img = self::getQiniuUrl() . $param;
        }
        else
        {
            $img = self::getQiniuUrl() . '默认130.png';
        }
        return $img;
    }

    /**
     * @param $param
     * @return string
     * 图片   没有默认图片
     */
    public static function getImgs($param)
    {
        if ($param)
        {
            $img = self::getQiniuUrl() . $param;
        }
        else
        {
            $img = '';
        }
        return $img;
    }

    /**
     * @param string $param
     * @param string $productId
     * @return string
     * 产品图图片
     */
    public static function getProductImgs($param, $productId)
    {
        if (!empty($param))
        {
            $img = self::getQiniuUrl() . $param;
        }
        elseif (!empty($productId))
        {
            $platform = PlatformProduct::where(['platform_product_id' => $productId])
                    ->select(['platform_id'])
                    ->first()->toArray();
            $platformLogo = Platform::where(['platform_id' => $platform['platform_id']])
                    ->select(['logo'])
                    ->first()->toArray();
            if ($platformLogo['logo'])
            {
                $img = self::getQiniuUrl() . $platformLogo['logo'];
            }
            else
            {
                $img = self::getQiniuUrl() . 'news/160808113650_788.jpg';
            }
        }
        else
        {
            $img = self::getQiniuUrl() . '默认130.png';
        }
        return $img;
    }

    /**
     * @param $param
     * @return string
     * 银行/信用卡logo
     */
    public static function getBankLogo($param)
    {
        if ($param)
        {
            $img = self::getQiniuUrl() . $param;
        }
        else
        {
            $img = '';
        }
        return $img;
    }

    /**
     * face++需要图片 不需要进行压缩处理
     * @param string $param
     * @return string
     */
    public static function getImgToFace($param = '')
    {
        if ($param)
        {
            $img = self::QINIU_URL . $param;
        }
        else
        {
            $img = '';
        }
        return $img;
    }

    /**
     * 用户头像图片
     * @param string $param
     * @return string
     */
    public static function getImgToPhoto($param = '')
    {
        if ($param)
        {
            $img = self::QINIU_URL . $param;
        }
        else
        {
            $img = UserConstant::USER_PHOTO_DEFAULT;
        }
        return $img;
    }

    /**
     * 网贷产品默认logo
     * @param string $param
     * @return string
     */
    public static function getImgToBillProduct($param = '')
    {
        if ($param)
        {
            $img = self::QINIU_URL . $param;
        }
        else
        {
            $img = 'http://image.jdt.com/production/20180129/platform/20180129152855-211.png';
        }
        return $img;
    }

    /**
     * 网贷账单默认水印logo
     * @param string $param
     * @return string
     */
    public static function getWatermarkImgToBillProduct($param = '')
    {
        if ($param)
        {
            $img = self::QINIU_URL . $param;
        }
        else
        {
            $img = 'http://image.jdt.com/production/20180129/platform/20180129160557-774.png';
        }
        return $img;
    }
}

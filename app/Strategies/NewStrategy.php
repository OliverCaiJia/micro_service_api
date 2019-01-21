<?php

namespace App\Strategies;

use App\Constants\CreditConstant;
use App\Constants\NewsConstant;
use App\Helpers\DateUtils;
use App\Helpers\LinkUtils;
use App\Helpers\RestUtils;
use App\Services\Core\Store\Qiniu\QiniuService;

/**
 * 资讯公共策略
 *
 * @package App\Strategies
 */
class NewStrategy extends AppStrategy
{
    /**
     * @param $data
     * @return bool
     * 资讯接收数据处理
     */
    public static function getData($data)
    {
        if ($data['newsType'] == NewsConstant::SUDAI_ACTIVITY) {
            $data['themeId']  = NewsConstant::SUDAI_ACTIVITY;
            $data['pageSize'] = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
            $data['pageNum']  = isset($data['pageNum']) ? intval($data['pageNum']) : 3;
        } elseif ($data['newsType'] == NewsConstant::SUDAI_COMMON) {
            $data['themeId']  = NewsConstant::SUDAI_COMMON;
            $data['pageSize'] = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
            $data['pageNum']  = isset($data['pageNum']) ? intval($data['pageNum']) : 100;
        } elseif ($data['newsType'] == NewsConstant::SUDAI_GUIDE) {
            $data['themeId']  = NewsConstant::SUDAI_GUIDE;
            $data['pageSize'] = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
            $data['pageNum']  = isset($data['pageNum']) ? intval($data['pageNum']) : 10;
        }
        return $data ? $data : [];
    }

    /**
     * @param $newsArr
     * 资讯数据规整
     */
    public static function getNewsData($themeNameId, $newsLists)
    {
        $newsArr = $newsLists['list'];
        $datas   = [];
        foreach ($newsArr as $key => $val) {
            $datas[$key]['id']              = $val['id'];
            $datas[$key]['title']           = $val['title'];
            $datas[$key]['cover_img']       = QiniuService::getImgs($val['cover_img']);
            $datas[$key]['cover_img_lager'] = QiniuService::getImgs($val['cover_img_lager']);
            $datas[$key]['create_time']     = DateUtils::formatDate($val['release_time']);
            if (empty($val['footer_img_h5_link'])) {
                $datas[$key]['footer_img_h5_link'] = LinkUtils::appLink($val['id']);
            } else {
                $datas[$key]['footer_img_h5_link'] = $val['footer_img_h5_link'];
            }
            $datas[$key]['sign'] = isset($val['sign']) ? $val['sign'] : 0;
            //h5 专用判断跳转地址
            $datas[$key]['footer_img_h5'] = $val['footer_img_h5_link'];
            $datas[$key]['theme_id']      = $themeNameId['id'];
        }

        $activitiesLists['list']      = $datas ? $datas : RestUtils::getStdObj();
        $activitiesLists['pageCount'] = $newsLists['pageCount'];

        return $activitiesLists;
    }

    /**
     * @param $newsArr
     * 返回资讯详情数据
     */
    public static function getDetails($newsArr)
    {
        $newsArr['cover_img']   = QiniuService::getImgs($newsArr['cover_img']);
        $newsArr['footer_img']  = QiniuService::getImgs($newsArr['footer_img']);
        $newsArr['create_time'] = DateUtils::formatToDay($newsArr['create_time']);

        return $newsArr ? $newsArr : [];
    }

    /**
     * @param $res
     * @return array
     * 获取banner 中news83 的资讯详情 —— App端使用
     */
    public static function getBannerNewsById($res)
    {
        $data = [];
        if (empty($res['footer_img_h5_link'])) {
            $data['footer_img_h5_link']           = LinkUtils::appLink($res['id']);
        } else {
            $data['footer_img_h5_link'] = $res['footer_img_h5_link'];
        }
        $data['sign']       = 0;

        return $data ? $data : [];
    }
}

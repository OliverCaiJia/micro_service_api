<?php
namespace App\Models\Factory;

use App\Helpers\DateUtils;
use App\Helpers\LinkUtils;
use App\Helpers\RestUtils;
use App\Models\AbsModelFactory;
use App\Models\Orm\News;
use App\Models\Orm\NewsTheme;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\FavouriteInfo;
use App\Strategies\PageStrategy;

class NewsFactory extends AbsModelFactory
{

    /**
     * @param $newsType
     * 返回资讯分类  名称 && id
     *  newsType 1  活动
     *  newsType 2  常识
     */
    public static function fetchNameAndId($data)
    {
        $themeId = !empty($data['themeId']) ? $data['themeId'] : false;
        $newsThemeArr = NewsTheme::select(['theme_name', 'id'])
            ->where(['id' => $themeId, ['status', '!=', 9]])
            ->first();
        return $newsThemeArr ? $newsThemeArr->toArray() : [];
    }

    /**
     * @param $newsType
     * 返回资讯信息
     */
    public static function fetchNewsArray($data)
    {
        $themeId = $data['themeId'];

        $newsObj = News::from('sd_news as n')
            ->select(['n.id', 'n.title', 'n.cover_img', 'n.cover_img_lager', 'n.release_time', 'n.footer_img_h5_link'])
            ->where(['n.status' => 0, 'news_theme_id' => $themeId])
            ->orderBy('n.update_time', 'desc');

        $count = $newsObj->count();
        /**
         * 分页
         */
        $page = PageStrategy::getPage($count, $data['pageSize'], $data['pageNum']);
        $newsListObj = $newsObj->limit($page['limit'])->offset($page['offset'])->get();

        $newsArr['list'] = $newsListObj ? $newsListObj->toArray() : [];
        $newsArr['pageCount'] = $page['pageCount'];
        return $newsArr;
    }


    //////////////////////////////////////////////////////////////////////////////////////
    const SUDAI_ACTIVITY = 11;
    const SUDAI_COMMON = 15;


    /**
     * @param $data
     * 查询攻略数据
     */
    public static function fetchGuides($data = [])
    {
        $pageSize = isset($data['pageSize']) ? $data['pageSize'] : 1;
        $pageNum = isset($data['pageNum']) ? $data['pageNum'] : 4;     //默认显示3条

        $productObj = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.is_delete' => 0, 'pf.online_status' => 1, ['p.news_link', '!=', '']])
            ->select('p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'p.product_logo', 'p.product_introduct', 'p.news_link')
            ->orderBy('p.position_sort', 'asc')
            ->orderBy('p.update_date', 'desc');
        $count = $productObj->count();
        //分页
        $page = PageStrategy::getPage($count, $pageSize, $pageNum);
        $guideLists['list'] = $productObj->limit($page['limit'])->offset($page['offset'])->get()->toArray();
        $guideLists['pageCount'] = $page['pageCount'];

        return $guideLists;
    }

    /**详情
     * @param array $data
     */
    public static function fetchDetails($newsId = '')
    {
        $newsObj = News::where(['id' => $newsId])
            ->select(['id', 'title', 'create_time', 'visit_count', 'cover_img', 'footer_img', 'footer_img_inapp_link', 'content'])
            ->first();

        return $newsObj ? $newsObj->toArray() : [];
    }

    /**
     * @param $data
     * @return bool
     * 统计点击量
     */
    public static function fetchClicks($newsId = '')
    {
        //点击量+1
        $click = News::where(['id' => $newsId])->first();
        if (!empty($click)) {
            $click->click_count += 1;
            $click->visit_count += 1;
            return $click->save();
        }
        return true;
    }





    ///////////////////////////////////////////////////////////////////////////////
    /**
     * @param $user_id
     * @param $data
     * @return mixed
     * 收藏资讯  列表
     */
    public static function collectionAll($user_id, $data)
    {
        //查询用户收藏的资讯
        $collectionIdArr = FavouriteInfo::select(['news_id'])
            ->where(['user_id' => $user_id])
            ->pluck('news_id')->toArray();
        foreach ($data as $key => $val) {
            if (in_array($val['id'], $collectionIdArr)) {
                $data[$key]['sign'] = 1;
            } else {
                $data[$key]['sign'] = 0;
            }
        }
        return $data;
    }

    /**收藏资讯 单个
     * @param $newsId
     * @param $userId
     * @return int
     */
    public static function collectionOne($newsId, $userId)
    {
        $sign = 0;
        $collection = FavouriteInfo::select(['user_id'])
            ->where(['news_id' => $newsId, 'user_id' => $userId])
            ->first();
        if (!empty($collection)) {
            $sign = 1;
        }
        return $sign;
    }


}
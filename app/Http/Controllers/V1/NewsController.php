<?php
namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\NewsFactory;
use App\Models\Factory\ProductFactory;
use App\Strategies\NewStrategy;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;


class NewsController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * 速贷攻略
     */
    public function fetchGuides(Request $request)
    {
        $data = $request->all();
        //查询产品数据
        $guideLists = NewsFactory::fetchGuides($data);
        //标签
        $guideTagLists = ProductFactory::tagsByAll($guideLists['list']);
        if (empty($guideTagLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //图片地址
        $productArr = ProductStrategy::getGuides($guideTagLists, $guideLists['pageCount']);

        return RestResponseFactory::ok($productArr);
    }

    /**
     * @param Request $request
     * 活动
     */
    public function fetchActivities(Request $request)
    {
        //用户id
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data   = $request->all();
        //接收值数据处理
        $data = NewStrategy::getData($data);
        //资讯分类内容
        $themeNameId = NewsFactory::fetchNameAndId($data);
        if (empty($themeNameId)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //资讯内容
        $newsLists = NewsFactory::fetchNewsArray($data);
        if(empty($newsLists['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(),RestUtils::getErrorMessage(1500),1500);
        }

        //数据规整
        $activitiesLists = NewStrategy::getNewsData($themeNameId, $newsLists);
        //收藏
        if (!empty($userId)) {
            $activitiesLists['list'] = NewsFactory::collectionAll($userId, $activitiesLists['list']);
        }

        return RestResponseFactory::ok($activitiesLists);
    }

    /**详情
     * @param Request $request
     */
    public function fetchDetails(Request $request)
    {
        $newsId   = $request->input('newsId','');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';

        //统计点击量
        $detailClick = NewsFactory::fetchClicks($newsId);
        //资讯详情
        $detailLists = NewsFactory::fetchDetails($newsId);
        if (empty($detailLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //收藏
        if (!empty($userId)) {
            $detailLists['sign'] = NewsFactory::collectionOne($newsId, $userId);
        }
        //图片处理
        $detailLists = NewStrategy::getDetails($detailLists);

        return RestResponseFactory::ok($detailLists);

    }


}
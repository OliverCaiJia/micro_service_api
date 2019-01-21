<?php

namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ProductSearchFactory;
use App\Models\Factory\UserFactory;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Class ProductSearchController
 * @package App\Http\Controllers\V1
 * 产品搜索
 */
class ProductSearchController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     * 产品搜索热词列表
     */
    public function fetchHots()
    {
        //限制个数
        $limit = 16;
        $hots = ProductSearchFactory::fetchHots($limit);
        //暂无数据
        if (empty($hots)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($hots);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 搜索列表
     */
    public function fetchSearchs(Request $request)
    {
        //搜索条件
        $productName = $request->input('product_name', '');
        $data['productName'] = trim($productName);
        //分页
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        $data['productType'] = $request->input('productType', 1);
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';

        //记录搜索流水
        $user = UserFactory::fetchUserNameAndMobile($data['userId']);
        $searchLog = ProductSearchFactory::createSearchLog($user, $data);

        //搜索范围
        $searchs = ProductSearchFactory::fetchSearchs($data);
        //最大页数
        $pageCount = $searchs['pageCount'];
        //标签
        $productLists = ProductFactory::tagsByAll($searchs['list']);
        //暂无产品
        if (empty($productLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //处理数据
        $productLists = ProductStrategy::getProductsOrSearchs($data['productType'], $productLists, $pageCount);

        return RestResponseFactory::ok($productLists);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 搜索反馈
     */
    public function createFeedback(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['content'] = $request->input('content', '');

        $feedback = ProductSearchFactory::createFeedback($data);
        if (!$feedback) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1701), 1701);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }


}
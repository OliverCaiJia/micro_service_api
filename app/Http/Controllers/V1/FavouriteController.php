<?php

namespace App\Http\Controllers\V1;

use App\Constants\ProductConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\FavouriteFactory;
use App\Models\Factory\ProductFactory;
use App\Strategies\FavouriteStrategy;
use Illuminate\Http\Request;


class FavouriteController extends Controller
{
    /**
     * @param Request $request
     * 产品收藏——收藏产品
     */
    public function createCollectionById(Request $request)
    {
        $productId = $request->input('productId');
        $userId = $request->user()->sd_user_id;

        //添加收藏
        $create = FavouriteFactory::createCollectionByUidAndPid($userId, $productId);
        if (empty($create)) {
            //请重新收藏
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1401), 1401);
        } else {
            return RestResponseFactory::ok(['is_collection' => 1]);
        }
    }

    /**
     * @param Request $request
     * 产品收藏——取消收藏
     */
    public function deleteCollectionById(Request $request)
    {
        $productId = $request->input('productId');
        $userId = $request->user()->sd_user_id;

        //取消收藏
        $del = FavouriteFactory::deleteCollectionByUidAndPid($userId, $productId);
        if (empty($del)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1403), 1403);
        } else {
            return RestResponseFactory::ok(['is_collection' => 0]);
        }
    }

    /**
     * @param Request $request
     * 产品收藏——收藏列表
     */
    public function fetchCollectionLists(Request $request)
    {
        $data = $request->all();
        $productType = isset($data['productType']) ? $data['productType'] : 1;
        $data['userId'] = $request->user()->sd_user_id;

        //获取收藏的product_id
        $productIdArr = FavouriteFactory::fetchCollectionProductId($data['userId']);
        if (empty($productIdArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //放款时间
        $data['key'] = ProductConstant::PRODUCT_LOAN_TIME;
        //收藏产品列表
        $productArr = FavouriteFactory::fetchCollectionLists($data, $productIdArr);
        $pageCount = $productArr['pageCount'];

        if (empty($productArr['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $productArr = ProductFactory::tagsByAll($productArr['list']);
        //数据处理
        $productArr = FavouriteStrategy::getCollectionLists($productType, $productArr, $pageCount);

        return RestResponseFactory::ok($productArr);
    }

    /**产品是否收藏  ios、android
     * @param Request $request
     * @return mixed
     */
    public function fetchCollections(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        $data = $request->all();
        $data['userId'] = $userId;

        $productId = $data['productId'];
        //是否收藏
        $product['is_collection'] = FavouriteFactory::collectionProducts($userId, $productId);
        //添加、修改评论
        $product['is_comment'] = CommentFactory::fetchIsComment($data);

        return RestResponseFactory::ok($product);
    }

    /**
     * @param Request $request
     * 资讯收藏——收藏资讯
     */
    public function createCollectionNewsById(Request $request)
    {
        $newsId = $request->input('newsId');
        $userId = $request->user()->sd_user_id;

        //添加收藏
        $create = FavouriteFactory::createClooectionNewsByUidAndPid($userId, $newsId);
        if (empty($create)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1401), 1401);
        } else {
            return RestResponseFactory::ok(['is_collection' => 1]);
        }
    }

    /**
     * @param Request $request
     * 资讯收藏——取消收藏
     */
    public function deleteCollectionNewsById(Request $request)
    {
        $newsId = $request->input('newsId');
        $userId = $request->user()->sd_user_id;

        //取消收藏
        $del = FavouriteFactory::deleteCollectionNewsByUidAndPid($userId, $newsId);
        if (empty($del)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1403), 1403);
        } else {
            return RestResponseFactory::ok(['is_collection' => 0]);
        }
    }


    /**
     * @param Request $request
     * 资讯收藏——资讯列表
     */
    public function fetchCollectionNewsLists(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        $data['userId'] = $userId;
        //用户收藏资讯id
        $newsIdArr = FavouriteFactory::fetchCollectionNewsId($data);
        if (empty($newsIdArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //获取用户收藏的所有资讯
        $newsLists = FavouriteFactory::fetchCollectionNewsLists($data, $newsIdArr);

        if (empty($newsLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $newsLists = FavouriteStrategy::getCollectionNewsLists($newsLists, $data);
        return RestResponseFactory::ok($newsLists);
    }


}

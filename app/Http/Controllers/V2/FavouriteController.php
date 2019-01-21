<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Constants\ProductConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Models\Factory\FavouriteFactory;
use App\Models\Factory\ProductFactory;
use App\Strategies\FavouriteStrategy;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    /**
     * 产品收藏——收藏列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCollectionLists(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        $data['mobile'] = $request->user()->mobile;

        //获取收藏的product_id
        $productIdArr = FavouriteFactory::fetchCollectionProductId($data['userId']);
        if (empty($productIdArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //放款时间
        $data['key'] = ProductConstant::PRODUCT_LOAN_TIME;
        //收藏产品列表
        $data['productIdArr'] = $productIdArr;
        $products = FavouriteFactory::fetchProductCollections($data);
        $pageCount = $products['pageCount'];
        if (empty($products['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $products = ProductFactory::tagsLimitOneToProducts($products['list']);
        //数据处理
        $productData['mobile'] = $data['mobile'];
        $productData['products'] = $products;
        $products = FavouriteStrategy::getProductCollections($productData);

        $res['list'] = $products;
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }
}
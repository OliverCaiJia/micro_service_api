<?php

namespace App\Http\Controllers\V1;

use App\Constants\ProductConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\BankFactory;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\ExactFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserinfoFactory;
use App\Strategies\BankStrategy;
use App\Strategies\ExactStrategy;
use App\Strategies\UserinfoStrategy;
use Illuminate\Http\Request;

/**
 * Class ExactsController
 * @package App\Http\Controllers\V1
 * 精准匹配
 */
class ExactController extends Controller
{
    /**
     * @return mixed
     * 精确匹配 —— 广告图片
     */
    public function fetchExactBanner()
    {
        //精确匹配广告图片
        $exactBanner = ExactFactory::fetchExactBanner();
        //数据处理
        $exactBannerArr = ExactStrategy::getExactBanner($exactBanner);

        return RestResponseFactory::ok($exactBannerArr);
    }

    /**
     * 获取精确匹配数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchMatchData(Request $request)
    {
        //接收用户需要匹配的值
        $userId = $request->user()->sd_user_id;
        //获取精确匹配数据
        $exactArr = ExactFactory::fetchMatchData($userId);

        if ($exactArr && $exactArr['balance'] != 0 && $exactArr['balance_time'] != 0 && $exactArr['to_use'] != 0) {
            //已进行过精准匹配
            return RestResponseFactory::ok($exactArr);
        } else {
            //暂无数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
    }

    /**
     * 精准匹配判断基础信息是否完整
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBasicCompleteness(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        $data = $request->all();
        //修改精确匹配数据
        $exacRes = ExactFactory::updateExactMatchDatas($userId, $data);
        //身份
        $indent = UserFactory::fetchUserIndent($userId);
        //身份证姓名 & 性别 & 真实姓名
        $basicArr = UserFactory::fetchCardAndRealname($userId);

        //根据 userId 查询 Account
        $userAccount = BankFactory::fetchBanksArray($userId);
        //银行卡信息 Name
        $bankArr = BankFactory::fetchBankNameByBankId($userAccount);
        //数据处理 得到 Account & Name
        $userBanksArr = BankStrategy::getAccountAndName($userAccount, $bankArr);

        //支付宝账号
        $alipay = BankFactory::fetchAlipayArray($userId);
        //是否拥有信用卡或是学信网账号
        $certifyArr = UserinfoFactory::fetchXuexinAndCreditByIndent($userId, $indent);

        //合并数组处理
        $basicCompleNum = UserinfoStrategy::mergeArray($basicArr, $userBanksArr, $alipay, $certifyArr);
        if ($basicCompleNum == 7) {
            return RestResponseFactory::ok(RestUtils::getStdObj());
        } else {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1502), 1502);
        }

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 精确匹配数据
     */
    public function fetchExactMatchDatas(Request $request)
    {
        //接收用户需要匹配的值
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        //地域id
        $areaId = $request->input('areaId', '');
        $deviceId = $request->input('deviceId', '');
        //根据设备id与用户id获取城市id
        $cityId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($deviceId);
        $data['cityId'] = !empty($cityId) ? $cityId : $areaId;
        //所有产品id
        $data['productIds'] = ProductFactory::fetchProductIds();
        //产品城市关联表中的所有产品id
        $data['cityProductIds'] = DeviceFactory::fetchCityProductIds();
        //地域对应产品id
        $data['deviceProductIds'] = DeviceFactory::fetchProductIdsByDeviceId($data['cityId']);

        //身份
        $data['indent'] = UserFactory::fetchUserIndent($userId);
        //修改精确匹配数据
        $exacRes = ExactFactory::updateExactMatchDatas($userId, $data);
        //已进行过精准匹配   直接匹配
        if (!empty($exacRes)) {
            //可以进行精准匹配
            $matchinfoArr = ExactFactory::fetchExactMatchDatas($userId);
            //获取产品id
            $data['productIdArr'] = array_column($matchinfoArr, 'productId');
            //放款时间
            $data['key'] = ProductConstant::PRODUCT_LOAN_TIME;
            //产品数据
            $productArr = ExactFactory::fetchExactMatchProducts($data);

            if (empty($productArr) || empty($productArr['list'])) {
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
            }
            //总页数
            $pageCount = $productArr['pageCount'];
            //产品标签
            $productArr = ProductFactory::tagsByAll($productArr['list']);

            //精确匹配结果数据处理
            $productData['list'] = ExactStrategy::getExactMatchDatas($productArr, $matchinfoArr);
            $productData['pageCount'] = $pageCount;

            return RestResponseFactory::ok($productData);

        } else {
            //暂时没有数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
    }


}
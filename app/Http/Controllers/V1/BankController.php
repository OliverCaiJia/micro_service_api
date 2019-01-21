<?php
namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Factory\BankFactory;
use App\Services\Core\Validator\Bank\Alipay\AlipayBankService;
use App\Strategies\BankStrategy;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * 基础信息 —— 获取银行列表
     */
    public function fetchBankLists()
    {
        $banks = BankFactory::fetchBankLists();
        return RestResponseFactory::ok($banks);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 基础信息 —— 验证银行名称
     */
    public function validateBankName(Request $request)
    {
        $data = $request->all();
        $bankNum = Utils::removeSpaces($data['account']);
        //银行卡号
        $accountRes = AlipayBankService::validateBankName($bankNum);
        //通过银行名称查数据
        $bankArr = BankFactory::fetchAccountByName($accountRes['bankName']);
        //数据处理
        $bankRes = BankStrategy::getValidateBankName($accountRes, $bankArr, $data);

        return RestResponseFactory::ok($bankRes);
    }

    /**
     * 基础信息 —— 银行列表数据是否更新
     */
    public function fetchBankCounts()
    {
        //银行表是否添加数据
        $bankCounts = BankFactory::fetchBankCounts();
        // 数据处理
        $bankCountsRes = BankStrategy::getBankCounts($bankCounts);

        return RestResponseFactory::ok($bankCountsRes);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 基础信息 —— 获取银行名称【h5】
     */
    public function fetchBankName(Request $request)
    {
        $account = $request->input('account');
        //银行卡号
        $accountRes = AlipayBankService::validateBankName($account);
        // 银行id
        $bankArr = BankFactory::fetchBanksByName($accountRes['bankName']);
        //数据处理
        $bankRes = BankStrategy::getValidateBankNameH5($accountRes, $bankArr);

        return RestResponseFactory::ok($bankRes);
    }

    

}
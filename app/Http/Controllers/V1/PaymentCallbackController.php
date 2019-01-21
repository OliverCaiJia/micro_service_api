<?php

namespace App\Http\Controllers\V1;

use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Factory\PaymentFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Factory\UserVipFactory;
use App\Services\AppService;
use App\Services\Core\Payment\PaymentService;
use App\Services\Core\Payment\YiBao\YiBaoService;
use App\Services\Core\Sms\Laiao\LaiaoEventService;
use App\Services\Core\Sms\SmsService;
use App\Services\Core\Tools\Phone\PhoneService;
use App\Services\Core\Validator\IP\Taobao\TaobaoIPService;
use App\Services\Core\Validator\IP\Taobao\TaobaoPhoneService;
use App\Strategies\PaymentStrategy;
use App\Strategies\UserVipStrategy;
use Illuminate\Http\Request;

/**
 * 易宝回调
 *
 * Class PaymentCallbackController
 * @package App\Http\Controllers\V1
 */
class PaymentCallbackController extends Controller
{
    /**
     * 易宝同步回调
     *
     * @param Request $request
     * @return string
     */
    public function yiBaoSynCallBack(Request $request)
    {
        SLogger::getStream()->info('易宝同步回调', ['code' => 10300]);
        $params = $request->input();
        if(!isset($params['type']) || !isset($params['data']) || !isset($params['encryptkey']))
        {
            return 'ERROR';
        }
        $return = YiBaoService::i()->undoData($params['data'], $params['encryptkey']);
        //对订单状态进行判断
        $status = PaymentFactory::getOrderStatus($return['orderid']);
        if($status == 1)
        {
            return 'SUCCESS';
        }
        else
        {
            $result = PaymentStrategy::getDiffOrderChain($params);
            if(isset($result['error']))
            {
                return $result;
            }

            return 'SUCCESS';
        }
    }

    /**
     * 易宝异步回调
     *
     * @param Request $request
     * @return string
     */
    public function yiBaoAsynCallBack(Request $request)
    {
        SLogger::getStream()->info('易宝异步回调', ['code' => 10200]);
        $params = $request->input();
        if(!isset($params['type']) || !isset($params['data']) || !isset($params['encryptkey']))
        {
            return 'ERROR';
        }
        $return = YiBaoService::i()->undoData($params['data'], $params['encryptkey']);
        //对订单状态进行判断
        $status = PaymentFactory::getOrderStatus($return['orderid']);
        if($status == 1)
        {
            return 'SUCCESS';
        }
        else
        {
            $result = PaymentStrategy::getDiffOrderChain($params);
            if(isset($result['error']))
            {
                return $result;
            }

            return 'SUCCESS';
        }
    }

}
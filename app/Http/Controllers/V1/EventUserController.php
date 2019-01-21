<?php

namespace App\Http\Controllers\V1;


use App\Constants\ConfigConstant;
use App\Constants\EventUserConstant;
use App\Helpers\Formater\NumberFormater;
use App\Helpers\LinkUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\AccountFactory;
use App\Models\Factory\AlipayFactory;
use App\Models\Factory\BankFactory;
use App\Models\Factory\CashFactory;
use App\Models\Chain\Cash\DoCashHandler;
use App\Models\Factory\ConfigFactory;
use App\Models\Factory\EventUserFactory;
use App\Models\Factory\UserFactory;
use App\Services\Core\Sms\SmsService;
use App\Strategies\AccountLogStrategy;
use App\Strategies\CashStrategy;
use App\Strategies\IdentityStrategy;
use App\Strategies\UserStrategy;
use Illuminate\Http\Request;

/**
 * Class EventUserController
 * @package App\Http\Controllers\V1
 * event
 */
class EventUserController extends Controller
{

    /**
     * 发送短信
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userMessage(Request $request)
    {
        $data['mobile'] = $request->input('mobile');

        //验证是否是新老用户
        $user = UserFactory::getUserByMobile($data['mobile']);
        $data['status'] = (!empty($user)) ? 0 : 1;

        //存入表中
        EventUserFactory::insertEventUser($data);
        //发送短信
        $data['message'] = EventUserConstant::SEND_MESSAGE;
        $res = SmsService::i()->to($data);
        //SLogger::getStream()->info('userMessage', ['message' => $res, 'code' => 1009100]);

        return RestResponseFactory::ok();
    }


}

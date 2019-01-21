<?php

namespace App\Services\Core\Sms\Changzhuo;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Services\Core\Sms\SmsService;
use Log;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Models\Orm\MessageLog;

/**
 * 畅卓短信通道
 * Class ChangzhuoService
 * @package App\Services\Core\Sms\Changzhuo
 */
class ChangzhuoService extends SmsService
{

    /**
     * 发送短信
     * @param $data
     */
    public function send($data)
    {
        $request = [
            'form_params' => [
                'account' => config('sms.changzhuo.account'),
                'password' => config('sms.changzhuo.password'),
                'mobile' => $data['mobile'],
                'content' => $data['message'],
                'sendTime' => '',
                'extno' => ''
            ]
        ];
        $promise = HttpClient::i()->request('POST', config('sms.changzhuo.smsSendUrl'), $request);
	    $result = $promise->getBody()->getContents();
	    $this->sendAfter($result, $data);
        return $result;
    }

    /**
     * 发送之后把返回短信商结果入库并执行更新
     */
    public function sendAfter($result, $data = [])
    {
        if ($result)
        {
            MessageLog::where('nid',$data['nid'])->where('mobile', $data['mobile'])->update(['result' => addslashes($result), 'response_time' => date('Y-m-d H:i:s', time())]);
        }
    }

}

<?php

namespace App\Services\Core\Sms\Laiao;

use App\Services\Core\Sms\SmsService;
use App\Helpers\Http\HttpClient;
use App\Models\Orm\MessageLog;
use Illuminate\Support\Facades\Log;

/**
 * 来凹短信通道
 *
 */
class LaiaoEventService extends SmsService
{

    /**
     * 发送
     *
     * @param $data
     * @return string
     */
    public function send($data)
    {
        $tm = date('YmdHis', time());
        $pwd = md5(config('sms.laiao.password').$tm);
        $request = [
            'query' => [
                'uid' => config('sms.laiao.uid'),
                'pw' => $pwd,
                'mb' => $data['mobile'],
                'ms' => mb_convert_encoding($data['message'], 'UTF-8' ,'UTF-8'),
                'ex' => '',
                'tm' => $tm,
                'dm' => $tm,
            ]
        ];

        $promise = HttpClient::i()->request('GET', config('sms.laiao.smsSendUrl'), $request);
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
            MessageLog::where('nid', $data['nid'])->where('mobile', $data['mobile'])->update(['result' => addslashes($result), 'response_time' => date('Y-m-d H:i:s', time())]);
        }
    }

}

<?php

namespace App\Services\Core\Sms\Yimei;

use App\Services\Core\Sms\SmsService;
use App\Helpers\Http\HttpClient;
use App\Models\Orm\MessageLog;

/**
 * 亿美短信通道
 *
 */
class YimeiService extends SmsService
{

    /**
     *
     * @param $data
     */
    public function send($data)
    {
        $request = [
            'query' => [
                'cdkey' => config('sms.yimei.cdkey'),
                'password' => config('sms.yimei.password'),
                'phone' => $data['mobile'],
                'message' => $data['message'],
	            'addserial'=> config('sms.yimei.addserial'),
            ]
        ];
        $promise = HttpClient::i()->request('GET', config('sms.yimei.smsSendUrl'), $request);
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

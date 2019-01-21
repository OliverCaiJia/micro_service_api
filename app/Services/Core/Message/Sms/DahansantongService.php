<?php

namespace App\Services\Core\Message\Sms\Dahansantong;

use App\Helpers\Http\HttpClient;
use App\Models\Orm\MessageLog;
use App\Services\Core\Sms\SmsService;
use Illuminate\Support\Facades\Log;

/**
 * 大汉三通短信
 *
 * Class DahansantongService
 * @package App\Services\Core\Sms\Dahansantong
 */
class DahansantongService extends SmsService
{
    public function send($data, $config = [])
    {
        //密码md5加密小写
        $pwd = strtolower(md5($config['password']));
        $request = [
            'json' => [
                'account' => $config['username'],
                'password' => $pwd,
                'phones' => $data['mobile'],  //发送多个手机号用','隔开的字符串
                'content' => $data['message'],
                'sign' => $config['sign'],
                'subcode' => $config['id_code'],
                'sendtime' => '',
            ]
        ];
        Log::info('check', $request);
        $response = HttpClient::i()->request('POST' ,$config['url'] ,$request);
        $result = $response->getBody()->getContents();
        Log::info('check', ['message' => $result]);
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
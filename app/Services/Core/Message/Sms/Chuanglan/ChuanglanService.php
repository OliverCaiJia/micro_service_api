<?php

namespace App\Services\Core\Message\Sms\Chuanglan;

use App\Services\Core\Sms\SmsService;
use App\Helpers\Http\HttpClient;
use App\Models\Orm\MessageLog;
use Log;

/**
 * 创蓝短信通道
 * Class ChuanglanService
 * @package App\Services\Core\Sms\Chuanglan
 */
class ChuanglanService extends SmsService
{

    /**
     *
     * @param $data
     */
    public function send($data, $config = [])
    {
        if (!empty($config))
        {
            $request = [
                'form_params' => [
                    'account' => $config['username'],
                    'pswd' => $config['password'],
                    'msg' => $data['message'],
                    'mobile' => $data['mobile'],
                    'needstatus' => true,
                    'product' => '',
                    'extno' => ''
                ]
            ];

            $promise = HttpClient::i()->request('POST', $config['url'], $request);
            $result = $promise->getBody()->getContents();
            $this->sendAfter($result, $data);
            return $result;
        }

        return [];
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

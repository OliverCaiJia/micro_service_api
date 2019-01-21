<?php

namespace App\Services\Core\Message\Sms\Wwtl;

use App\Services\Core\Sms\SmsService;
use App\Helpers\Http\HttpClient;
use App\Models\Orm\MessageLog;
use Log;

/**
 * 微网通联短信通道
 *
 */
class WwtlService extends SmsService
{

    /**
     *
     * @param $data
     */
    public function send($data, $config = [])
    {
        $request = [
            'query' => [
                'sname' => $config['username'],
                'spwd' => $config['password'],
                'scorpid' => '',
                'sprdid' => $config['id_code'],
                'sdst' => $data['mobile'],
                'smsg' => $data['message'],
            ]
        ];

        $promise = HttpClient::i()->request('GET', $config['url'], $request);
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

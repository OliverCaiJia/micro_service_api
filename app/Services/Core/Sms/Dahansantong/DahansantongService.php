<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 17-8-16
 * Time: 上午10:14
 */
namespace App\Services\Core\Sms\Dahansantong;

use App\Helpers\Http\HttpClient;
use App\Models\Orm\MessageLog;
use App\Services\Core\Sms\SmsService;

/**
 * 大汉三通短信
 *
 * Class DahansantongService
 * @package App\Services\Core\Sms\Dahansantong
 */
class DahansantongService extends SmsService
{
    public function send($data)
    {
        //密码md5加密小写
        $pwd = strtolower(md5(config('sms.dahansantong.password')));
        $url = config('sms.dahansantong.smsSendUrl');
        $request = [
            'json' => [
                'account' => config('sms.dahansantong.account'),
                'password' => $pwd,
                'phones' => $data['mobile'],  //发送多个手机号用','隔开的字符串
                'content' => $data['message'],
                'sign' => '',
                'subcode' => config('sms.dahansantong.subcode'),
                'sendtime' => '',
            ]
        ];

        $response = HttpClient::i()->request('POST' ,$url ,$request);
        $result = $response->getBody()->getContents();
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
<?php
namespace App\Models\Chain\ShadowSms\Sms\Register;

use App\Helpers\Generator\TokenGenerator;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Message\Sms\SmsService;
use App\Models\Chain\Sms\Register\PutValueToCacheAction;

class SendRegisterSmsAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '短信验证码下发已超上限', 'code' => 3);
    protected $randoms;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 发送注册短信
     */
    public function handleRequest()
    {
        if ($this->sendRegisterSms($this->params) == true) {
            $this->setSuccessor(new PutValueToCacheAction($this->params,$this->randoms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 发送注册短信
     */
    private function sendRegisterSms($params)
    {
        #生成四位数字短信验证码
        $code = mt_rand(1000, 9999);
        #组织短信验证码内容
        $data['mobile'] = $params['mobile'];
        $data['message'] = "验证码：{$code}，请勿泄露。关注官方微信“速贷之家官微”";
        $data['code']=$code;
        $data['shadowNid'] = $params['shadowNid'];
        #调取发送方法
        $re = SmsService::i()->to($data, $data['shadowNid']);
        #生成32位随机字符串
        $random = [];
        $random['sign'] = TokenGenerator::generateToken();

        $this->randoms = $random;
        $this->params['code'] = $code;

        if(!$re) {
            return false;
        }

        return true;
    }



}

<?php

namespace App\Models\Chain\Creditcard\Account;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\PushFactory;
use App\Strategies\PushStrategy;
use App\Models\Chain\Creditcard\Account\CreateAccountLogAction;

/**
 * Class CheckRegistrationIdAction
 * @package App\Models\Chain\Creditcard\Bill
 * 1.验证registration_id是否存在于sd_user_jpush表中
 *      存在查id，不存在插入sd_user_jpush表并获取id
 */
class FetchRegistrationIdAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '获取推送相关信息失败！', 'code' => 1000);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     *
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->fetchRegistrationId($this->params) == true) {
            $this->setSuccessor(new CheckIsAccountAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function fetchRegistrationId($params)
    {
        $registrationId = PushFactory::fetchIdByRegistrationId($params['registrationId']);
        if (!$registrationId && !empty($params['registrationId'])) {
            //转化数据
            $datas = PushStrategy::getRegistrations($params);
            //不存在 向表中插入数据
            $registration = PushFactory::addJpushInfo($datas);
            if ($registration) {
                //获取id
                $registrationId = PushFactory::fetchIdByRegistrationId($params['registrationId']);
            }
        }
        $this->params['registration_id'] = $registrationId;

        return true;
    }
}

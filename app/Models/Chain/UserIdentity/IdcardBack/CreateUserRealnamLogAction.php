<?php

namespace App\Models\Chain\UserIdentity\IdcardBack;

use App\Constants\UserIdentityConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Strategies\UserIdentityStrategy;
use App\Models\Chain\UserIdentity\IdcardBack\UpdateUserRealnameAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 3.记实名认证流水
 */
class CreateUserRealnamLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '请您扫描有效期内证件', 'code' => 10003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 3.记实名认证流水
     */
    public function handleRequest()
    {
        if ($this->createUserRealnamLog($this->params) == true) {
            $this->setSuccessor(new UpdateUserRealnameAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 3.记实名认证流水
     */
    private function createUserRealnamLog($params = [])
    {
        // 记流水
        $params['type'] = 'faceid';
        $params['status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FACE;
        $params['certificate_type'] = UserIdentityConstant::CERTIFICATE_TYPE_IDCARD;
        //身份证有效期
        $faceinfo = $params['faceinfo'];
        $validDate = isset($faceinfo['valid_date']) ? explode('-', $faceinfo['valid_date']) : [];
        $params['card_starttime'] = UserIdentityStrategy::formatTimeToYmd($validDate[0]);
        $params['card_endtime'] = UserIdentityStrategy::formatTimeToYmd($validDate[1]);
        $this->params['card_starttime'] = $params['card_starttime'];
        $this->params['card_endtime'] = $params['card_endtime'];

        //判断身份证期限
        $time = date('Y-m-d', time());
        if ($params['card_endtime'] <= $time) {
            return false;
        }

        return UserIdentityFactory::createUserRealnameLog($params);

    }

}

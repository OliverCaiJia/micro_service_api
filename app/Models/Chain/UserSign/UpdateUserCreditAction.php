<?php

namespace App\Models\Chain\UserSign;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CreditFactory;

class UpdateUserCreditAction extends AbstractHandler
{

    private $params = [];
    protected $error = ['error' => '用户积分增加失败！', 'code' => 1003];

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第三步:用户增加积分
     * @return array
     */
    public function handleRequest()
    {
        if ($this->updateUserCredit($this->params))
        {
            return true;
        }
        else
        {
            return $this->error;
        }
    }

    private function updateUserCredit($params)
    {
        $params['frost'] = 0;
        $params['score'] = $params['income'];
        $res = CreditFactory::addUserCredit($params);

        return $res;
    }

}

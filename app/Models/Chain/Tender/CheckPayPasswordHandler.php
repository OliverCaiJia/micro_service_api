<?php

namespace App\Models\Chain\Tender;

use \Cache as Cache;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\CheckActiveCodeHandler;
use App\Models\Orm\BorrowTender;
use App\Models\Orm\Linkages;
use App\Models\Orm\LinkagesType;
use App\Models\Orm\Account;
use App\Models\Orm\BorrowStyle;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TenderChain
 */
class CheckPayPasswordHandler extends AbstractHandler
{
	
    const V_PAYPASSWORD_TIME_LIMIT = 5;
    const V_PAYPASSWORD_LIMIT_KEY = 'limit_';
    const V_PAYPASSWORD_LIMIT_TIME = 1440;
    private $parms = array();
    //private $error = array('error' => '交易密码错误');

    public function __construct($parms = array())
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        $result = $this->checkPayPassword($this->parms['userId'], $this->parms['paypassword']);
        if ($result === true) {
            $this->setSuccessor(new CheckActiveCodeHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $result;
        }
    }

    //检查交易密码是否正确
    public function checkPayPassword($userId, $paypassword)
    {
        $data = self::getUser($userId);
        if (empty($data) || $paypassword != $data->paypassword) {
            $result = self::checkPaypasswordLimit($userId);
            if($result !== true){
                return $result;
            }
            return array('error' => '交易密码错误');
        }
        return true;
    }
	
    //检查交易密码错误次数
    public static function checkPaypasswordLimit($userId = '')
    {
        $Limitkey = 'getPaypassword_' . self::V_PAYPASSWORD_LIMIT_KEY . $userId;
        if (Cache::has($Limitkey)) {
            Cache::increment($Limitkey, 1);
        } else {
            Cache::put($Limitkey, 1, self::V_PAYPASSWORD_LIMIT_TIME);
        }
        // $effectResult = Cache::get($Limitkey);
        /*if($effectResult > self::V_PAYPASSWORD_TIME_LIMIT){
            return array('error' => '交易密码输入错误次数最大为5次');
        }*/
        return true;
    }
}

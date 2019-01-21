<?php
namespace App\Models\Chain\Credit;

use App\Helpers\Utils;
use App\Models\Orm\UserCredit;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Credit\CreateAccountLogAction;

class UpdateCreditAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '对不起,用户总积分减少失败！', 'code' => 6003);
    protected $data;

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * @return array|bool
     * 2.用户总积分减少
     */
    public function handleRequest()
    {
        if ($this->updateCredit($this->params) == true) {
            $this->setSuccessor(new CreateAccountLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * 更新用户总积分
     * @param $params
     * @return bool
     */
    private function updateCredit($params)
    {
        //用户id
        $userId  = $params['userId'];
        $expends = intval($params['expend_credits']);

        //锁行
        $creditObj = UserCredit::where(['user_id' => $userId, 'status' => 0])->lockForUpdate()->first();

        $creditObj->expend += $expends;
        $creditObj->credits   = $creditObj->income - $creditObj->expend;
        $creditObj->balance   = $creditObj->credits - $creditObj->frost;
        $creditObj->update_at = date('Y-m-d H:i:s', time());
        $creditObj->update_user_id = $userId;
        $creditObj->update_ip = Utils::ipAddress();
        return $creditObj->save();
    }
}

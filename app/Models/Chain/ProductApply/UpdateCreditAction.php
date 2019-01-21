<?php
namespace App\Models\Chain\ProductApply;

use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserCredit;
use App\Models\Chain\ProductApply\CheckIsInviteAction;

class UpdateCreditAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '用户积分表加积分失败!', 'code' => 8005);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 4.用户积分表加积分
     */
    public function handleRequest()
    {
        if ($this->updateCredit($this->params) == true) {
            $this->setSuccessor(new CheckIsInviteAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 用户积分表加积分
     *      先查询 后修改
     */
    private function updateCredit($params)
    {
        //查询不在就创建一条数据
        $creditObj = UserCredit::lockForUpdate()
            ->where(['user_id' => $params['userId'], 'status' => 0])
            ->first();
        if (empty($creditObj)) {
            $creditObj = new UserCredit();
        }

        $creditObj->user_id = $params['userId'];
        $creditObj->income += intval($params['credits']);
        $creditObj->expend    = isset($creditObj->expend) ? $creditObj->expend : 0;
        $creditObj->credits   = bcsub($creditObj->income, $creditObj->expend);
        $creditObj->frost     = isset($creditObj->frost) ? $creditObj->frost : 0;
        $creditObj->balance   = bcsub($creditObj->credits, $creditObj->frost);
        $creditObj->update_at = date('Y-m-d H:i:s', time());
        $creditObj->update_user_id = $params['userId'];
        $creditObj->update_ip = Utils::ipAddress();
        return $creditObj->save();

    }
}
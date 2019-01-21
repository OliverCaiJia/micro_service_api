<?php
namespace App\Models\Chain\ProductApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserInviteLog;
use App\Models\Chain\ProductApply\CreateAccountLogAction;

class CheckIsInviteAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '是否被邀请过判断失败!', 'code' => 8006);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 5.判断是否被邀请过
     *      已邀请  6.邀请流水表  先查询后修改   查询有值修改状态（邀请中、已注册、已申请）
     *             7.邀请人账户流水表插入数据
     *             8.邀请人账户表更新数据
     *      未邀请  true
     */
    public function handleRequest()
    {
        if ($this->checkIsInvite($this->params) == true) {
            //被邀请
            $this->setSuccessor(new CheckAccountLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            //未被邀请
            return true;
        }
    }

    /**
     * @param $params
     * 判断是否被邀请过
     *      已邀请  6.邀请流水表  先查询后插入   查询有值修改状态（邀请中、已注册、已申请）
     *             7.邀请人账户流水表插入数据
     *             8.邀请人账户表更新数据
     *      未邀请  true
     */
    private function checkIsInvite($params)
    {
        $inviteLog = UserInviteLog::where(['invite_user_id' => $params['userId']])
            ->first();
        if (empty($inviteLog)) {
            return false;
        }
        //邀请人id
        $this->params['inviteId'] = $inviteLog->user_id;
        //已申请
        $inviteLog->status          = 3;
        $inviteLog->created_at      = date('Y-m-d H:i:s');
        $inviteLog->created_user_id = $params['userId'];
        return $inviteLog->save();

    }
}
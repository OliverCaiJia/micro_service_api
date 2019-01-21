<?php
namespace App\Models\Chain\Invite;


use App\Constants\InviteConstant;
use App\Models\Factory\InviteFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserInviteLog;
use App\Helpers\Logger\SLogger;

class CreateInviteLogAction extends AbstractHandler
{
	private $params = array();
	
	public function __construct($params)
	{
		$this->params = $params;
	}
	
	
	
	/**
	 *创建邀请记录表日志记录
	 * @return array|bool
	 */
	public function handleRequest()
	{
		if ($this->createInviteLog($this->params) == true)
		{
			$this->setSuccessor(new UpdateUserInviteAction($this->params));
			return $this->getSuccessor()->handleRequest();
		}
		else
		{
			return $this->error;
		}
	}
	
	
	/**
	 *
	 * @param $params
	 * @return bool
	 */
	private function createInviteLog($params)
	{
		//根据手机号和状态去邀请日志表中获得邀请人id
		$invite_log_arr = InviteFactory::fetchInviteUserIdByMobileFromLog($params['mobile']);
		if (empty($invite_log_arr)) {
			$params['from'] = InviteConstant::INVITE_FROM_SHARE;
			$params['sd_invite_code'] = !empty($params['sd_invite_code']) ? $params['sd_invite_code'] : '';
			$params['user_id'] = InviteFactory::fetchInviteUserIdByCode($params['sd_invite_code']);
		} else {
			$params['from'] = InviteConstant::INVITE_FROM_SMS;
			$params['user_id'] = $invite_log_arr['user_id'];
			$params['sd_invite_code'] = $invite_log_arr['code'];
		}
		$this->params['user_id'] = $params['user_id'];
        $params['status'] = InviteConstant::INVITE_REGISTER;
		return  InviteFactory::updateOrCreateInviteLog($params);
	}
}

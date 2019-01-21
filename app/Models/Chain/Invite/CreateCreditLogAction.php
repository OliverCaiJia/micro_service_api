<?php
namespace App\Models\Chain\Invite;


use App\Models\Factory\InviteFactory;
use App\Models\Factory\CreditFactory;
use App\Models\Chain\AbstractHandler;
use App\Constants\CreditConstant;

class CreateCreditLogAction extends AbstractHandler
{
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
		if ($this->createCreditLog($this->params) == true)
		{
			$this->setSuccessor(new UpdateUserCreditAction($this->params));
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
	private function createCreditLog($params)
	{
        $params['income'] = InviteFactory::inviteScore($params['user_id']);
        $params['type'] = CreditConstant::REGISTER_INVITE_TYPE;
        $params['remark']  = CreditConstant::REGISTER_INVITE_REMARK;
		return  CreditFactory::createAddCreditLog($params);
	}
}

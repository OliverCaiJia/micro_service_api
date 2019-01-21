<?php
namespace App\Models\Chain\Invite;


use App\Models\Factory\CreditFactory;
use App\Models\Factory\InviteFactory;
use App\Models\Chain\AbstractHandler;

class UpdateUserCreditAction extends AbstractHandler
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
		if ($this->updateUserCredit($this->params) == true)
		{
			return true;
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
	private function updateUserCredit($params)
	{
        $params['score'] = InviteFactory::inviteScore($params['user_id']);
		return  CreditFactory::addUserCredit($params);
	}
}

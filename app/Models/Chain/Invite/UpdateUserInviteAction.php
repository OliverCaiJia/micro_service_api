<?php
namespace App\Models\Chain\Invite;


use App\Models\Factory\InviteFactory;
use App\Models\Chain\AbstractHandler;

class UpdateUserInviteAction extends AbstractHandler
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
		if ($this->updateUserInvite($this->params) == true)
		{
			$this->setSuccessor(new CreateCreditLogAction($this->params));
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
	private function updateUserInvite($params)
	{
		return  InviteFactory::updateInvite($params);
	}
}

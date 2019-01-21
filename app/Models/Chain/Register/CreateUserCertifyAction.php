<?php
namespace  App\Models\Chain\Register;

use App\Models\Factory\AuthFactory;
use App\Models\Factory\UserFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Register\CreateUserIdentityAction;

class CreateUserCertifyAction extends  AbstractHandler
{
	private $params = array();
	protected $error = array('error' => '对不起,用户认证失败!！', 'code' => 111);
	protected $data;
	
	
	public function __construct($params)
	{
		
		$this->params = $params;
	}
	
	
	/**创建用户认证信息
	 * @return array|bool
	 */
	public function handleRequest()
	{
		if ($this->createUserCertify($this->params) == true) {
			$this->setSuccessor(new CreateUserIdentityAction($this->params));
			return $this->getSuccessor()->handleRequest();
		} else {
			return $this->error;
		}
	}
	
	
	/**
	 * @desc 创建用户认证信息
	 * @param $params
	 */
	private function createUserCertify($params)
	{
		 return AuthFactory::createUserCertify($params);
	}
	
}
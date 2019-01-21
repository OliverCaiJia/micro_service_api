<?php
namespace App\Models\Chain\Register;


use App\Models\Factory\AuthFactory;
use App\Models\Factory\UserFactory;
use App\Models\Orm\UserAuth;
use App\Models\Chain\AbstractHandler;
use \DB;
use App\Models\Chain\Register\CreateUserCertifyAction;

class CreateUserAction extends AbstractHandler
{
	private $params = array();
	protected $error = array('error' => '对不起,用户注册失败！', 'code' => 111);
	protected $data;
	protected  $user;
	
	public function __construct($params)
	{
		$this->params = $params;
	}
	
	
	
	/**
	 * 创建用户
	 * @return array|bool
	 */
	public function handleRequest()
	{
		if ($this->createUser($this->params) == true)
		{
			$this->params['sd_user_id'] = UserFactory::getIdByMobile($this->params['mobile']);
			$this->params['user'] =$this->user;
			$this->setSuccessor(new CreateUserCertifyAction($this->params));
			return $this->getSuccessor()->handleRequest();
		}
		else
		{
			return $this->error;
		}
	}
	
	
	/**
	 * 用户主表sd_user_auth中存数据
	 * @param $params
	 * @return bool
	 */
	private function createUser($params)
	{
		$this->user = AuthFactory::createUser($params);
        return true;
	}
}

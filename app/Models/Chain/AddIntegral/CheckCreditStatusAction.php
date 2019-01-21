<?php

namespace App\Models\Chain\AddIntegral;

use App\Constants\CreditConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CreditStatusFactory;
use App\Strategies\CreditStatusStrategy;
use App\Models\Chain\AddIntegral\CreateCreditLogAction;

class CheckCreditStatusAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '不符合加积分条件!', 'code' => 6001);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.判断是否符合加积分的条件
     */
    public function handleRequest()
    {
        if ($this->checkCreditStatus($this->params) == true) {
            $this->setSuccessor(new CreateCreditLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    private function checkCreditStatus($params)
    {
        switch ($params['typeNid']) {
            //新用户注册 永远1次
            case CreditConstant::ADD_INTEGRAL_USER_REGISTER_TYPE:
                return CreditStatusFactory::fetchCreditOnceStatusByUserId($params);
                break;
            //设置头像 永远1次
            case CreditConstant::ADD_INTEGRAL_USER_PHOTO_TYPE:
                return CreditStatusFactory::fetchCreditOnceStatusByUserId($params);
                break;
            //设置用户名 永远1次
            case CreditConstant::ADD_INTEGRAL_USER_USERNAME_TYPE:
                return CreditStatusFactory::fetchCreditOnceStatusByUserId($params);
                break;
            //发表评论 每天最多5次
            case CreditConstant::ADD_INTEGRAL_USER_COMMENT_TYPE:
                $params['count'] = CreditStatusFactory::fetchCreditStatusCountById($params);
                return CreditStatusStrategy::fetchCreditStatusCount($params);
                break;
            //推荐新贷款产品 每天最多2次
            case CreditConstant::ADD_INTEGRAL_FEEDBACK_PRODUCT_NAME_TYPE:
                $params['count'] = CreditStatusFactory::fetchCreditStatusCountById($params);
                return CreditStatusStrategy::fetchCreditStatusCount($params);
                break;
            //意见反馈 每天最多1次
            case CreditConstant::ADD_INTEGRAL_FEEDBACK_TYPE:
                $params['count'] = CreditStatusFactory::fetchCreditStatusCountById($params);
                return CreditStatusStrategy::fetchCreditStatusCount($params);
                break;
            default :
                return false;
                break;
        }
    }


}

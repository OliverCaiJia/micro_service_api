<?php

namespace App\Models\Chain\AddIntegral;

use App\Constants\CreditConstant;
use App\Models\Factory\CreditFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CreditStatusFactory;

class UpdateCreditStatusAction extends AbstractHandler
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
     * 4.修改完成状态
     */
    public function handleRequest()
    {
        if ($this->updateCreditStatus($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 修改用户完成加积分状态
     */
    private function updateCreditStatus($params)
    {
        switch ($params['typeNid']) {
            //新用户注册
            case CreditConstant::ADD_INTEGRAL_USER_REGISTER_TYPE:
                return CreditStatusFactory::updateCreditStatusById($params);
                break;
            //设置头像
            case CreditConstant::ADD_INTEGRAL_USER_PHOTO_TYPE:
                return CreditStatusFactory::updateCreditStatusById($params);
                break;
            //设置用户名
            case CreditConstant::ADD_INTEGRAL_USER_USERNAME_TYPE:
                return CreditStatusFactory::updateCreditStatusById($params);
                break;
            //发表评论 每天最多5次
            case CreditConstant::ADD_INTEGRAL_USER_COMMENT_TYPE:
                return CreditStatusFactory::updateCreditStatusCountById($params);
                break;
            //推荐新贷款产品 每天最多两次
            case CreditConstant::ADD_INTEGRAL_FEEDBACK_PRODUCT_NAME_TYPE:
                return CreditStatusFactory::updateCreditStatusCountById($params);
                break;
            //意见反馈 每天最多一次
            case CreditConstant::ADD_INTEGRAL_FEEDBACK_TYPE:
                return CreditStatusFactory::updateCreditStatusCountById($params);
                break;
            default :
                return false;
                break;
        }
    }
}

<?php

namespace App\Models\Chain\Creditcard\ShadowApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Orm\BankCreditcard;
use App\Models\Chain\Creditcard\ShadowApply\CreateCardApplyLogAction;

/*
 * 更新信用卡信息
 */
class UpdateCreditCardAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,修改信用卡信息失败！', 'code' => 1002);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 第一步:更新信用卡数据
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateCreditCard($this->params)) {
            $this->setSuccessor(new CreateCardApplyLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /** 更新信用卡
     * @param $params
     * @return bool
     */
    private function updateCreditCard($params)
    {
        $model = BankCreditcard::where('id', $params['card_id'])->first();
        //  申请量 + 1
        $apply_count = $model->apply_count + 1;
        // 申请量总值
        $total_apply_count = $apply_count + $model->add_apply_count;

        // 更新卡片数据
        $model->total_apply_count = $total_apply_count;
        $model->apply_count = $apply_count;
        $res = $model->save();

        if ($res)
        {
            $this->params['bank_id'] = $model->bank_id;
        }

        return $res;
    }
}

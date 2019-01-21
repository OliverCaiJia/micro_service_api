<?php

namespace App\Models\Chain\Creditcard\Account;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\Creditcard\Account\FetchRegistrationIdAction;

/**
 *  还款提醒-信用卡
 */
class DoAccountHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * @return mixed]
     * 1.验证registration_id是否存在于sd_user_jpush表中
     *      存在查id，不存在插入sd_user_jpush表并获取id
     * 2.验证该信用卡账户是否存在
     * 3.插入流水sd_bank_creditcard_account_log表
     * 4.创建或修改sd_bank_creditcard_account表
     */
    public function handleRequest()
    {
        $result = ['error' => '出错啦,可能该信用卡已存在！', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new FetchRegistrationIdAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('添加行用卡失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('添加信用卡失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}

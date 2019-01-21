<?php

namespace App\Models\Chain\Creditcard\Account;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\BanksFactory;
use App\Models\Factory\CreditcardAccountFactory;
use App\Services\Core\Store\Qiniu\QiniuService;

/**
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 * 4.创建或修改sd_bank_creditcard_account表
 */
class CreateOrUpdateAccountAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,修改信用卡账户失败！', 'code' => 1004);
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
        if ($this->createOrUpdateAccount($this->params) == true) {
            return $this->params;
        } else {
            return $this->error;
        }
    }


    /**
     * @param $params
     * @return bool
     */
    private function createOrUpdateAccount($params)
    {
        $re = CreditcardAccountFactory::createOrUpdateAccount($params);

        //查询生成的accountId
        $accountId = CreditcardAccountFactory::fetchAccountId($params);
        $this->params['account_id'] = $accountId['id'];
        //银行logo
        $bankLogo = BanksFactory::fetchBankUsageById($params['bankUsageId']);
        $this->params['bank_logo'] = empty($bankLogo) ? '' : QiniuService::getBankLogo($bankLogo['bank_logo']);

        return $re;
    }
}

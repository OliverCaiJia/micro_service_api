<?php
namespace App\Services\Core\Validator\Bank\Alipay;

use App\Constants\BankConstant;
use App\Services\AppService;

class AlipayBankService extends AppService
{
    /**
     * @param $cardNum
     * @return array
     * 通过阿里验证银行名称
     */
    public static function validateBankName($cardNum)
    {
        $result = file_get_contents("https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo={$cardNum}&cardBinCheck=true");
        $result = json_decode($result);

        $bankInfoConstant = BankConstant::BANK_INFO;
        $cardTypeConstant = BankConstant::CARD_TYPE;

        if ($result->validated == false) {
            $bankInfo = array(
                'validated' => $result->validated,
                'bank' => '',                        // 银行代码
                'bankName' => '',                       // 银行名称
                'bankImg' => '',
                'cardType' => '',                       // 银行卡类型, CC 信用卡, DC 储蓄卡
                'cardTypeName' => '',
            );
        } else {
            $bankInfo = array(
                'validated' => $result->validated,                                                                // 是否验证通过
                'bank' => $result->bank,                                                                    // 银行代码
                'bankName' => isset($bankInfoConstant[$result->bank]) ? $bankInfoConstant[$result->bank] : '',  // 银行名称
                'bankImg' => self::getBankImg($result->bank),
                'cardType' => $result->cardType,                                                               // 银行卡类型, CC 信用卡, DC 储蓄卡
                'cardTypeName' => $cardTypeConstant[$result->cardType],
            );
        }

        return $bankInfo;
    }
    /**
     * @param $bank
     * @return string
     * 阿里获取银行logo
     */
    public static function getBankImg($bank)
    {
        return "https://apimg.alipay.com/combo.png?d=cashier&t={$bank}";
    }

}
<?php

namespace App\Strategies;

use App\Strategies\AppStrategy;

/**
 * Data公共策略
 *
 * @package App\Strategies
 */
class DataStrategy extends AppStrategy
{
    const BAIRONG_PRODUCT_BIG_TYPE = 1;  //大额度
    const BAIRONG_PRODUCT_SMAILL_TYPE = 2;  //小额度

    /**
     * 百融返回的产品列表
     *
     * @return array
     */
    public static function getBairongProductList()
    {
        return [
            'SJD' => [
                'type' => self::BAIRONG_PRODUCT_BIG_TYPE,
                'name' => '手机贷大额分期',
                'pic_name' => 'shoujidaidae',
                'quota' => '100000元',
                'describe' => '用身份证就能借款，约90%可获得高额度',
                'introduce1' => '月息1.5%',
                'introduce2' => '5分钟到账',
                'introduce3' => '当天放款',
                'h5url' => 'https://m.shoujidai.com/index.php/user/flow_index/channel/dktg0022-llcs.html'
            ],
            'ZZ' => [
                'type' => self::BAIRONG_PRODUCT_BIG_TYPE,
                'name' => '闪电大额分期',
                'pic_name' => 'shandiandae',
                'quota' => '100000元',
                'describe' => '用身份证就能借款，约90%可获得高额度',
                'introduce1' => '月息1.5%',
                'introduce2' => '无任何费用',
                'introduce3' => '当天放款',
                'h5url' => 'https://dljk.weshare.com.cn/regisiter.html?c=26703&utm_source=BaiRong-D&utm_medium=SMS&utm_term=E'
            ],
            'PPM' => [
                'type' => self::BAIRONG_PRODUCT_BIG_TYPE,
                'name' => '及贷大额分期',
                'pic_name' => 'jidaidae',
                'quota' => '20000元',
                'describe' => '3-12期随心借，时间更灵活，费率更低',
                'introduce1' => '日息0.03%',
                'introduce2' => '5分钟到账',
                'introduce3' => '门槛级低',
                'h5url' => 'https://m.geedai.com/activity/reg/register.html?utm_source=wap_jidaiqudao166'
            ],
            'MMD' => [
                'type' => self::BAIRONG_PRODUCT_BIG_TYPE,
                'name' => '么么大额分期',
                'pic_name' => 'memedae',
                'quota' => '50000元',
                'describe' => '3分钟智能审核，通过率高，流程便捷',
                'introduce1' => '借款低至0利率',
                'introduce2' => '最快30分钟到账',
                'introduce3' => '消费分期更优惠',
                'h5url' => 'https://coupon.mi-me.com/promo/html/marketing.html?td_channelid=bairong2'
            ],
            'ZZ_S' => [
                'type' => self::BAIRONG_PRODUCT_SMAILL_TYPE,
                'name' => '闪电借款',
                'pic_name' => 'shandian',
                'quota' => '10000元',
                'describe' => '用身份证就能借款，约90%可获得高额度',
                'introduce1' => '月息1.5%',
                'introduce2' => '无任何费用',
                'introduce3' => '最快56秒到账',
                'h5url' => 'https://dljk.weshare.com.cn/regisiter.html?c=1010101999&utm_source=BaiRong10Yue&utm_medium=SMS&utm_term=A'
            ],
            'PPM_S' => [
                'type' => self::BAIRONG_PRODUCT_SMAILL_TYPE,
                'name' => '及贷',
                'pic_name' => 'jidai',
                'quota' => '20000元',
                'describe' => '3-12期随心借，时间更灵活，费率更低',
                'introduce1' => '日息0.03%',
                'introduce2' => '5分钟到账',
                'introduce3' => '门槛级低',
                'h5url' => 'https://m.geedai.com/activity/reg/register.html?utm_source=wap_jidaiqudao90'
            ],
            'GM_S' => [
                'type' => self::BAIRONG_PRODUCT_SMAILL_TYPE,
                'name' => '美借',
                'pic_name' => 'meijie',
                'quota' => '20000元',
                'describe' => '3-18月还款周期，时间更长，更加省心',
                'introduce1' => '纯信用',
                'introduce2' => '无抵押',
                'introduce3' => '审核快',
                'h5url' => 'https://jie.gomemyf.com/jie-h5/html/activity/flow-register-b.html?vendor=bairong&fromSource=bairong1'
            ]
        ];
    }

}

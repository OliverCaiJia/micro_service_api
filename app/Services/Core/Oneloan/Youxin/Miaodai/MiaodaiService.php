<?php

namespace App\Services\Core\Oneloan\Youxin\Miaodai;

use App\Services\AppService;
use App\Helpers\Http\HttpClient;
use App\Services\Core\Oneloan\Youxin\Miaodai\Config\MiaodaiConfig;

/**
 * Class MiaodaiService
 * @package App\Services\Core\Platform\Youxin\Miaodai
 * 友信-秒贷
 */
class MiaodaiService extends AppService
{
    /**
     * 友贷推广
     * @param $params
     * @return mixed
     */
    public static function spread($params = [])
    {
        //Authorization 
        $author = MiaodaiConfig::getAuthorization();
        //header参数
        $header = [
            'Authorization' => $author,
        ];
        //body参数
        $data = [
            'customerInfo' => [
                'sales_project_id' => MiaodaiConfig::SOURCE_ID,
                'source_id' => MiaodaiConfig::SOURCE_ID,//名单来源
                'name' => $params['name'],//姓名
                'cellphone' => $params['mobile'],//手机号
                'belong_city' => $params['city'],//城市
                'is_social' => MiaodaiConfig::getSocialSecurity($params['social_security']),//是否有社保
                //@todo 待定字段 是否有公积金
                'accumulation_fund' => MiaodaiConfig::getAccumulationFund($params['accumulation_fund']),//是否有公积金
                ////@todo 待定字段 微粒贷
                'is_micro' => $params['is_micro'] == 1 ? '有' : '无', //微粒贷
                //'monthly_income' => $params['salary'], //月收入
                //非必要字段
                'gender' => $params['sex'],//性别
                'id_number' => $params['certificate_no'],//身份证号
                'apply_amount' => $params['money'],//借款金额
                'has_creditcard' => MiaodaiConfig::getCreditcard($params['has_creditcard']),//是否有信用卡
            ]
        ];
        $paramsStr = json_encode($data, JSON_UNESCAPED_UNICODE);

        //申请时间
        $params['time'] = date('Ymd');
        //请求参数
        $request = [
            'headers' => $header,
            'form_params' => [
                'customerInfo' => $paramsStr,
            ],
        ];

        //请求地址
        $url = MiaodaiConfig::URL;
        //发送头部信息
        //发送主题信息
        $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $arr = json_decode($result, true);
        return $arr;
    }

}
<?php

namespace App\Services\Core\Oneloan\Hougedai;

use App\Services\AppService;
use App\Helpers\Http\HttpClient;
use App\Services\Core\Oneloan\Hougedai\Config\HougedaiConfig;
use App\Strategies\SpreadStrategy;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * 猴哥贷对接
 */
class HougedaiService extends AppService
{
    /**
     * 猴哥贷接口对接
     *
     * @param array $params
     * @return mixed
     */
    public static function spread($params = [], callable $success, callable $fail)
    {
        //请求url
        $url = HougedaiConfig::REAL_URL;
        //请求参数
        $request = [
            'form_params' => [
                'code' => HougedaiConfig::CODE, //必填 渠道code
                'truename' => $params['name'],  //必填 名字
                'mobile' => $params['mobile'],  //必填 手机号
                'city' => $params['city'],  //必填 城市
                'loanamount' => SpreadStrategy::getLoanMoneyToThou($params['money']), //选填 贷款金额
                'birthday' => date("Ymd", strtotime($params['birthday'])), //选填 出生日期
                'gender' => $params['sex'] == 1 ? 'M' : 'F', //选填 性别
            ],
        ];

        $promise = HttpClient::i()->requestAsync('POST', $url, $request);

        $promise->then(
            function (ResponseInterface $res) use($success) {
                $result = $res->getBody()->getContents();
                $success(json_decode($result, true));
            },
            function (RequestException $e) use($fail) {
                $fail($e);
            }
        );

        $promise->wait();
        return;
    }
}


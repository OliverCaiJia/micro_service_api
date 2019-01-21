<?php

namespace App\Services\Core\Oneloan\Renxinyong;

use App\Services\AppService;
use App\Helpers\Http\HttpClient;
use App\Services\Core\Oneloan\Renxinyong\Config\RenxinyongConfig;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * 任信用对接
 */
class RenxinyongService extends AppService
{
    /**
     * 任信用接口对接
     *
     * @param array $params
     * @return mixed
     */
    public static function spread($params = [], callable $success, callable $fail)
    {
        //请求url
        $url = RenxinyongConfig::REAL_URL;
        //请求参数
        $request = [
            'json' => [
                'params' => [
                    'phoneNo' => $params['mobile'],
                    'channelCode' => RenxinyongConfig::CHANNEL_CODE,
                ],
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


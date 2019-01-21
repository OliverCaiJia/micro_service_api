<?php

namespace App\Services\Core\Oneloan\Chunyu;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Services\AppService;
use App\Services\Core\Oneloan\Chunyu\Config\ChunyuConfig;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * 春雨对接
 */
class ChunyuService extends AppService
{

    /**
     * 注册接口
     *
     * @param array $params
     * @return mixed
     */
    public static function register($params = [], callable $success, callable $fail)
    {
        //请求url
        $url = ChunyuConfig::REAL_URL;

        //扩展字段 [{}, {}]二维数组格式 键值为json字符串
        $extentedParam[] = [
            'client_ip' => isset($params['created_ip']) ? $params['created_ip'] : Utils::ipAddress(), // 必填 用户真实IP
        ];

        //请求
        $request = [
            'form_params' => [
                'name' => $params['name'],                                    // 用户名
                'gender' => $params['sex'] == 1 ? 1 : 2,                      // 性别
                'mobile' => $params['mobile'],                                // 手机号
                'birthday' => substr($params['birthday'], 0, 10),  // 城市名字:拼音形式
                'debug' => PRODUCTION_ENV ? '' : 1,                           // 1 不是必填，值为1的时候是测试数据
                'extentedParam' => json_encode($extentedParam),               // 扩展字段
            ],
        ];

        $promise = HttpClient::i()->requestAsync('POST', $url, $request);

        $promise->then(
            function (ResponseInterface $res) use ($success) {
                $result = $res->getBody()->getContents();
                $success(json_decode($result, true));
            },
            function (RequestException $e) use ($fail) {
                $fail($e);
            }
        );

        $promise->wait();

        return ;
    }
}


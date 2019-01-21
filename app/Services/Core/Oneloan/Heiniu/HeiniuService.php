<?php

namespace App\Services\Core\Oneloan\Heiniu;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Services\AppService;
use App\Services\Core\Oneloan\Heiniu\Config\HeiniuConfig;
use App\Services\Core\Oneloan\Heiniu\Util\DesUtil;
use Illuminate\Support\Facades\Log;

/**
 * 黑牛保险 —— 黑牛保险对接Service
 *
 * Class HeiniuService
 * @package App\Services\Core\Platform\Heiniu\HeiniuService
 */
class HeiniuService extends AppService
{
    /**
     * 黑牛保险 —— 黑牛保险对接Service
     * @param $datas
     * @return array
     */
    public static function insurance($datas)
    {
        // 签名
//        $sign = self::getSign($datas['name'], $datas['mobile'], HeiniuConfig::CHANNEL);
        // 性别
        $datas['sex'] = $datas['sex'] == 1 ? 'M' : 'F';
        $encryptedSex = DesUtil::i()->encrypt($datas['sex'], HeiniuConfig::DES_KEY);
        // 姓名
        $encryptedName = DesUtil::i()->encrypt($datas['name'], $key = HeiniuConfig::DES_KEY);
        // 手机
        $encryptedMobile = DesUtil::i()->encrypt($datas['mobile'], $key = HeiniuConfig::DES_KEY);
        // 生日 1980-01-01
        $encryptedBirth = DesUtil::i()->encrypt($datas['birthday'], $key = HeiniuConfig::DES_KEY);
        // 用户ip
        $customer_ip = isset($datas['created_ip']) ? $datas['created_ip'] :Utils::ipAddress();
        //用户新增信息
        $customers = HeiniuConfig::createCustomer($datas);
        $encryCustomer = DesUtil::i()->encrypt($customers, $key = HeiniuConfig::DES_KEY);

        $param = [
//            'sex' => $encryptedSex,
            'name' => $encryptedName,
//            'sign' => $sign,
//            'birth' => $encryptedBirth,
            'phone' => $encryptedMobile,
            'channel' => HeiniuConfig::CHANNEL,
            'subchannel' => HeiniuConfig::SUBCHANNEL,
            'customer_ip' => $customer_ip,
            'custom' => $encryCustomer,
        ];
        $otherParam = HeiniuConfig::getParams($datas);
        $params = array_merge($param, $otherParam);
        // 参数
        $args = http_build_query($params);

        $url = HeiniuConfig::URL . '?' . $args;
        $promise = HttpClient::i(['verify' => false])->request('GET', $url);
        $result = $promise->getBody()->getContents();

        $resultObj = json_decode($result, true);

        return $resultObj;
    }


}
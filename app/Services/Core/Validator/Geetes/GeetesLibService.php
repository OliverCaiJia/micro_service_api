<?php
namespace App\Services\Core\Validator\Geetes;

use App\Helpers\RestResponse;
use App\Services\AppService;
use App\Services\Core\Validator\Geetes\Config\GeetestLibConfig;
use App\Services\Core\Validator\Geetes\Libs\GeetestLib;
use App\Models\Factory\CacheFactory;
use App\Helpers\RestResponseFactory;
use App\Helpers\Utils;
use App\Helpers\RestUtils;

/**
 * Class GeetesLibService
 * @package App\Services\Core\Store\Geetes
 * 极验
 */
class GeetesLibService extends AppService
{
    /** 极验一次验证
     * web:电脑浏览器 h5 手机浏览器 包括移动应用内置web_view native通过原生SDK植入APP应用
     * @param $captcha_id
     * @param $private_key
     */
    public function startCaptcha($data)
    {
        $GtSdk = new GeetestLib(GeetestLibConfig::CAPTCHA_ID, GeetestLibConfig::PRIVATE_KEY);

        $uuid = isset($data['uuid']) ? $data['uuid'] : null;
        $type = isset($data['client_type']) ? $data['client_type'] : null;
        $result = [
            'success' => 0,
            'gt' => '',
            'challenge' => '',
            'new_captcha' => 1
        ];

        // 参数不存在 则返回错误
        if (is_null($uuid) or is_null($type))
        {
            return json_encode($result);
        }

        // uuid验证失败
        if (!$this->verifyUuid($type,$uuid))
        {
            return json_encode($result);
        }

        $data = [
            'user_id' => $uuid,
            'client_type' => $type,
            'ip_address' => Utils::ipAddress()
        ];
        $status = $GtSdk->pre_process($data, 1);

        CacheFactory::putValueToCacheForever('gtserver_' . $uuid, $status);

        $gtRes = $GtSdk->get_response_str();
        return $gtRes;
    }

    /** 极验二次验证
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify($data)
    {
        $client_type = $data['client_type'];
        $uuid = $data['uuid'];

        // uuid验证失败
        if (!$this->verifyUuid($client_type, $uuid))
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(),RestUtils::getErrorMessage(9101),9101);
        }

        $GtSdk = new GeetestLib(GeetestLibConfig::CAPTCHA_ID, GeetestLibConfig::PRIVATE_KEY);

        //标识符 1成功 0失败
        $gtserver = CacheFactory::getValueFromCache('gtserver_' . $uuid);
        $params = [
            'user_id' => $uuid,
            'client_type' => $client_type,
            'ip_address' => Utils::ipAddress()
        ];

        //进行极验二次验证 图片的吻合度
        if ($gtserver == 1) {
            $result = $GtSdk->success_validate($data['geetest_challenge'], $data['geetest_validate'], $data['geetest_seccode'], $params);
            if ($result) {
                return RestResponseFactory::ok(['status' => 'success']);
            } else {
                return RestResponseFactory::ok(RestUtils::getStdObj(),RestUtils::getErrorMessage(9102),9102);
            }
        } else {
            // 极验服务器宕机情况下在本地完成二次验证·
            if ($GtSdk->fail_validate($data['geetest_challenge'], $data['geetest_validate'], $data['geetest_seccode'])) {
                return json_encode(['status' => 'success']);
            } else {
                return RestResponseFactory::ok(RestUtils::getStdObj(),RestUtils::getErrorMessage(9102),9102);
            }
        }
    }

    /** 验证uuid是否有效
     * @param $type
     * @param $uuid
     * @return bool
     */
    public function verifyUuid($type, $uuid)
    {
        $geetestUuid = 'geetest_uuid_' . $type . '_' . $uuid;
        if (CacheFactory::existValueFromCache($geetestUuid))
        {
            return true;
        }

        return false;
    }
}
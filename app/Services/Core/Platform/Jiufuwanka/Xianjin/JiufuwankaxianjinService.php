<?php

namespace App\Services\Core\Platform\Jiufuwanka\Xianjin;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\PlatformService;
use App\Services\Core\Platform\Jiufuwanka\Xianjin\Util\RsaUtil;
use App\Services\Core\Platform\Jiufuwanka\Xianjin\Config\XianjinConfig;

/**
 * 玖富万卡 —— 玖富万卡现金对接Service
 * Class JiufuwankaxianjinService
 * @package App\Services\Core\Platform\Jiufuwanka\Xianjin
 */
class JiufuwankaxianjinService extends PlatformService
{
    /**
     * 玖富万卡现金 对接地址
     * @param $mobile
     * @param $page
     * @return string
     */
    public static function fetchJiufuwankaUrl($datas)
    {
        $mobile = $datas['user']['mobile'];       //手机号
        $real_name = $datas['user']['real_name']; // 真实姓名
        $idcard = $datas['user']['idcard'];       // 身份证号码
        $realip = Utils::ipAddress();             // 用户真实ip

        $page = $datas['page'];
        $is_new_user = 0;
        $complete_degree = '';
        $qualify_status = 0;

        // 合作商分配id
        $partnerId = XianjinConfig::getPartnerId();
        // 查询接口
        $selectUrl = XianjinConfig::getSelectUrl();
        // 联合登录地址
        $url = XianjinConfig::getLoginUrl();
        // 加密参数
        $encryptedPhoneNo = RsaUtil::i()->rsaEncrypt($mobile);
        $encryptedRealName = RsaUtil::i()->rsaEncrypt($real_name);
        $encryptedIdCardNo = RsaUtil::i()->rsaEncrypt($idcard);
        $encryptedSourceIp = RsaUtil::i()->rsaEncrypt($realip);
        // 查询参数
        $selectRequest = [
            'form_params' => [
                'parterId' => $partnerId,
                'phoneNo'  => $encryptedPhoneNo,
                'requestSourceIp' => $encryptedSourceIp
            ]
        ];
        // 查询
        $selectResult = static::execute($selectRequest, $selectUrl);
        if (isset($selectResult['data'])) {
            $data = json_decode($selectResult['data'], true);
            if (isset($data['userStatus']) && $data['competeDegree'] && isset($data['qualifiStatus'])) {
                if ($data['userStatus'] == 0) {
                    // 未注册 => 通过速贷之家推过来的新用户
                    $is_new_user = 3;
                } elseif($data['userStatus'] == 1) {
                    // 已注册 且为渠道用户 => 通过速贷之家推的老用户
                    $is_new_user = 2;
                } elseif($data['userStatus'] == 2) {
                    // 已注册 且非渠道用户 => 其他渠道推过来的用户
                    $is_new_user = 4;
                }

                $complete_degree = $data['competeDegree'];
                $qualify_status = $data['qualifiStatus'];
            }
        }

        // 联合登录参数
        $request = [
            'form_params' => [
                'parterId' => $partnerId,
                'phoneNo'  => $encryptedPhoneNo,
                'realName' => $encryptedRealName,
                'IdCardNo' => $encryptedIdCardNo,
                'requestSourceIp' => $encryptedSourceIp
            ],
        ];

        // 联合登录
        $result = static::execute($request, $url);
        if (isset($result['data'])) {
            $data = json_decode($result['data'], true);
            if (isset($data['url'])) {
                $page = $data['url'];
            }
        }

        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $mobile;
        $datas['channel_no'] = 'SDZJ';
        $datas['apply_url'] = $page;
        $datas['feedback_message'] = isset($result['message']) ? $result['message'] : '';
        $datas['is_new_user'] = $is_new_user;
        $datas['complete_degree'] = $complete_degree;
        $datas['qualify_status'] = $qualify_status;
        //对接平台返回对接信息记流水
        $log = OauthFactory::createDataProductAccessLog($datas);

        return $page ? $page : '';
    }

    /**
     * 通用请求
     * @param $request
     * @param $url
     * @return mixed
     */
    public static function execute($request, $url)
    {
        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        return json_decode($result, true);
    }
}
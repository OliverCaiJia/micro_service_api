<?php

namespace App\Services\Core\Data\Oxygendai;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use Carbon\Carbon;
use App\Helpers\Utils;
use App\Models\Factory\OxygenDaiFactory;
use App\Services\Core\Data\Oxygendai\Config\OxygendaiConf;


class OxygendaiService extends AppService
{
    /**申请气球贷接口（单条）
     * @param array $params
     */
    public static function spread($params = [])
    {
        if (!is_array($params)) {

            return ['error' => '参数必须是数组'];
        }
        //token值
        $access_token = self::getAccessToken();
        //request_id
        $request_id = Utils::getMicrotime();
        $request = [
            'json' => [
                'access_token' => $access_token,
                'request_id' => $request_id,
                'mobileNo' => $params['mobile'],
                'mediaSourceCode' => OxygendaiConf::MEDIA_SOURCE_CODE,
                'name' => $params['name'],
                'ipAddr' => $params['created_ip'],
                'receivedChannel' => OxygendaiConf::RECEIVED_CHANNEL,
            ]
        ];
        //拼接url参数
        $ext = '?access_token=' . $access_token . '&request_id=' . $request_id;
        //访问的url链接
        $url = OxygendaiConf::URL . 'open/appsvr/channel/openApi/externalSingleImport' . $ext;
        //访问链接
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $result = str_replace(["\"{", "}\""], ["{", "}"], $result);
        $arr = json_decode($result, true);

        return $arr;

    }

    /**申请气球贷接口(批量)
     * @param $users
     * @return array|mixed
     * $users = [
     *         [
     *          'name'=>awen,
     *          'mobile'=>'18132256858',
     *          'created_ip'=>'127.0.0.1'
     *         ],
     *          ]
     */
    public static function spreadList($users)
    {
        if (!is_array($users)) {
            return ['error' => '参数必须是数组'];
        }
        //token值
        $access_token = self::getAccessToken();
        //request_id
        $request_id = Utils::getMicrotime();
        //custInfo
        $userlist = self::getUserList($users);
        $request = [
            'json' => [
                'access_token' => $access_token,
                'request_id' => $request_id,
                'custInfo' => $userlist
            ]
        ];
        //拼接url参数
        $ext = '?access_token=' . $access_token . '&request_id=' . $request_id;
        //访问的url链接
        $url = OxygendaiConf::URL . 'open/appsvr/channel/openApi/externalListImport' . $ext;
        //访问链接
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $result = str_replace(["\"{", "}\""], ["{", "}"], $result);
        $arr = json_decode($result, true);

        return $arr;


    }

    /**整理数据
     * @param $users
     */
    public static function getUserList($users)
    {
        $result = [];
        foreach ($users as $k => $user) {
            $result[$k]['name'] = $user['name'];
            $result[$k]['mobileNo'] = $user['mobile'];
            $result[$k]['ipAddr'] = $user['created_ip'];
            $result[$k]['mediaSourceCode'] = OxygendaiConf::MEDIA_SOURCE_CODE;
            $result[$k]['receivedChannel'] = OxygendaiConf::RECEIVED_CHANNEL;
        }

        return $result;
    }

    /**
     * 获取访问的token
     * @param $assessTokenUrl
     * @param $clientId
     * @param $clientPassword
     */
    private static function getAccessToken()
    {
        $tokenid = OxygenDaiFactory::getCache(OxygenDaiFactory::TOKENID);
        if (empty($tokenid)) {
            $url = OxygendaiConf::ACCESS_TOKEN_URL;
            $request = [
                'json' => [
                    'client_id' => OxygendaiConf::CLIENT_ID,
                    'grant_type' => OxygendaiConf::GRANT_TYPE,
                    'client_secret' => OxygendaiConf::CLIENT_SECRET,
                ],
            ];
            $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
            $result = $response->getBody()->getContents();
            $arr = json_decode($result, true);
            $tokenid = !empty($arr['data']['access_token']) ? $arr['data']['access_token'] : "";
            //把获取的token放入redis中,过期时间30天
            if (!empty($tokenid)) {
                OxygenDaiFactory::setCache(OxygenDaiFactory::TOKENID, $tokenid, Carbon::now()->addDays(30));
            }
        }
        return $tokenid;

    }
}

<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-1-29
 * Time: 下午7:55
 */

namespace App\Services\Core\Oneloan\Oxygendai;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Services\AppService;
use App\Services\Core\Oneloan\Oxygendai\Config\OxygendaiConf;
use Carbon\Carbon;
use App\Models\Cache\OxygenDaiCache;
use Illuminate\Support\Facades\Log;


class OxygendaiService extends AppService
{
    /**
     * 申请气球贷接口（单条）
     *
     * @param array $params
     * @return bool|array
     * 结果：$arr = [
     *      'ret' => '0',
     *      'msg' => '',
     *      'requestId' => '1520913728965',
     *      'data' => [
     *              'isSuccess' => 'T',
     *              'redirectUrl' => 'https://mphwxtest-stg2.ph.com.cn/m/Market/2017/04/01/step1.html?WT.mc_id=CXX-MKHUOKECEA-&uniqueId=1b21379c88582e69'
     *          ],
     * ]
     */
    public static function spread($params = [])
    {
        if (!is_array($params)) {

            return ['error' => '参数必须是数组'];
        }
        //token值
        $access_token = OxygendaiService::getAccessToken();
        //request_id
        $request_id = Utils::getMicrotime();
        $request = [
            'json' => [
                'access_token' => $access_token,
                'request_id' => $request_id,
                'mobileNo' => $params['mobile'],
                'mediaSourceCode' => OxygendaiConf::MEDIA_SOURCE_CODE,
                'name' => $params['name'],
                'ipAddr' => isset($params['created_ip']) ? $params['created_ip'] : Utils::ipAddress(),
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

        if(isset($arr['ret']))
        {
            //token过期
            if($arr['ret'] == '13002' || $arr['ret'] == '13012')
            {
                OxygenDaiCache::delCache(OxygenDaiCache::TOKENID);
                return false;
            } else {
                if(isset($arr['data']['isSuccess']) && $arr['data']['isSuccess'] == 'T')
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 申请气球贷接口(批量)
     *
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
        $access_token = OxygendaiService::getAccessToken();
        //request_id
        $request_id = Utils::getMicrotime();
        //custInfo
        $userlist = OxygendaiConf::getUserList($users);
        $request = [
            'json' => [
                'access_token' => $access_token,
                'request_id' => $request_id,
                'custInfo' => $userlist
            ]
        ];
        //SLogger::getStream()->info('氧气贷code码',['data'=>$request]);
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

    /**
     * 获取访问的token
     *
     * @return string
     */
    public static function getAccessToken()
    {
        $tokenid = OxygenDaiCache::getCache(OxygenDaiCache::TOKENID);
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
                OxygenDaiCache::setCache(OxygenDaiCache::TOKENID, $tokenid, Carbon::now()->addDays(29));
            }
        }

        return $tokenid;
    }
}

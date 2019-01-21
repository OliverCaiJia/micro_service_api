<?php

namespace App\Services\Core\Club;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;

/**
 * 微论坛服务
 * Class ClubService
 */
class ClubService extends AppService
{

    const DOMAIN = PRODUCTION_ENV ? 'club.sudaizhijia.com' : 'test.club.sudaizhijia.com';
    const HTTPURL = 'http://' . ClubService::DOMAIN . '/';
    const APIURL = ClubService::HTTPURL . 'index.php?c=api&a=puyuetian_api:chklogin';
    const REGISTURL = ClubService::HTTPURL . 'index.php?c=api&a=puyuetian_api:reg';
    const PASSWORDURL = ClubService::HTTPURL . 'index.php?c=api&a=puyuetian_api:uppasswod&return=json';

    /**
     * 身份校验码加密及生成时间
     */
    public function CreateUIA($uia = '')
    {
        //$uia为用户成功登录后返回的身份校验码
        if ($uia)
        {
            $uia = explode('|', $uia);
            return $uia[0] . '|' . strtoupper(md5($uia[1] . time())) . '|' . time();
        }
        else
        {
            return false;
        }
    }

    /**
     * @param array $params
     * @return array
     * 论坛登录
     */
    public static function clubLogin($params = [])
    {
        //post 传值数据
        $request = [
            'json' => [
                'username' => $params['club_username'],
                'password' => $params['club_password'],
                'referer' => $params['referer'],
                'verifycode' => '',
                'app_puyuetian_api_uia_type' => 'login',
                'rnd' => '',
                'return' => 'json',
            ],
        ];

        //发送请求
        $promise = HttpClient::i(['verify' => false])->request('POST', ClubService::APIURL, $request);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);

        $datas = [];
        if ($result['state'] == 'ok' && isset($result['data']) && !empty($result['data']))
        {
            $datas['data'] = $result['data'];
        }
        $datas['code'] = $result['code'];
        $datas['msg'] = $result['msg'];

        return $datas;
    }

    /**
     * @param array $params
     * @return array
     * 论坛注册
     */
    public static function clubRegister($params = [])
    {
        //post 传值数据
        $request = [
            'json' => [
                'username' => $params['username'],
                'password' => $params['password'],
                'phone' => $params['mobile'],
                'sex' => empty($params['sex']) ? 1 : $params['sex'], //默认男
                'nickname' => $params['username'],
                'app_puyuetian_api_uia_type' => 'reg',
                'return' => 'json',
            ],
        ];

        //发送请求
        $promise = HttpClient::i(['verify' => false])->request('POST', ClubService::REGISTURL, $request);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);

        $datas = [];
        if ($result['state'] = 'ok' && isset($result['data']) && !empty($result['data']))
        {
            $datas['data'] = $result['data'];
        }
        $datas['code'] = $result['code'];
        $datas['msg'] = $result['msg'];

        return $datas;
    }

    /**
     * @param array $params
     * @return array
     * 论坛修改密码
     */
    public static function clubPassword($params = [])
    {
        //post 传值数据
        // 去除'\'
        $uia = stripslashes($params['uia']);
        $request = [
            'json' => [
                'old_password' => $params['old_password'],
                'new_password' => $params['new_password'],
                'club_user_id' => $params['club_user_id'],
                'app_puyuetian_api_uia_type' => 'uppasswod',
                'uia' => $uia,
                'rnd' => '',
                'return' => 'json',
            ],
        ];

        //发送请求
        $promise = HttpClient::i(['verify' => false])->request('POST', ClubService::PASSWORDURL, $request);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);

        $datas = [];
        if ($result['state'] == 'ok' && isset($result['data']) && !empty($result['data']))
        {
            $datas['data'] = $result['data'];
        }
        $datas['code'] = $result['code'];
        $datas['msg'] = $result['msg'];

        return $datas;
    }

}

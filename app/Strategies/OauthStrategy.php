<?php

namespace App\Strategies;

use App\Helpers\UserAgent;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\PlatformService;

/**
 * Class OauthStrategy
 * @package App\Strategies
 * 第三方对接策略层
 */
class OauthStrategy extends AppStrategy
{
    /**
     * @param $type
     * @param $platformWebsite
     * @param $mobile
     * @param $userId
     * @return array|string
     * 产品详情——点击借款
     */
    public static function getWebsite($datas = [])
    {
        //平台数据
        //$params = $datas['platform'];
        //产品数据
        $params = $datas['product'];

        switch ($datas['type']) {
            case 1:
                // 非合作点击网页
                $websiteArr = ['url' => $params['official_website']];
                break;
            case 2:
                // 非合作点击微信
                $websiteArr = ['errorCode' => 200, 'url' => ''];
                break;
            case 3:
                // 非合作点击app  preg_match('/iPhone|iPad/i',UserAgent::i()->getUserAgent())
                if (UserAgent::i()->mobileGrade()) {
                    $websiteArr = ['url' => $params['apple_download']];
                } else {
                    $websiteArr = ['url' => $params['anddroid_download']];
                }
                break;
            case 4:
                // 合作店家借款
                if (!$params || empty($datas['user']['mobile'])) {
                    $page = '';
                } else {
                    // h5_register_link 是空返回官网
                    $datas['page'] = !empty($params['h5_register_link']) ? $params['h5_register_link'] : $params['official_website'];
                    //调取service
                    $page = PlatformService::i()->toPlatformService($datas);
                }

                $websiteArr = ['url' => $page];
                break;
            default:
                $websiteArr = ['url' => ''];
                break;
        }

        return $websiteArr;
    }

    /**
     * @param $mobile
     * @param $params
     * @return array
     * 产品/平台数据处理
     */
    public static function getOauthProductDatas($data, $user, $params)
    {
        $data['user']['username'] = isset($user['user']['username']) ? $user['user']['username'] : '';
        $data['user']['mobile'] = isset($user['user']['mobile']) ? $user['user']['mobile'] : '';
        $data['user']['sex'] = isset($user['profile']['sex']) ? $user['profile']['sex'] : '';
        $data['user']['real_name'] = isset($user['profile']['real_name']) ? $user['profile']['real_name'] : '';
        $data['user']['idcard'] = isset($user['profile']['identity_card']) ? $user['profile']['identity_card'] : '';
        //产品数据
        $data['product']['h5_register_link'] = $params['product_h5_url'];
        $data['product']['official_website'] = $params['product_official_website'];
        $data['product']['anddroid_download'] = $params['product_android_download'];
        $data['product']['apple_download'] = $params['product_apple_download'];
        $data['product']['type_nid'] = $params['product_type_nid'];
        $data['product']['channel_status'] = $params['product_channel_status'];
        $data['product']['product_name'] = $params['platform_product_name'];
        //平台数据
        $data['platform']['h5_register_link'] = $params['h5_register_link'];
        $data['platform']['official_website'] = $params['official_website'];
        $data['platform']['anddroid_download'] = $params['anddroid_download'];
        $data['platform']['apple_download'] = $params['apple_download'];
        $data['platform']['type_nid'] = $params['type_nid'];
        $data['platform']['channel_status'] = $params['channel_status'];
        $data['platform']['platform_name'] = $params['platform_name'];
        $data['platform']['platform_id'] = $params['platform_id'];

        return $data ? $data : [];

    }

    /**
     * @param $data
     * @param $type
     * @return int
     * 转化平台返回用户信息数据 —— 发薪贷
     */
    public static function formatFaxindaiIsNewUser($data)
    {
        //是否为机构的新注册用户1:是；0:否；不为‘1’的情况当作‘0’处理, 2通过速贷之家推过来老用户，3其他渠道推过来的用户
        //1平台自有注册用户，2通过速贷之家推过来老用户，3通过速贷之家推的新用户，4其他渠道推过来的用户
        $data = intval($data);
        if ($data == 1) {
            $data = 3;
        } elseif ($data == 2) {
            $data = 2;
        } elseif ($data == 3) {
            $data = 4;
        } else {
            $data = 0;
        }

        return $data;
    }

    /**
     * @param $data
     * @return int
     * 转化平台返回用户信息数据 —— 贷上钱
     */
    public static function formatDaishangqianIsNewUser($data)
    {
        //1：该机构新用户、2：该机构老用户、3：非该机构用户
        //1平台自有注册用户，2通过速贷之家推过来老用户，3通过速贷之家推的新用户，4其他渠道推过来的用户
        $data = intval($data);
        if ($data == 1) {
            $data = 3;
        } elseif ($data == 2) {
            $data = 1;
        } elseif ($data == 3) {
            $data = 3;
        } else {
            $data = 0;
        }

        return $data;
    }

}
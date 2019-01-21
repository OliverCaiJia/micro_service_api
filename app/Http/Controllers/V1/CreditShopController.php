<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Factory\CreditFactory;
use App\Models\Orm\UserCredit;
use Illuminate\Http\Request;
use App\Services\Core\Supports\Duiba\Libs\DuibaLib;
use App\Helpers\RestResponseFactory;
use App\Models\Orm\SystemConfig;

/** 积分商城
 * Class UserSignController
 * @package App\Http\Controllers\V1
 */
class CreditShopController extends Controller
{
    /** 免登陆接口
     * @param Request $request
     */
    public function shop(Request $request)
    {
        // 未登录默认not_login
        $params['uid'] = 'not_login';
        $user = $request->user();
        $redirect = $request->input('redirect');

        //登录 获取积分
        if (!empty($user))
        {
            $params['uid'] = $user->sd_user_id;
            // 查询用户的积分
            $credit = CreditFactory::fetchCredit($user->sd_user_id);
            $params['credits'] = $credit;
        }

        // 获取key和secret
        $appKey = SystemConfig::where('nid', 'con_duiba_appkey')->value('value');
        $appSecret = SystemConfig::where('nid', 'con_duiba_appsecret')->value('value');
        if ($appSecret && $appKey) {
            $params['appKey'] = $appKey;
            $params['appSecret'] = $appSecret;
        }
        else
        {
            return RestResponseFactory::ok(['redirect_url' => '']);
        }

        $url = '';
        if (isset($redirect))
        {
            $params['redirect'] = $redirect;
            $url= DuibaLib::i($params)->buildRedirectAutoLoginRequest();
        }
        else
        {
            $url = DuibaLib::i($params)->buildCreditAutoLoginRequest();
        }

        // 返回积分商城链接
        return RestResponseFactory::ok(['redirect_url' => $url]);
    }
}
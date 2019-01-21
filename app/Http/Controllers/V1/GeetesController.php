<?php
namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Http\Controllers\Controller;
use App\Models\Factory\CacheFactory;
use App\Services\Core\Validator\Geetes\GeetesConfig;
use App\Services\Core\Validator\Geetes\GeetesService;
use Illuminate\Http\Request;

/**
 * Class GeetesController
 * @package App\Http\Controllers\V1
 * 极验
 */
class GeetesController extends Controller
{
    /**
     * @param Request $request
     * 极验 —— 极验一次验证
     */
    public function fetchCaptcha(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        $data = $request->all();
        if ($data['type'] == 'pc') {
            $GtSdk = new GeetesService(GeetesConfig::CAPTCHA_ID, GeetesConfig::PRIVATE_KEY);
        } elseif ($data['type'] == 'mobile') {
            $GtSdk = new GeetesService(GeetesConfig::MOBILE_CAPTCHA_ID, GeetesConfig::MOBILE_PRIVATE_KEY);
        }else {
            $GtSdk = new GeetesService(GeetesConfig::MOBILE_CAPTCHA_ID, GeetesConfig::MOBILE_PRIVATE_KEY);
        }
        
        //这个是用户的标识，或者说是给极验服务器区分的标识，如果你项目没有预先设置，可以像下面这样设置：唯一id
        //12位随机数[数字+大小写字母]最好在加上一个可以生成唯一字符串的函数
        $unique = $data['unique'];
        $status = $GtSdk->pre_process($unique);

        CacheFactory::putValueToCacheForever('gtserver_' . $unique, $status);
        CacheFactory::putValueToCacheForever('geetes_user_id_' . $unique, $unique);

        $gtRes = $GtSdk->get_response_str();
        echo $gtRes;
        die();
    }

    /**
     * @param Request $request
     *
     */
    public function fetchVerification(Request $request)
    {
        
        $data = $request->all();
        //判断移动端与web端
        if ($data['type'] == 'pc') {
            $GtSdk = new GeetesService(GeetesConfig::CAPTCHA_ID, GeetesConfig::PRIVATE_KEY);
        } elseif ($data['type'] == 'mobile') {
            $GtSdk = new GeetesService(GeetesConfig::MOBILE_CAPTCHA_ID, GeetesConfig::MOBILE_PRIVATE_KEY);
        }else {
            $GtSdk = new GeetesService(GeetesConfig::MOBILE_CAPTCHA_ID, GeetesConfig::MOBILE_PRIVATE_KEY);
        }

        $unique = $data['unique'];

        //唯一标识
        $user_id  = CacheFactory::getValueFromCache('geetes_user_id_' . $unique );
        //标识符 1成功 0失败
        $gtserver = CacheFactory::getValueFromCache('gtserver_' . $unique );
        //进行极验二次验证 图片的吻合度
        if ($gtserver == 1) {
            $result = $GtSdk->success_validate($data['geetest_challenge'], $data['geetest_validate'], $data['geetest_seccode'], $user_id);
            if ($result) {
                return RestResponseFactory::ok(['message'=>'success']);
            } else {
                return RestResponseFactory::ok(['message'=>'false']);
            }
        } else {
            // 极验服务器宕机情况下在本地完成二次验证·
            if ($GtSdk->fail_validate($data['geetest_challenge'], $data['geetest_validate'], $data['geetest_seccode'])) {
                return RestResponseFactory::ok(['message'=>'success']);
            } else {
                return RestResponseFactory::ok(['message'=>'false']);
            }
        }
    }
}
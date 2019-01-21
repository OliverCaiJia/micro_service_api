<?php

namespace App\Http\Controllers\V1;

use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Http\Controllers\Controller;
use App\Services\AppService;
use App\Services\Core\Wechat\Applet\AppletService;
use App\Services\Core\Wechat\JssdkService;
use Illuminate\Http\Request;

/**
 * Class WechatController
 * @package App\Http\Controllers\V1
 * 微信 JSSDK
 */
class WechatController extends Controller
{
    /**
     * @param Request $request
     * 微信对接 JSSDK
     */
    public function fetchSignPackage(Request $request)
    {
        //接收访问的URL地址
        $url = $request->input('url');
        //对接微信的ID与秘钥
        //$appId = 'wxd6e7d96d7602b';
        $appId = 'wxd6e7d967602b';
        //$appSecret = 'cf827897a150f998a';
        $appSecret = '76c8b1fc647e91e';
        $obj = new  JssdkService($appId, $appSecret);
        $signPackage = $obj->getSignPackage($url);
        //print_r($signPackage);die;
        return RestResponseFactory::ok($signPackage);
    }

    /**event站 分享
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function fetchEventWechatShare(Request $request)
    {
        //接收访问的URL地址
        $url = $request->input('url');
        //对接微信的ID与秘钥
        $appId = 'wxab78b101369';
        $appSecret = 'acfab251488f2d0ac18d0db2';
        $obj = new  JssdkService($appId, $appSecret);
        $signPackage = $obj->getSignPackage($url);
        //print_r($signPackage);die;
        return RestResponseFactory::ok($signPackage);
    }


}
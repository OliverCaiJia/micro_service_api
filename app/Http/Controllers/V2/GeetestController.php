<?php
namespace App\Http\Controllers\V2;

use App\Helpers\RestResponseFactory;
use App\Http\Controllers\Controller;
use App\Models\Factory\CacheFactory;
use App\Services\Core\Validator\Geetes\GeetesLibService;
use Illuminate\Http\Request;
use App\Helpers\Utils;

/**
 * Class GeetestController
 * @package App\Http\Controllers\V2
 * 极验
 */
class GeetestController extends Controller
{
    /**
     * @param Request $request
     * 极验 —— 极验一次验证 获取极验信息以及uuid,verifyUrl
     */
    public function fetchCaptcha(Request $request)
    {
        $data = $request->all();
        $res = GeetesLibService::o()->startCaptcha($data);
        return $res;
    }

    /** 获取uuid 验证通过后走极验二次验证
     * @param Request $request
     */
    public function fetchVerification(Request $request)
    {
        $data = $request->all();
        return GeetesLibService::o()->verify($data);
    }

    public function test()
    {
        return view('vendor.geetest.test');
    }

    /**
     * 获取uuid数组
     * @param Request $request
     * @param string $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUuid(Request $request)
    {
        $data = $request->all();
        $type = $data['client_type'];
        $urlDomain = substr(str_replace($request->decodedPath(),'',$request->url()),0,-1);
        $geetestUuid = Utils::generate_uuid();
        $geetestData = [
            'geetestUrl_captcha'=>$urlDomain .'/v2/geetest/captcha?uuid=' . $geetestUuid . '&client_type=' . $type,
            'geetestUrl_verification'=>$urlDomain .'/v2/geetest/verification',
            'geetestUuid'=>(string)$geetestUuid
        ];
        CacheFactory::putValueToCacheForever('geetest_uuid_' . $type . '_' . $geetestUuid, $geetestUuid);

        return RestResponseFactory::ok($geetestData);
    }

    /** 验证uuid
     * @param Request $request
     * @param string $type
     * @param string $uuid
     */
    public function verifyUuid(Request $request)
    {
        $data = $request->all();
        $type = $data['client_type'];
        $uuid = $data['uuid'];
        $service = new GeetesLibService();
        if ($service->verifyUuid($type, $uuid))
        {
            return RestResponseFactory::ok(['status' => true]);
        }

        return RestResponseFactory::ok(['status' => false]);
    }

}
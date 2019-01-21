<?php

namespace App\Services\Core\Validator\FaceId;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Services\Core\Validator\ValidatorService;

/**
 * Class FaceidService
 * @package App\Services\Core\Validator\FaceId
 * Face++ 验证
 */
class FaceIdService extends ValidatorService
{

    /**
     * @param array $data
     * @return mixed
     * 检测和识别中华人民共和国第二代身份证。
     */
    public static function fetchOcriIdcardToInfo($image)
    {
        $url = ValidatorService::FACEID_API_URL . '/faceid/v1/ocridcard';
        $apiKey = ValidatorService::getFaceidAppKey();
        $apiSecret = ValidatorService::getFaceidAppSecret();

        $request = [
            'multipart' => [
                [
                    'name' => 'image',
                    'contents' => fopen($image, 'r'),
                ],
                [
                    'name' => 'api_key',
                    'contents' => $apiKey,
                ],
                [
                    'name' => 'api_secret',
                    'contents' => $apiSecret,
                ],
                // 是否返回身份证照片合法性检查结果 “1”：返回； “0”：不返回。
                [
                    'name' => 'legality',
                    'contents' => 1,
                ],
            ],
        ];
        //请求face++
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);
        return $res;
    }

    /**
     * @param array $params
     * @return mixed
     * 此接口提供基于人脸比对的身份核实功能，支持无源比对（直接比对待核实人脸图和参照人脸图）,待核实人脸图可以由FaceID MegLive SDK产品提供。
     */
    public static function verify($params = [])
    {
        $url = ValidatorService::FACEID_API_URL . '/faceid/v2/verify';
        $apiKey = ValidatorService::getFaceidAppKey();
        $apiSecret = ValidatorService::getFaceidAppSecret();

        $request = [
            'multipart' => [
                [
                    'name' => 'api_key',
                    'contents' => $apiKey,
                ],
                [
                    'name' => 'api_secret',
                    'contents' => $apiSecret,
                ],
                //无源对比0，有缘对比1
                [
                    'name' => 'comparison_type',
                    'contents' => '0',
                ],
                //确定待比对图片的类型。取值只为“meglive”、“facetoken”、“raw_image”、“meglive_flash” 四者之一，取其他值返回错误码400（BAD_ARGUMENTS）
                [
                    'name' => 'face_image_type',
                    'contents' => 'meglive',
                ],
                //无缘对比 uuid
                [
                    'name' => 'uuid',
                    'contents' => $params['userId'] . '',
                ],
                //无缘对比 图片1 身份证正面照
                [
                    'name' => 'image_ref1',
                    'contents' => fopen($params['card_front'], 'r'),

                ],
                //无缘对比 图片2 身份证大头照
                [
                    'name' => 'image_ref2',
                    'contents' => fopen($params['card_photo'], 'r'),
                ],
                //在配合MegLive SDK使用时，用于校验上传数据的校验字符串，此字符串会由MegLive SDK直接返回。
                [
                    'name' => 'delta',
                    'contents' => $params['delta'],
                ],
                //MegLive获取最佳照片
                [
                    'name' => 'image_best',
                    'contents' => fopen($params['image_best_url'], 'r'),
                ],
                //MegLive获取全景照片
                [
                    'name' => 'image_env',
                    'contents' => fopen($params['image_env_url'], 'r'),
                ],

            ],
        ];

        //请求face++
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);

        return $res;
    }

    /**
     * 此接口检测一张照片中的人脸，并且将检测出的人脸保存到FaceID平台里，便于后续的人脸比对
     * @param array $params
     * @return mixed
     */
    public static function detect($params = [])
    {
        $url = ValidatorService::FACEID_API_URL . '/faceid/v1/detect';
        $apiKey = ValidatorService::getFaceidAppKey();
        $apiSecret = ValidatorService::getFaceidAppSecret();

        $request = [
            'multipart' => [
                [
                    'name' => 'image',
                    'contents' => fopen($params['image'], 'r'),
                ],
                [
                    'name' => 'api_key',
                    'contents' => $apiKey,
                ],
                [
                    'name' => 'api_secret',
                    'contents' => $apiSecret,
                ],
            ],
        ];
        //请求face++
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);
        return $res;
    }


}
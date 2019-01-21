<?php

namespace App\Services\Core\Platform\Mobp2p;

use App\Helpers\Utils;
use App\Services\Core\Platform\PlatformService;
use App\Services\Core\Platform\Mobp2p\Mobp2pConfig;

/**
 * 手机贷
 */
class Mobp2pService extends PlatformService
{

    public static function fetchMobp2pPage($datas)
    {
        $mobile = $datas['user']['mobile']; //手机号
        $page = $datas['page']; //地址

        $des_key = Mobp2pConfig::DES_KEY;
        $clientPublicKey = chunk_split(Mobp2pConfig::CLIENT_PUBLIC_KEY, 64, "\n");
        $clientPublicKey = "-----BEGIN PUBLIC KEY-----\n" . $clientPublicKey . "-----END PUBLIC KEY-----\n";
        $privateKey      = file_get_contents('rsa_private_key.pem'); // 我们的私钥
        $res             = openssl_pkey_get_private($privateKey);
        if ($res) {
            $sign = '';
            $key  = '';
            $data = Utils::encrypt(json_encode(['phone' => $mobile]), $des_key);
            openssl_sign($data, $sign, $res);
            openssl_free_key($res);
            openssl_public_encrypt($des_key, $key, $clientPublicKey);
            $vargs = http_build_query([
                'channel'    => Mobp2pConfig::CHANNEL,    //第三方接入商标识
                'merchantId' => Mobp2pConfig::MERCHANTID, //第三方接入商编码
                'reqData'    => $data,    //对业务JSON数据做DES加密后的加密串,许做Base64编码
                'sign'       => base64_encode($sign),   //RSA后的签名串
                'key'        => base64_encode($key),    // 随意8为长度DesKEY
                'token'      => md5(time() + mt_rand(1, 9999)),   // 合作渠道商提供的唯一认证令牌
                'type_id'    => 2,
                'utm_tag'    => '',
                'autodown'   => 0

            ]);
            $url   = Mobp2pConfig::getUrl() . '?' . $vargs;
            $page  = $url;
        }
        return $page ? $page : '';
    }


}

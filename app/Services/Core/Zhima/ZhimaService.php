<?php
namespace App\Services\Core\Zhima;
use App\Helpers\Utils;
use App\Services\AppService;
use App\Services\Core\Zhima\Config\ZhimaConfig;
use App\Services\Core\Zhima\SDK\Zmop\ZmopClient;
use App\Services\Core\Zhima\SDK\Zmop\Request\ZhimaCreditScoreGetRequest;
use App\Services\Core\Zhima\SDK\Zmop\Request\ZhimaAuthInfoAuthqueryRequest;
use App\Services\Core\Zhima\SDK\Zmop\Request\ZhimaAuthInfoAuthorizeRequest;
use App\Services\Core\Zhima\SDK\Zmop\Request\ZhimaCreditWatchlistiiGetRequest;
use App\Models\Factory\ZhimaFactory;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Models\Chain\UserZhima\DoZhimaHandler;

class ZhimaService {

    // 参数
    private $ZMOP_SDK_WORK_DIR = '';
    private $ZMOP_SDK_DEV_MODE = '';
    private $ZMOP_SDK_KEY_PATH = '';
    private $PRIVATE_KEY_FILE = '';
    private $ZM_PUBLIC_KEY_FILE = '';
    private $APP_ID = '';


    public static $util;// 单例对象

    /** 单例
     * @return static
     */
    public static function i($params = [])
    {
        if(!(self::$util instanceof static))
        {
            self::$util = new static($params);
        }

        return self::$util;
    }

    // 禁用克隆,完整单例
    public function __clone() {}

    // 私有化构造方法
    private function __construct($params = [])
    {
        $this->ZMOP_SDK_WORK_DIR = __DIR__ . '/SDK/Zmop/';
        $this->ZMOP_SDK_DEV_MODE = true;
        $this->ZMOP_SDK_KEY_PATH = __DIR__ . '/Key/';

        //正式线文件
        if (PRODUCTION_ENV) {
            //商户私钥文件
            $this->PRIVATE_KEY_FILE = $this->ZMOP_SDK_KEY_PATH.'product_rsa_private_key.pem';
            //芝麻公钥文件
            $this->ZM_PUBLIC_KEY_FILE = $this->ZMOP_SDK_KEY_PATH.'product_score_public_key.pem';
            //appid
            $this->APP_ID = ZhimaConfig::APP_ID;
        } else {
            //商户私钥文件
            $this->PRIVATE_KEY_FILE = $this->ZMOP_SDK_KEY_PATH.'uat_rsa_private_key.pem';
            //芝麻公钥文件
            $this->ZM_PUBLIC_KEY_FILE = $this->ZMOP_SDK_KEY_PATH.'uat_score_public_key.pem';
            //appid
            $this->APP_ID = ZhimaConfig::UAT_APP_ID;
        }
    }

    /**
     * 芝麻信用
     * @param array $data = ['userId' => '', 'mobile' => '', 'idcard' => '', 'name' => '', 'pay_type'=1免费/2付费](必填)
     * @param array $data
     * @return array|string
     */
    public function query($data = [])
    {
        // 创建任务流程
        $save = ZhimaFactory::createZhimaTask($data);

        if (!$save)
        {
            return ['errorCode' => 9108, 'errorMessage' => RestUtils::getErrorMessage(9108)];
        }

        $url = ZhimaService::i()->authUrl(['name' => $data['name'], 'idcard' => $data['idcard']]);
        return $url ? $url : '';
//        // 获取openid
//        $openid = ZhimaFactory::fetchOpenId($data['userId']);
//
//        // 存在openid 验证是否授权
//        if ($openid)
//        {
//            $result = json_decode(ZhimaService::i()->isAuth(['openId' => $openid]), true);
//
//            if ($result['success'])
//            {
//                // 已授权 去获取信用分数&行业白名单
//                if ($result['authorized'])
//                {
//                    // 更新任务状态
//                    $update = ZhimaFactory::updateTaskStatus(['where' => 0, 'userId' => $data['userId'], 'step' => 1]);
//                    if (!$update)
//                    {
//                        return ['errorCode' => 9110, 'errorMessage' => RestUtils::getErrorMessage(9110)];
//                    }
//
//                    // 获取行业关注白名单
//                    $list = $this->getZhimaCreditWatchlist($openid);
//
//                    // 错误处理
//                    if (!$list['success'])
//                    {
//                        return ['errorCode' => 9108, 'errorMessage' => RestUtils::getErrorMessage(9108)];
//                    }
//
//                    // 获取芝麻信用分
//                    $res = $this->getZhimaCreditScore($openid);
//
//                    // 错误处理
//                    if (!$res['success'])
//                    {
//                        return ['errorCode' => 9108, 'errorMessage' => RestUtils::getErrorMessage(9108)];
//                    }
//
//                    // 行业关注白名单参数
//                    $watch = [
//                        'user_id' => $data['userId'],
//                        'is_matched' => $list['is_matched'],
//                        'details' => isset($list['details']) ? json_encode($list['details']) : '',
//                        'biz_no' => $list['biz_no'],
//                        'created_at' => date('Y-m-d H:i:s', time()),
//                        'updated_at' => date('Y-m-d H:i:s', time()),
//                        'created_ip' => Utils::ipAddress(),
//                        'updated_ip' => Utils::ipAddress()
//                    ];
//
//                    $oldScore = ZhimaFactory::getOldScore($openid);
//                    $params['transactionId'] = $this->getTransactionId();
//                    $params['openId'] = $openid;
//                    $params['userId'] = $data['userId'];
//                    $params['score_old'] = $oldScore;
//                    $params['score_new'] = $res['zm_score'];
//                    $params['phone'] = $data['mobile'];
//                    $params['identityType'] = 2;
//                    $params['name'] = $data['name'];
//                    $params['idcard'] = $data['idcard'];
//                    $params['watch'] = $watch;
//
//                    $zhimaHandler = new DoZhimaHandler($params);
//                    $re = $zhimaHandler->handleRequest();
//
//                    if (isset($re['error']))
//                    {
//                        return ['errorCode' => 9108, 'errorMessage' => RestUtils::getErrorMessage(9108)];
//                    }
//
//                    return false;
//                } else {
//                    // 未授权 返回授权url
//                    $url = ZhimaService::i()->authUrl(['name' => $data['name'], 'idcard' => $data['idcard']]);
//                    return $url;
//                }
//            } else {
//                // 返回错误信息
//                return ['errorCode' => 9108, 'errorMessage' => RestUtils::getErrorMessage(9108)];
//            }
//        }
//        else {
//            // 未授权 返回授权url
//            $url = ZhimaService::i()->authUrl(['name' => $data['name'], 'idcard' => $data['idcard']]);
//            return $url;
//        }
    }


    /**
     * 获取芝麻信用评分
     * @return string
     */
    public function getZhimaCreditScore($openId)
    {
        //芝麻信用网关地址
        $gatewayUrl = AppService::ZHIMA_API_URL;
        //芝麻分配给商户的 appId
        $appId = $this->APP_ID;
        //数据编码格式
        $charset = 'UTF-8';
        //商户私钥文件
        $privateKeyFile = $this->PRIVATE_KEY_FILE;
        //芝麻公钥文件
        $zmPublicKeyFile = $this->ZM_PUBLIC_KEY_FILE;
        //获取transactionId
        $transactionId = $this->getTransactionId();
        //获取productCode
        $productCode = ZhimaConfig::PRODUCT_CODE;
        $client = new ZmopClient($gatewayUrl,$appId,$charset,$privateKeyFile,$zmPublicKeyFile);
        $request = new ZhimaCreditScoreGetRequest();
        $request->setTransactionId($transactionId);// 必要参数
        $request->setProductCode($productCode);// 必要参数
        $request->setOpenId($openId);// 必要参数
        $response = $client->execute($request);
        return get_object_vars($response);
    }

    /** 根据OpenId查询是否授权
     * @param array $data
     * @return string
     */
    public function isAuth($data = [])
    {
        //芝麻信用网关地址
        $gatewayUrl = AppService::ZHIMA_API_URL;

        //芝麻分配给商户的 appId
        $appId = $this->APP_ID;
        //数据编码格式
        $charset = 'UTF-8';
        //商户私钥文件
        $privateKeyFile = $this->PRIVATE_KEY_FILE;
        // 芝麻公钥文件
        $zmPublicKeyFile = $this->ZM_PUBLIC_KEY_FILE;
        $client = new ZmopClient($gatewayUrl,$appId,$charset,$privateKeyFile,$zmPublicKeyFile);
        $request = new ZhimaAuthInfoAuthqueryRequest();
        $request->setChannel("apppc");
        $request->setPlatform("zmop");
        $request->setIdentityType("0");// 必要参数
        $request->setIdentityParam(json_encode($data));// 必要参数
        $request->setAuthCategory("C2B");// 必要参数
        $response = $client->execute($request);
        return json_encode($response);
    }


    /** 用户授权入口
     * @param $certInfo
     * @param $authCode
     * @param $identityType
     * @return string
     */
    public function authUrl($data)
    {
        // 参数
        $certInfo = [
            'certNo' => $data['idcard'],
            'name' => $data['name'],
            'certType' =>'IDENTITY_CARD'
        ];
        $authCode =[
            'auth_code' => 'M_H5',
            'state' => $data['idcard']
        ];
        $certInfo = json_encode($certInfo);
        $authCode = json_encode($authCode);
        $identityType = 2;

        //芝麻信用网关地址
        $gatewayUrl = AppService::ZHIMA_API_URL;
        //芝麻分配给商户的 appId
        $appId = $this->APP_ID;
        //数据编码格式
        $charset = "UTF-8";
        //商户私钥文件
        $privateKeyFile = $this->PRIVATE_KEY_FILE;
        //芝麻公钥文件
        $zmPublicKeyFile = $this->ZM_PUBLIC_KEY_FILE;
        $client = new ZmopClient($gatewayUrl,$appId,$charset,$privateKeyFile,$zmPublicKeyFile);
        $request = new ZhimaAuthInfoAuthorizeRequest ();
        $request->setIdentityType ($identityType);  // 必要参数
        $request->setIdentityParam ($certInfo);
        $request->setBizParams ($authCode);
        $url = $client->generatePageRedirectInvokeUrl ($request );
        return $url;
    }

    /**根据返回的$params和$sign,得到openId
     * @param $params
     * @param $sign
     * @return string
     */
    public function getScoreOpenId($params, $sign)
    {
        //芝麻信用网关地址
        $gatewayUrl = AppService::ZHIMA_API_URL;
        //芝麻分配给商户的 appId
        $appId = $this->APP_ID;
        //数据编码格式
        $charset = 'UTF-8';
        //商户私钥文件
        $privateKeyFile = $this->PRIVATE_KEY_FILE;
        //芝麻公钥文件
        $zmPublicKeyFile = $this->ZM_PUBLIC_KEY_FILE;
        // 判断串中是否有%，有则需要decode
        $params = strstr ( $params, '%' ) ? urldecode ( $params ) : $params;
        $sign = strstr ( $sign, '%' ) ? urldecode ( $sign ) : $sign;
        $client = new ZmopClient($gatewayUrl,$appId,$charset,$privateKeyFile,$zmPublicKeyFile);
        $openId = $client->decryptAndVerifySign ( $params, $sign );
        return $openId;
    }

    /**
     * 获取行业关注名单
     * @return string
     */
    public function getZhimaCreditWatchlist($openId)
    {
        //芝麻信用网关地址
        $gatewayUrl = AppService::ZHIMA_API_URL;
        //芝麻分配给商户的 appId
        $appId = ZhimaConfig::WATCH_ID;
        //数据编码格式
        $charset = "UTF-8";
        //商户私钥文件
        $privateKeyFile = $this->ZMOP_SDK_KEY_PATH.'rsa_private_key.pem';
        //芝麻公钥文件
        $zmPublicKeyFile = $this->ZMOP_SDK_KEY_PATH.'watchlist_public_key.pem';
        //获取transactionId
        $transactionId = $this->getTransactionId();
        //获取ProductCode
        $productCode = ZhimaConfig::WATCH_LIST_PRODUCT_CODE;
        $client = new ZmopClient($gatewayUrl,$appId,$charset,$privateKeyFile,$zmPublicKeyFile);
        $request = new ZhimaCreditWatchlistiiGetRequest();
        $request->setProductCode($productCode);// 必要参数
        $request->setTransactionId($transactionId);// 必要参数
        $request->setOpenId($openId);// 必要参数
        $response = $client->execute($request);
        return get_object_vars($response);
    }

    /**
     * 业务流水凭证
     * @return string
     */
    public function getTransactionId()
    {
        $date = date('YmdHis', time());
        return $date.mt_rand('1000000000', '9999999999');
    }
}
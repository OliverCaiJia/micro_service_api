<?php
namespace App\Services\Core\SNS\Libs;
use App\Services\AppService;
use App\Helpers\Http\HttpClient;

class SNSService {

    // 注册接口
    private $registerUrl = '';
    // 登录接口
    private $loginUrl = '';

    public static $util;// 单例对象

    /** 静态方法,单例的统一访问入口
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

    // 私有化构造方法 禁止实例化对象
    private function __construct()
    {
        $this->registerUrl = AppService::SNS_URL . '?s=/ucenter/memberSudai/register';
        $this->loginUrl = AppService::SNS_URL . '?s=/ucenter/memberSudai/login';
    }

    // 私有化克隆函数 禁止clone对象
    private function __clone() {}

    /** 注册SNS普通用户
     * @param $params
     * @return mixed|string
     */
    public function register($params)
    {
        $request = [
            'form_params' => [
                'mobnumber' => isset($params['mobile']) ? $params['mobile'] : '',
                'mnickname' => isset($params['nickname']) ? $params['nickname'] : '',
                'mpassword' => isset($params['password']) ? $params['password'] : '',
                'reg_verify' => isset($params['reg_verify']) ? $params['reg_verify'] : '',
                'reg_type' => isset($params['reg_type']) ? $params['reg_type'] : 'email',
                'role' => 1, //角色ID, 默认1
            ],
        ];

        $promise = HttpClient::i(['verify' => false])->request('POST', $this->registerUrl, $request);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);

        return $result;
    }

    /** 登录接口
     * @param $params
     * @return mixed|string
     */
    public function login($params)
    {
        $request = [
            'form_params' => [
                'username' => isset($params['username']) ? $params['username'] : '',
                'password' => isset($params['password']) ? $params['password'] : '',
                'nickname' => isset($params['nickname']) ? $params['nickname'] : '',
                'photo'    => isset($params['photo']) ? $params['photo'] : '',
                'address'  => isset($params['address']) ? $params['address'] : '',
                'birthday' => isset($params['birthday']) ? $params['birthday'] : '',
                'sex'      => isset($params['sex']) ? $params['sex'] : 2,
                'city'     => isset($params['city']) ? $params['city'] : '',
                'is_vip'   => isset($params['is_vip']) ? $params['is_vip'] : '0',
            ]
        ];

        $promise = HttpClient::i(['verify' => false])->request('POST', $this->loginUrl, $request);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);

        return $result;
    }
}
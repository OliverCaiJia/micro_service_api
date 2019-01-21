<?php
namespace App\Services\Core\Platform\Jimu;

use App\Models\Orm\UserAuth;
use App\Models\Orm\UserProfile;
use App\Services\Core\Platform\PlatformService;
use Illuminate\Support\Facades\Cache;

/**
 * 积木盒子
 */
class JimuService extends PlatformService
{
    protected $partner = 'sdzj';
    protected $salt = PRODUCTION_ENV ? '6E7EE5A8DC234esdfC36CF88' : '51A1A92D917EF5BwerwrwqeE62903E2B'; //test

    /*
     * @desc    用户信息接口  sdf->jimu
     * 2.2用户信息接口
     * */

    public function fetchInfo($userId)
    {
        $res  = $this->servicedo($userId);
        if ($res['error_code'] === 0) {
            return $this->getSuccess($res, $userId);
        } else {
            return ['url' => '', 'error_code' => $res['error_code'], 'error_msg' => $res['error_msg']];
//            $this->renderJson($res['error_code'], $res['result_msg'], $res['error_msg']);
        }
    }

    //获取unique_id 和 safe_code
    private function servicedo($id, $newNo = '')
    {
        if ($newNo != '') {
            $proinfo             = UserProfile::select()->where("user_id = $id")->first();
            $proinfo->outApplyNo = $proinfo->outApplyNo . ',' . $newNo;
            $proinfo->save();
        }

        $userInfo = UserAuth::from('sd_user_auth as user')
            ->join('sd_user_profile as profile', 'profile.user_id', '=', 'user.sd_user_id')
            ->where(['user.sd_user_id'=>$id])
            ->first();
        if(empty($userInfo)) {
           return false;
        }
        $userInfo = $userInfo->toArray();
        $outApplyNoArr = explode(',', $userInfo['outApplyNo']);
        $outApplyNo    = $outApplyNoArr[count($outApplyNoArr) - 1];
        $data          = [
            'outApplyNo'     => $outApplyNo,
            'balance'        => $userInfo['balance'],
            'term'           => $userInfo['term'],
            'realName'       => $userInfo['real_name'],
            'idCard'         => $userInfo['identity_card'],
            'mobile'         => $userInfo['mobile'],
            'emContact'      => $userInfo['emergency_contact'],
            'emMobile'       => $userInfo['emergency_contact_mobile'],
            'emRelationship' => $userInfo['emergency_contact_relation'],
            'address'        => $userInfo['address'],
            'income'         => $userInfo['income'],
            'workExperience' => $userInfo['workExperience'],
            'homeSituation'  => $userInfo['marriage'],
            'creditCard'     => $userInfo['creditCard'],
        ];
        //将数据进行处理   获得加密之后的密文
        $res = BaseService::JiMu($data);
        //返回unique_id   safe_code
        if ($res['error_code'] === 0) {
            //将返回结果存到cache中
            Cache::put('unique_id_' . $id,$res['unique_id']);
            Cache::put('safe_code_' . $id,$res['safe_code']);
        }
        return $res;
    }

    //调用service.do后返回结果处理
    public function getSuccess($res, $userId)
    {
        //判断是否授权
        $authCode = Cache::get('authCode_' . $userId);
        //如果authcode为空 说明第一次  否则不是第一次
        if (!empty($authCode)) {
            //判断状态 跳转相应页面
            return $this->getstatus($res['unique_id'], $userId);
        } else {
            //返回给客户端参数  跳转到登陆页面
            return $this->actionAuth($res['unique_id'], $res['safe_code']);
        }
    }

    /*
     * @desc    2.4合作方获取申请状态
     * @param   $outApplyNo     string
     * */

    public function getstatus($applyNo, $userId)
    {
        $needData   = BaseService::threeParam();
        $outApplyNo = [
            'outApplyNo' => $applyNo,
        ];

        //申请状态数组
        $url        = BaseService::BASE_URL . '/3rd/sdzj/status';
        $statusData = array_merge($needData, $outApplyNo);
        $result     = BaseService::curl($statusData, $url);
        $token      = $this->actionToken($userId);
        if (is_array($token)) {
            return ['url' => '', 'error_code' => 1001, 'error_msg' => '获取token失败'];
        }
        if (isset($result['can_apply']) && $result['can_apply']) {
            $newNo = $userId . date('Ymd', time()) . time() . rand(1000, 9999);
            $res   = $this->servicedo($userId, $newNo);
            //调用service.do 并返回访问index页的参数 重定向到index
            if ($res['error_code'] === 0) { //成功跳转到index页面
                return $this->actionIndex($res['unique_id'], $token);
            } else {
                return ['url' => '', 'error_code' => $res['error_code'], 'error_msg' => $res['error_msg']];
//                $this->renderJson($res['error_code'], $res['result_msg'], $res['error_msg']);
            }
        } else {
            //跳转相应页面 判断请求哪个接口
//            $statusArr = [
//                0   => '没有申请',#1
//                100 => '资料填写中',#1
//                101 => '资料审核中',#1
//                102 => '审核通过，等待放款',  # 2
//                103 => '审核拒绝',  #1
//                104 => '用户放弃',#4
//                105 => '资料审核通过，等待签约',#1
//                106 => '放款完成，还款中',#4
//                107 => '还款完成',#4
//            ];
            if (in_array($result['status_code'], [0, 100, 101, 103, 104, 107])) {
                return $this->actionResult($applyNo, $token);
            } elseif (in_array($result['status_code'], [102, 105])) {
                return $this->actionWithdraw($token);
            } elseif (in_array($result['status_code'], [106])) {
                return $this->actionRepayment($token);
            } elseif (in_array($result['status_code'], [10000])) {
                return $this->actionUserinfo($token);
            }
        }
    }

    /*
     * @desc    2.5授权登录接口
     * @param   $unique_id     string
     * @param   $safe_code     string
     * @method  GET
     * @result  return unique_id authCode
     * */

    public function actionAuth($unique_id, $safe_code)
    {
        $url = BaseService::BASE_URL . "/oauth/auth2?unique_id=$unique_id&safe_code=$safe_code";
        $this->returnNeedData($url);
//            header('http/1.1 301 moved permanently');
//            header("Location:$url");
        return ['code' => 200, 'message' => 'ok', 'url' => $url];
//        $this->renderJson(200, 'ok', $url);
    }

    /*
     * 推送状态
     * $param  timestamp  毫秒时间戳
     * $param  unique_id
     * $param  status    状态码
     * $param  sign      加密后的校验码
     */

    public function actionPushstatus()
    {
        $statusArr = [
            100 => '借款中',
            101 => '还款中',
            102 => '已还款',
        ];
        $params    = Yii::$app->request->post();
        if (!empty($params)) {
            extract($params);
        } else {
            $this->renderJson(1001, '参数错误');
        }
        if (!isset($timestamp) || empty($timestamp)) {
            $this->renderJson(1005, '时间戳参数错误');
        }

        if (!isset($sign) || empty($sign)) {
            $this->renderJson(1007, '校验码参数错误');
        }

        $tmpArr = array($this->partner, $this->salt, $timestamp);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr, '&');
        $mySign = md5($tmpStr);
        if ($mySign != $sign) {
            $this->renderJson(1008, '校验码参数错误');
        }

        $statusIds = array_keys($statusArr);
        if (!isset($unique_id) || empty($unique_id)) {
            $this->renderJson(1002, '订单ID参数错误');
        }

        if (!isset($status) || empty($status) || !in_array($status, $statusIds)) {
            $this->renderJson(1003, '状态参数错误');
        }
        $redis     = Yii::$app->redis;
        $setStatus = $redis->set('status_' . $unique_id, $status);
        if ($setStatus) {
            $this->renderJson(200, 'success');
        } else {
            $this->renderJson(1004, 'error');
        }
    }

    /*
     * @desc    2.6获取临时 Token
     * @param   $authCode     string
     * @method  POST
     * @result  return token
     * */

    public function actionToken($userId)
    {
        $authCode  = Cache::get('authCode_' . $userId);
        $needData  = BaseService::threeParam();
        $paramsArr = [
            'authCode' => $authCode,
        ];

        //申请状态数组
        $statusData = array_merge($needData, $paramsArr);
        //调用积木盒子接口
        $url    = BaseService::BASE_URL . '/oauth/token';
        $result = BaseService::curl($statusData, $url);
        if ($result['error_code'] == 0) {
//            $redis->set("token_" . $unique_id, $result['token']);
            return urlencode($result['token']);
        } else {
            return ['code' => 1001, 'message' => '获取token失败', 'url' => ''];
        }
    }

    /*
     * @desc    2.7申请状态接口
     * @param   $authCode     string
     * @method  GET
     * @result  打开 Jimu 读秒借款申请状态页面
     * */

    public function actionResult($unique_id, $token)
    {
        $url = $this->baseUrl . "/3rd/apply/result?unique_id=" . $unique_id . "&token=" . $token;
        $this->returnNeedData($url);
        return ['code' => 200, 'message' => 'ok', 'url' => $url];
//        $this->renderJson(200, 'ok', $url);
    }

    /*
     * @desc    2.8 提现接口
     * @param   $token     string
     * @method  GET
     * @result  打开 Jimu 读秒借款ᨀ现页面
     * */

    public function actionWithdraw($token)
    {

        $url = $this->baseUrl . "/3rd/apply/withdraw?&token=$token";
        $this->returnNeedData($url);
        return ['code' => 200, 'message' => 'ok', 'url' => $url];
//        $this->renderJson(200, 'ok', $url);
    }

    /*
     * @desc    2.9还款接口
     * @param   $token     string
     * @method  GET
     * @result  打开 Jimu 读秒借款还款页面
     * */

    public function actionRepayment($token)
    {
        $url = $this->baseUrl . "/3rd/apply/repayment?&token=$token";
        $this->returnNeedData($url);
        return ['code' => 200, 'message' => 'ok', 'url' => $url];
//        $this->renderJson(200, 'ok', $url);
    }

    /*
     * @desc    2.11 申请首页
     * @param   $token     string
     * @param   $unique_id     string
     * @method  GET
     * @result  打开 Jimu 读秒借款申请首页
     * */

    public function actionIndex($unique_id, $token)
    {
        $url = BaseService::BASE_URL . "/3rd/apply/index?unique_id=" . $unique_id . "&token=" . $token;
        $this->returnNeedData($url);
        return ['code' => 200, 'message' => 'ok', 'url' => $url];
//        $this->renderJson(200, 'ok', $url);
    }

    /*
     * @desc    2.12 用户信息页
     * @param   $token     string
     * @method  GET
     * @result  打开 Jimu 读秒用户信息页，可以查看还款计划，历史借款记录
     * */

    public function actionUserinfo($token)
    {
        $url = $this->baseUrl . "/3rd/apply/user?unique_id=" . $unique_id . "&token=" . $token;
        $this->returnNeedData($url);
        return ['code' => 200, 'message' => 'ok', 'url' => $url];
//        $this->renderJson(200, 'ok', $url);
    }

    /*
     * @desc    2.13 还款记录查询接口
     * @param   $outApplyNo     string
     * @method  POST
     * */

    public function actionRepaymentlog($user_id)
    {
        $session    = \Yii::$app->session;
        $unique_id  = $session->get('unique_id_' . $user_id);
        $outApplyNo = $unique_id;
        $needData   = $this->threeParam();
        $paramsArr  = [
            'outApplyNo' => $outApplyNo,
        ];

        //申请状态数组
        $statusData = array_merge($needData, $paramsArr);
        //调用积木盒子接口
        $url    = $this->baseUrl . '/3rd/apply/repayment/log';
        $result = $this->curl($statusData, $url);
        $result = json_decode($result, true);
    }

    /*
     * 将必要的三个参数拼接到URL上
     */

    public function returnNeedData(&$url)
    {
        $needData = BaseService::threeParam();
        $url .= "&sign=" . $needData['sign'] . "&partner=" . $needData['partner'] . "&timestamp=" . $needData['timestamp'];
    }

    /*
     * @desc    2.10  Jimu 回调 Corp 接口函数
     * @param   $event      string  事件类型:oauth: 发送授权码withdraw：发送ᨀ现额度
     * @param   $unique_id  string  event=oauth 时必填
     * @param   $authCode   string  event=oauth 时必填
     * @param   $outApplyNo string  event= withdraw 时必填  Corp 对应 Jimu 的借款申请唯一标识
     * @param   $amount     string  event= withdraw 时必填  用户提现金额
     * @param   $t_pay      string  event= withdraw 时必填  现记录唯一标识
     * */

    public function actionCallback()
    {
        $params = \Yii::$app->getRequest()->get();
        if (!empty($params)) {
            extract($params);
        } else {
            exit(Json::encode(['status_code' => 1000, 'result_msg' => '参数错误']));
        }
        $eventArr = ['withdraw', 'oauth'];
        if (!isset($event) || !in_array($event, $eventArr)) {
            exit(Json::encode(['status_code' => 1001, 'result_msg' => '事件类型参数错误']));
        }
        switch ($event) {
            case 'oauth':
                if (!isset($unique_id) || trim($unique_id) == '') {
                    exit(Json::encode(['status_code' => 1002, 'result_msg' => 'unique_id必填']));
                }
                if (!isset($authCode) || $authCode == '') {
                    exit(Json::encode(['status_code' => 1003, 'result_msg' => 'authCode必填']));
                }
                //将授权码存入redis unique_id => authcode
                $info   = UserProfile::find()->where("outApplyNo = '$unique_id'")->one();
                $userId = $info->user_id;
                $redis  = Yii::$app->redis;
                $redis->set('authCode_' . $userId, $authCode);
                exit(Json::encode(['status_code' => 200, 'result_msg' => 'OK']));
                break;
            case 'withdraw':
                if (!isset($outApplyNo) || trim($outApplyNo) == '') {
                    exit(Json::encode(['status_code' => 1004, 'result_msg' => 'outApplyNo必填']));
                }
                if (!isset($t_pay) || $t_pay == '') {
                    exit(Json::encode(['status_code' => 1005, 'result_msg' => 't_pay必填']));
                }
                if (!isset($amount) || $amount == '') {
                    exit(Json::encode(['status_code' => 1006, 'result_msg' => 'amount必填']));
                }
                exit(Json::encode(['status_code' => 200, 'result_msg' => 'OK']));
                break;
            case 'status':
                $statusArr = [
                    0   => '没有申请',
                    100 => '资料填写中',
                    101 => '资料审核中',
                    102 => '审核通过，等待放款',
                    103 => '审核拒绝',
                    104 => '用户放弃',
                    105 => '资料审核通过，等待签约',
                    106 => '放款完成，还款中',
                    107 => '还款完成',
                ];

                $statusIds = array_keys($statusArr);
                if (!isset($outApplyNo) || empty($outApplyNo)) {
                    $this->renderJson(1007, '标识ID参数错误');
                }

                if (!isset($status) || empty($status) || !in_array($status, $statusIds)) {
                    $this->renderJson(1008, '状态参数错误');
                }
                $redis     = Yii::$app->redis;
                $setStatus = $redis->set('status_' . $outApplyNo, $status);
                if ($setStatus) {
                    $this->renderJson(200, 'success');
                } else {
                    $this->renderJson(1009, 'error');
                }
                break;
        }
    }

}

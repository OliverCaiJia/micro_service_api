<?php

namespace App\Models\Chain\ShadowSms\Sms\Register;

use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Sms\Register\PutValueToCacheAction;
use App\Models\Orm\SmsMessageBatch;
use App\Models\Factory\SystemFactory;
use App\Strategies\UserStrategy;
use Log;

class CreateEventSmsAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '通知短信创建失败', 'code' => 5);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 发送营销短信
     */
    public function handleRequest()
    {
        if ($this->createEventSms($this->params) == true)
        {
            $this->setSuccessor(new SendRegisterSmsAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 发送营销短信
     * @param $params
     * @return bool
     */
    private function createEventSms($params)
    {
        $version = isset($params['version']) ? intval($params['version']) : UserStrategy::version();
        // 触发间隔
        $interval = SystemFactory::getBatchInterval();

        switch ($version) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
                // 发送下载短信
                $params['type'] = 'download';
                $params['message'] = SystemFactory::getBatchMessage($params['type']);
                if ($params['message']) {
                    $params['triggered_at'] = date('Y-m-d 15:00:00', strtotime('+3 day'));
                    $this->createMessageBatch($params);
                }
                // 发送产品短信
                $params['type'] = 'product';
                $params['message'] = SystemFactory::getBatchMessage($params['type']);
                if ($params['message']) {
                    $params['triggered_at'] = date("Y-m-d H:i:s", time() + 60 * 30);
                    $this->createMessageBatch($params);
                }
                // 发送产品短信
                $params['type'] = 'product2';
                $params['message'] = SystemFactory::getBatchMessage($params['type']);
                if ($params['message']) {
                    $params['triggered_at'] = date('Y-m-d 15:00:00', strtotime('+1 day'));
                    $this->createMessageBatch($params);
                }
                // 微信公众号相关短信
                $params['type'] = 'wechat';
                $params['message'] = SystemFactory::getBatchMessage($params['type']);
                if ($params['message']) {
                    $params['triggered_at'] = date("Y-m-d H:i:s", time() + 60 * 10);
                    $this->createMessageBatch($params);
                }
                // 用户申请相关短信
                $params['type'] = 'application';
                $params['message'] = SystemFactory::getBatchMessage($params['type']);
                if ($params['message']) {
                    $params['triggered_at'] = date("Y-m-d H:i:s", time() + 60 * 30);
                    $this->createMessageBatch($params);
                }
                // 用户资料相关短信
                $params['type'] = 'information';
                $params['message'] = SystemFactory::getBatchMessage($params['type']);
                if ($params['message']) {
                    $params['triggered_at'] = date('Y-m-d H:i:s', strtotime('+1 day'));
                    $this->createMessageBatch($params);
                }
                // 产品推广相关短信
                $params['type'] = 'products';
                $params['message'] = SystemFactory::getBatchMessage($params['type']);
                if ($params['message']) {
                    $params['triggered_at'] = date('Y-m-d H:i:s', strtotime('+2 day'));
                    $this->createMessageBatch($params);
                }
                // 用户vip相关短信
                $params['type'] = 'vip';
                $params['message'] = SystemFactory::getBatchMessage($params['type']);
                if ($params['message']) {
                    $params['triggered_at'] = date('Y-m-d H:i:s', strtotime('+3 day'));
                    $this->createMessageBatch($params);
                }
                // 用户信用相关短信
                $params['type'] = 'report';
                $params['message'] = SystemFactory::getBatchMessage($params['type']);
                if ($params['message']) {
                    $params['triggered_at'] = date('Y-m-d H:i:s', strtotime('+10 day'));
                    $this->createMessageBatch($params);
                }
                // 贷款审核相关短信
                $params['type'] = 'review';
                $params['message'] = SystemFactory::getBatchMessage($params['type']);
                if ($params['message']) {
                    $params['triggered_at'] = date('Y-m-d H:i:s', strtotime('+17 day'));
                    $this->createMessageBatch($params);
                }
                break;
            default:
                // 默认短信
                $params['type'] = 'download';
                $params['message'] = SystemFactory::getBatchMessage($params['type']);
                if ($params['message']) {
                    $params['triggered_at'] = date("Y-m-d H:i:s", time() + 60 * 60 * 2);
                    $this->createMessageBatch($params);
                }

        }
        return true;
    }

    /**
     * 生成系统触发Log
     */
    private function createMessageBatch($params)
    {
        Log::info('createMessageBatch', $params);
        // 暂时不发送注册营销短信
//        return true;
        SmsMessageBatch::create([
            'user_id' => $params['user_id'],
            'status' => 0, //0 待触发 1 已触发
            'type' => $params['type'],
            'mobile' => $params['mobile'],
            'content' => $params['message'],
            'triggered_at' => $params['triggered_at'],
            'create_at' => date("Y-m-d H:i:s"),
            'server_name' => $_SERVER['SERVER_NAME'],
            'server_ip' => Utils::ipAddress()
        ]);
    }

}

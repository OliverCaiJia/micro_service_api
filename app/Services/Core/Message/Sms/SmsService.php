<?php

namespace App\Services\Core\Message\Sms;

use App\Helpers\UserAgent;
use App\Models\Factory\SmsFactory;
use App\Models\Orm\MessageLog;
use App\Services\AppService;
use App\Services\Core\Message\Sms\Changzhuo\ChangzhuoService;
use App\Services\Core\Message\Sms\Chuanglan\C253Service;
use App\Services\Core\Message\Sms\Chuanglan\ChuanglanService;
use App\Services\Core\Message\Sms\Dahansantong\DahansantongService;
use App\Services\Core\Message\Sms\Wwtl\WwtlService;
use App\Services\Core\Message\Sms\Yimei\YimeiService;
use Carbon\Carbon;
use DB;
use App\Helpers\Utils;
use Cache;
use App\Helpers\Generator\TokenGenerator;
use Log;

class SmsService extends AppService
{
	public static $services;

	public static function i()
	{

		if (!(self::$services instanceof static))
		{
			self::$services = new static();
		}

		return self::$services;
	}

	/**
	 * 根据系统参数配置去选择短信通道
	 * @param $data
	 * @return mixed|\Psr\Http\Message\ResponseInterface
	 */
	public function to($data, $shadow_nid = 'sudaizhijia')
	{
		$ipNums = $this->limitIp($shadow_nid);
		if ($ipNums >= 36) return false;
		$mobileNums = $this->limitMobile($data['mobile'], $shadow_nid);
		if ($mobileNums >= 6 ) return false;
		$uaNums = $this->limitUA($shadow_nid);
		if ($uaNums >= 10000) return false;
		$config = SmsFactory::getShadowSmsConfig($shadow_nid);

		$name = '';
		if (empty($config))
        {
             $name = 'chuanglan_shadow_' . $shadow_nid;
             $data['channel'] = 'chuanglan_' . $shadow_nid;
        } else{
            $nid = explode('_', $config['nid']);
            $name = $nid[0];
            $data['channel'] = $nid[0] . '_' . $nid[2];
        }

		switch ($name)
		{
			case 'chuanglan':
				$data['nid'] = $this->sendBefore($data);
				$re = C253Service::i()->send($data, $config);
				break;
			case 'changzhuo':
				$data['nid'] = $this->sendBefore($data);
				$re = ChangzhuoService::i()->send($data, $config);
				break;
			case 'wwtl':
				$data['nid'] = $this->sendBefore($data);
				$re = WwtlService::i()->send($data, $config);
				break;
			case 'yimei':
				$data['nid'] = $this->sendBefore($data);
				$re = YimeiService::i()->send($data, $config);
				break;
            case 'dahansantong':
                $data['nid'] = $this->sendBefore($data);
                $re = DahansantongService::i()->send($data, $config);
                break;
			default:
                $config = SmsFactory::getShadowSmsConfigByNid('chuanglan_' . $shadow_nid);
                $data['nid'] = $this->sendBefore($data);
                $re = ChuanglanService::i()->send($data, $config);
                break;
		}
		return $re;
	}

	/**
	 * 发送短信之前入库创建发送内容
	 */
	protected function sendBefore($data)
	{
		$model = new MessageLog();
		$model->mobile = $data['mobile'];
        $model->nid = TokenGenerator::generateToken();
		$model->content = $data['message'];
		$model->send_time = date('Y-m-d H:i:s',time());
		$model->channel = $data['channel'];
        //1 验证码短信 2 通知短信
		$model->send_type = isset($data['send_type']) ? $data['send_type'] : 1;//验证码短信
		$model->auto= 0; //0为用户触发 1为系统发送
        $model->code = isset($data['code']) ? $data['code'] : '';
        $model->code_time = date('Y-m-d H:i:s',time()+ 60);
		$model->send_ip = Utils::ipAddress();
		$model->user_agent = UserAgent::i()->getUserAgent();
		$model->save();

        return $model->nid;
	}

	

	/**
	 * 限制ip
	 */
	protected  function limitIp($shadow_nid)
	{
		$ip = Utils::ipAddress();
        $key = 'sd_ip_value_' . $shadow_nid .'_' . $ip;
        if (Cache::has($key))
        {
            Cache::increment($key);
        }
        else
        {
            Cache::put($key, 1, Carbon::now()->second(60 * 60 * 24));
        }
        return Cache::get($key);
    }


	/**
	 * 手机号限制
	 */
	protected  function limitMobile($mobile, $shadow_nid)
	{
		$key = 'sd_mobile_value_' .$shadow_nid . '_' . $mobile;
        if (Cache::has($key))
        {
            Cache::increment($key);
        }
        else
        {
            Cache::put($key, 1, Carbon::now()->second(60 * 60 * 24));
        }
        return Cache::get($key);
    }

    /**
	 * UA限制
	 */
	protected  function limitUA($shadow_nid)
	{
		$key = 'sd_ua_value_' . $shadow_nid . '_' . md5(UserAgent::i()->getUserAgent());
        if (Cache::has($key))
        {
            Cache::increment($key);
        }
        else
        {
            Cache::put($key, 1, Carbon::now()->second(60 * 60 * 24));
        }
        return Cache::get($key);
    }
}
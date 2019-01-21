<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\AccountMessage;
use App\Models\Orm\AccountMessageConfig;
use App\Models\Orm\SystemConfig;
use App\Services\Core\Sms\SmsService;
use App\Strategies\SmsStrategy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * 短信工厂
 * Class SmsFactory
 * @package App\Models\Factory
 */
class SmsFactory extends AbsModelFactory
{

    /**
     * 把短信验证码存储在cache中（过期时间为60s）
     * @param $key
     * @param $value
     * @return bool
     */
    public static function putSmsCodeToCache($key, $value, $sec = 100)
    {
        Cache::put($key, $value, Carbon::now()->second($sec));
        return true;
    }


    /**
     * 从cache中获取存储的短信验证码
     * @param $key
     */
    public static function getSmsCodeFromCache($key)
    {
        return Cache::get($key);
    }


    /**
     * 把发送短信验证码时生成的32二位随机数存储进cache
     * @param $key
     * @param $value
     * @return bool
     */
    public static function putSmsRandomToCache($key, $value,$sec = 100)
    {
        Cache::put($key, $value, Carbon::now()->second($sec));
        return true;
    }


    /**
     * 从cache中取出发送短信验证码时存进的随机数
     * @param $key
     * @return mixed
     */
    public static function getSmsRandomFromCache($key)
    {
        return Cache::get($key);
    }

    /**
     * @param $mobile
     * 验证短信1分钟之内不能重复发送
     */
    public static function checkCodeExistenceTime($mobile,$type)
    {
        $codeArr = SmsStrategy::getCodeKeyAndSignKey($mobile,$type);
        if(CacheFactory::existValueFromCache($codeArr['codeKey'])) {
            return false;
        }
        return true;
    }

    /**
     * 根据nid查找系统配置中的短信通道的value
     * @param $nid
     */
    public static function getSmsSystemConfig($config = 'con_sms_config')
    {
        $system_config = SystemConfig::select('value')->where('nid', '=', $config)->where('status', '=', '1')->first();
        return $system_config->value;
    }

    /** 根据nid获取当前马甲配置的短信通道的value
     * @param string $config
     */
    public static function getShadowSmsConfig($shadow_nid = 'shadow_jieqian360')
    {
        $config = AccountMessageConfig::select('account_message_id')->where('shadow_nid', $shadow_nid)->where('status', 1)->first();
        if ($config)
        {
            $message = AccountMessage::select('nid', 'username', 'password', 'id_code', 'url', 'sign')->where('id', $config->account_message_id)->first();
            return $message ? $message->toArray() : [];
        }

        return [];
    }

    /** 获取大汉三通的配置
     * @param string $message_nid
     * @return array
     */
    public static function getShadowSmsConfigByNid($message_nid = 'dahansantong_shadow_jieqian360')
    {
        $message = AccountMessage::select('nid', 'username', 'password', 'id_code', 'url')->where('nid', $message_nid)->first();
        return $message ? $message->toArray() : [];
    }

    /**
     * @param array $params
     * @return mixed
     * 填写完整信用资料，发送短信通知
     */
    public static function SendSmsFromUserinfoComplete($smsData = [])
    {
        $data['mobile']  = $smsData['mobile'];
        $data['message'] = $smsData['content'];
        $data['code']    = '';
        $data['send_type'] = 2;
        $re = SmsService::i()->to($data);
        return $re;
    }

}

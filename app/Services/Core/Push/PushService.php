<?php
namespace App\Services\Core\Push;

use App\Helpers\Logger\SLogger;
use App\Models\Factory\CacheFactory;
use App\Services\AppService;
use App\Services\Core\Store\Qiniu\QiniuService;
use JPush\Client;
use JPush\Exceptions\JPushException;

/**
 * Class PushService
 * @package App\Services\Core\Store\Push
 * 推送
 */
class PushService extends AppService
{
    private static $app_key = '379c9d17234EFa18412f80';              //填入你的app_key
    private static $master_secret = '59305de2134Fc420d6c08951';        //填入你的master_secret
    
    /**
     * @param array $params
     * @param array $eventHeap
     * @return bool
     * 推送消息
     */
    public static function sendPush($params = [], $eventHeap = [])
    {
        $client = new Client(self::$app_key, self::$master_secret);

        //参数
        $user_id = $params['user_id'];
        $title   = $eventHeap['title'];
        $content = $eventHeap['content'];
        $src     = $eventHeap['src'];
        $url     = $eventHeap['url'];
        
        //用户设备号
        if (!CacheFactory::existValueFromCache('jpush_registration_id_' . $user_id)) {
            return false;    //registrationId is not exist
        }
        $registration_id = CacheFactory::getValueFromCache('jpush_registration_id_' . $user_id);

        $push            = $client->push()
            ->setPlatform('all')
            ->message('jpush', array(
                'title'        => 'yeer',
                'content_type' => 'text',
                'extras'       => array(
                    'title'   => $title,
                    'content' => $content,
                    'src'     => QiniuService::getImgs($src),
                    'url'     => $url,
                ),
            ));
        $option          = array(
            'sendno'          => time(),          //推送序号
            'time_to_live'    => 86400,           //推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送
            'apns_production' => true,            //True 表示推送生产环境，False 表示要推送开发环境
        );
        $push->options($option);

        if ($registration_id) {
            $push->addRegistrationId($registration_id);
        }
        try {
            $response = $push->send();

        } catch (JPushException $e) {
            // try something else here
            SLogger::getStream()->info('jpush推送错误',$e);
        }
    }
}
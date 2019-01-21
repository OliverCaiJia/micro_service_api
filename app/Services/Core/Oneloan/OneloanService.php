<?php

namespace App\Services\Core\Oneloan;

use App\Constants\SpreadNidConstant;
use App\Events\Oneloan\Partner\UserChunyuEvent;
use App\Events\Oneloan\Partner\UserDongfangEvent;
use App\Events\Oneloan\Partner\UserFinanceEvent;
use App\Events\Oneloan\Partner\UserGongyinyingEvent;
use App\Events\Oneloan\Partner\UserHengchangEvent;
use App\Events\Oneloan\Partner\UserHengyiEvent;
use App\Events\Oneloan\Partner\UserHoubenEvent;
use App\Events\Oneloan\Partner\UserHougedaiEvent;
use App\Events\Oneloan\Partner\UserLoanEvent;
use App\Events\Oneloan\Partner\UserMiaodaiEvent;
use App\Events\Oneloan\Partner\UserMiaolaEvent;
use App\Events\Oneloan\Partner\UserNewLoanEvent;
use App\Events\Oneloan\Partner\UserNiwodaiEvent;
use App\Events\Oneloan\Partner\UserOxygendaiEvent;
use App\Events\Oneloan\Partner\UserPaipaidaiEvent;
use App\Events\Oneloan\Partner\UserRenxinyongEvent;
use App\Events\Oneloan\Partner\UserRongshidaiEvent;
use App\Events\Oneloan\Partner\UserXiaoxiaoEvent;
use App\Events\Oneloan\Partner\UserYouliEvent;
use App\Events\Oneloan\Partner\UserZhongtengxinEvent;
use App\Events\Oneloan\Partner\UserInsuranceEvent;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\UserSpreadFactory;
use App\Services\AppService;
use DB;

/**
 * 一键贷
 * Class OneloanService
 * @package App\Services\Core\Oneloan
 */
class OneloanService extends AppService
{

    public static $services;

    public static function i()
    {

        if (!(self::$services instanceof static)) {
            self::$services = new static();
        }

        return self::$services;
    }

    /**
     * 根据参数去推送产品
     * @param array $params
     * @return mixed
     */
    public function to($params = [])
    {
        $typeNid = $params['type_nid'];
        //SLogger::getStream()->info('推送type_nid确认',['data'=>$typeNid]);
        switch ($typeNid) {
            case SpreadNidConstant::SPREAD_HEINIU_NID:
                // 触发赠险事件
                event(new UserInsuranceEvent($params));
                break;
            case SpreadNidConstant::SPREAD_DONGFANG_NID:
                // 东方事件
                event(new UserDongfangEvent($params));
                break;
            case SpreadNidConstant::SPREAD_XIAOXIAO_NID:
                // 小小金融事件
                event(new UserFinanceEvent($params));
                break;
            case SpreadNidConstant::SPREAD_HENGCHANG_NID:
                // 恒昌事件
                event(new UserHengchangEvent($params));
                break;
            case SpreadNidConstant::SPREAD_HOUBEN_NID:
                // 厚本事件
                event(new UserHoubenEvent($params));
                break;
            case SpreadNidConstant::SPREAD_ZHUDAIWANG_NID:
                // 助贷网事件
                event(new UserLoanEvent($params));
                break;
            case SpreadNidConstant::SPREAD_XINYIDAI_NID:
                // 新一贷事件
                event(new UserNewLoanEvent($params));
                break;
            case SpreadNidConstant::SPREAD_OXYGENDAI_NID:
                // 氧气贷事件
                event(new UserOxygendaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_PAIPAIDAI_NID:
                // 拍拍贷事件
                event(new UserPaipaidaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_XIAOXIAO_SECOND_NID:
                // 小小金融2事件
                event(new UserXiaoxiaoEvent($params));
                break;
            case SpreadNidConstant::SPREAD_YOULI_NID:
                // 有利保险事件
                event(new UserYouliEvent($params));
                break;
            case SpreadNidConstant::SPREAD_ZHONGTENGXIN_NID:
                // 中腾信事件
                event(new UserZhongtengxinEvent($params));
                break;
            case SpreadNidConstant::SPREAD_MIAODAI_NID:
                // 秒贷事件
                event(new UserMiaodaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_GONGYINYING_NID:
                // 工银英事件
                event(new UserGongyinyingEvent($params));
                break;
            case SpreadNidConstant::SPREAD_RONGSHIDAI_NID:
                // 融时代事件
                event(new UserRongshidaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_NIWODAI_NID;
                //你我贷
                event(new UserNiwodaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_MIAOLA_NID;
                //你我貸-秒啦事件
                event(new UserMiaolaEvent($params));
                break;
            case SpreadNidConstant::SPREAD_HENGYIDAI_NID;
                //恒昌 - 恒易贷
                event(new UserHengyiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_HOUGEDAI_NID;
                //猴哥贷
                event(new UserHougedaiEvent($params));
                break;
            case SpreadNidConstant::SPREAD_CHUNYU_NID;
                //春雨贷
                event(new UserChunyuEvent($params));
                break;
            case SpreadNidConstant::SPREAD_RENXINYONG_NID;
                //任信用
                event(new UserRenxinyongEvent($params));
                break;
            default:
        }

        return true;
    }

}

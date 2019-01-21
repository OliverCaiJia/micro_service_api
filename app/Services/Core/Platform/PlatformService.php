<?php

namespace App\Services\Core\Platform;

use App\Models\Factory\OauthFactory;
use App\Services\AppService;
use App\Services\Core\Platform\Daishangqian\DaishangqianService;
use App\Services\Core\Platform\Faxindai\FaxindaiService;
use App\Services\Core\Platform\Jimu\JimuService;
use App\Services\Core\Platform\Jindoukuaidai\JindoukuaidaiService;
use App\Services\Core\Platform\Jiufuqianbao\Jiufudingdang\JiufudingdangService;
use App\Services\Core\Platform\JsXianjinxia\Xianjinxia\XianjinxiaService;
use App\Services\Core\Platform\Mobp2p\Mobp2pService;
use App\Services\Core\Platform\Renxinyong\Renxinyong\RenxinyongService;
use App\Services\Core\Platform\Xyqb\XyqbService;
use App\Services\Core\Platform\Xinerfu\Xianjindai\XianjindaiService;
use App\Services\Core\Platform\Yirendai\Yirendai\YirendaiService;
use App\Services\Core\Platform\Jiufuwanka\Xianjin\JiufuwankaxianjinService;
use DB;

class PlatformService extends AppService
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
     * @param $params
     * @return mixed
     * 获取对接url
     */
    public function toPlatformService($datas)
    {
        $page = $datas['page'];
        //类型id
        //$typeNid = $datas['platform']['type_nid'];
        $typeNid = $datas['product']['type_nid'];
        //判断对接开关
        $productId = $datas['productId']; //产品id
        $channelStatus = 1; //1开
        $channelStatus = OauthFactory::checkProductChannelStatus($productId, $channelStatus);
        if (!$channelStatus || empty($typeNid)) {
            return $page;
        }
        switch ($typeNid) {
            case 'DM':    //读秒/积木
                $service = new JimuService();
                $res = $service->fetchInfo($datas['userId']);
                if (!empty($res['url'])) {
                    $page = $res['url'];
                }
                break;
            case 'FXD':     //发薪贷/应急贷
                $page = FaxindaiService::fetchFaxindaiUrl($datas);
                break;
            case 'XYQB':     //量化派/信用钱包
                $page = XyqbService::fetchQuantgroupUrl($datas);
                break;
            case 'SJD':    //手机贷/简单易贷
                $page = Mobp2pService::fetchMobp2pPage($datas);
                break;
            case 'DSQ':     //贷上钱/贷上钱
                $page = DaishangqianService::fetchDaishangqianUrl($datas);
                break;
            case 'JFDDD':   //玖富钱包/玖富叮当贷
                $page = JiufudingdangService::fetchJiufudingdangUrl($datas);
                break;
            case 'XJD':       // 信而富/现金贷
                $page = XianjindaiService::fetchXinerfuUrl($datas);
                break;
            case 'XJX':      // 极速现金侠/现金侠
                $page = XianjinxiaService::fetchXianjinxiaUrl($datas);
                break;
            case 'YRD':
                $page = YirendaiService::fetchYirendaiUrl($datas);
                break;
            case 'JFWK-XJ':   //玖富万卡/玖富万卡现金
                $page = JiufuwankaxianjinService::fetchJiufuwankaUrl($datas);
                break;
            case 'JDKD': //筋斗快贷
                $page = JindoukuaidaiService::fetchJindoukuaidaiUrl($datas);
                break;
            case 'RXY': //任信用
                $page = RenxinyongService::fetchRenxinyongUrl($datas);
                break;
            default:
                break;
        }
        return $page;
    }

    /**
     * @return array
     * 对接平台地址
     */
    public function getCooperate()
    {
        return [
            //历史地址  'url' => 'http://116.236.22.158:8020/fxd-h5/page/thirdIndex.html',
            'faxindai' => [
                'platform_name' => '发薪贷',
            ],
            'xinyongqianbao' => [
                'platform_name' => '信用钱包',
                //'url' => 'http://61.50.43.14:9001/app/login'   //测试环境
                'url' => 'http://auth.xyqb.com/app/login'   //线上环境
            ],
            'shoujidai' => [
                'platform_name' => '手机贷',
                'url' => 'http://m.mobp2p.com/union/login/' //生产环境
                //'url' => 'http://116.228.32.34:7070/wap/union/login' //测试环境
            ],
            'daishangqian' => [
                'platform_name' => '贷上钱',
                'url' => 'https://api.daishangqian.com/asset/third/register' //生产环境
                //'url' => 'http://paydayloan.fond.io/asset/third/register' //测试环境
            ],
        ];
    }


}
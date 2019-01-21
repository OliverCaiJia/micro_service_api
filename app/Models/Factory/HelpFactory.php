<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\Help;
use App\Models\Orm\HelpType;
use App\Models\Orm\Suggest;
use App\Services\Core\Store\Qiniu\QiniuService;

class HelpFactory extends AbsModelFactory
{
    /**
     * @param $helpTypeArr
     * @return array
     * 帮助中心——帮助列表数据
     */
    public static function fetchHelpLists($helpTypeArr)
    {
        foreach ($helpTypeArr as $key => $val) {
            $helpTypeArr[$key]['img_link'] = QiniuService::getImgs($val['img_link']);
            $helpTypeArr[$key]['list'] = Help::select(['title', 'content'])->where(['type_id' => $val['type_id']])
                ->get()->toArray();
        }

        return $helpTypeArr ? $helpTypeArr : [];
    }

    /**
     * @param $param
     * @return array
     * 根据类型id获取帮助列表
     */
    public static function fetchHelpsByTypeId($param)
    {
        $helps = Help::select(['title', 'content'])->where(['type_id' => $param])
            ->get()->toArray();
        return $helps ? $helps : [];
    }

    /**
     * @return array
     * 帮助中心——帮助分类数据
     */
    public static function fetchHelpTypes()
    {
        $helpTypeArr = HelpType::select(['sd_help_type_id as type_id', 'type_name', 'img_link'])
            ->orderBy('position_sort')
            ->get()->toArray();

        return $helpTypeArr ? $helpTypeArr : [];
    }

    /**
     * @param $param
     * @return mixed|string
     * 根据唯一标识获取帮助类型
     */
    public static function fetchTypeIdByNid($param)
    {
        $helpType = HelpType::select(['sd_help_type_id as type_id', 'type_name'])
            ->where(['type_nid' => $param])
            ->first();

        return $helpType ? $helpType->type_id : '';
    }

    /**
     * @param array $data
     * @return bool
     * 反馈 —— 添加反馈
     * * IOS & Android
     * 除了用户的留言外，还要返回用户的手机型号，系统版本号（安卓、IOS），屏幕尺寸
     * H5
     * 除了用户的留言外，还要返回用户的屏幕尺寸，使用浏览器（微信，Safari，）
     */
    public static function createFeedback($data = [])
    {
        $feedback = new Suggest();
        $feedback->content = $data['feedback'];
        $feedback->user_id = $data['userId'];
        $feedback->create_date = date('Y-m-d H:i:s', time());
        $feedback->create_ip = Utils::ipAddress();
        $feedback->version = !empty($data['version']) ? $data['version'] : '';        //系统版本
        $feedback->browser = !empty($data['browser']) ? $data['browser'] : '';        //浏览器 手机型号
        $feedback->screen_size = !empty($data['screenSize']) ? $data['screenSize'] : '';  //屏幕尺寸
        $feedback->phone_model = !empty($data['phoneModel']) ? $data['phoneModel'] : '';  //手机型号
        $feedback->program_version = !empty($data['programVersion']) ? $data['programVersion'] : '';  //程序名和版本
        return $feedback->save();
    }

    /**
     * @param array $params
     * @return array
     * 分类中心图片
     */
    public static function fetchHelpTypeImg($params = [])
    {
        foreach ($params as $key=>$val) {
            $params[$key]['img_link'] = QiniuService::getImgs($val['img_link']);
        }

        return $params;
    }
}
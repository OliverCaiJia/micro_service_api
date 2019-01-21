<?php

namespace App\Strategies;

use App\Strategies\AppStrategy;
use App\Helpers\DateUtils;
use App\Services\Core\Store\Qiniu\QiniuService;

/**
 * 通知公共策略
 *
 * @package App\Strategies
 */
class NoticeStrategy extends AppStrategy
{
    /**
     * @param $noticeArr
     * 通知——获取通知列表数据处理
     */
    public static function getNoticeLists($indent, $noticeArr)
    {
        //发送人群  to_use 0全部 1分组
        $noticeData = [];
        foreach ($noticeArr as $key => $val) {
            $group = explode(',', $val['user_group']);
            switch ($val['to_users']) {
                case 0: //全部
                    $noticeData[$key]['id'] = $val['id'];
                    $noticeData[$key]['title'] = $val['title'];
                    $noticeData[$key]['content'] = $val['content'];
                    $noticeData[$key]['create_time'] = DateUtils::formatDate($val['update_time']);
                    if ($val['be_used'] == 1) {
                        $noticeData[$key]['src'] = QiniuService::getImgs($val['src']);
                        $noticeData[$key]['app_link'] = $val['app_link'];
                        $noticeData[$key]['url'] = $val['url'];
                        $noticeData[$key]['notice_sign'] = 1;
                    } else {
                        $noticeData[$key]['notice_sign'] = 2;
                    }

                    break;
                case 1:
                    //用户身份是否符合要求
                    if (in_array($indent, $group)) {
                        $noticeData[$key]['id'] = $val['id'];
                        $noticeData[$key]['title'] = $val['title'];
                        $noticeData[$key]['content'] = $val['content'];
                        $noticeData[$key]['create_time'] = DateUtils::formatDate($val['update_time']);
                        if ($val['be_used'] == 1) {
                            $noticeData[$key]['src'] = QiniuService::getImgs($val['src']);
                            $noticeData[$key]['app_link'] = $val['app_link'];
                            $noticeData[$key]['url'] = $val['url'];
                            $noticeData[$key]['notice_sign'] = 1;
                        } else {
                            $noticeData[$key]['notice_sign'] = 2;
                        }
                    }
                    break;
            }
        }
        $noticeData = DateUtils::formatArray($noticeData);
        return $noticeData ? $noticeData : [];
    }
}

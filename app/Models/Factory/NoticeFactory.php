<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\Notice;

/**
 * 通知处理工厂类
 */
class NoticeFactory extends AbsModelFactory
{
    /**
     * 资讯 —— 获取所有资讯
     */
    public static function fetchNoticeLists()
    {
        $noticeArr = Notice::select(['id', 'name', 'title', 'content',
            'update_time', 'src', 'app_link', 'url', 'to_users', 'user_group', 'be_used'])
            ->orderBy('update_time', 'desc')
            ->where(['status' => 0])
            ->get()->toArray();
        return $noticeArr ? $noticeArr : [];
    }
}

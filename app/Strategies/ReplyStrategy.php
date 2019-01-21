<?php

namespace App\Strategies;

use App\Helpers\DateUtils;
use App\Helpers\Utils;
use App\Strategies\AppStrategy;

/**
 * Class SexStrategy
 * @package App\Strategies
 * 回复公共策略
 */
class ReplyStrategy extends AppStrategy
{
    /**
     * @param $replys
     * @return mixed
     * 回复数据处理
     */
    public static function getCommentReplys($replys)
    {
        foreach ($replys as $key => $val) {
            $params[$key]['id'] = $val['id'];
            $params[$key]['content'] = $val['content'];
            $params[$key]['username'] = $val['username'];
            $params[$key]['user_photo'] = $val['user_photo'];
            $params[$key]['is_useful'] = $val['is_useful'];
            $params[$key]['create_date'] = DateUtils::formatDateToMdhi($val['created_at']);
            $params[$key]['use_count'] = DateUtils::formatMathToThous($val['use_count']);
            $params[$key]['reply_count'] = DateUtils::formatMathToThous($val['reply_count']);
            $params[$key]['total_count'] = intval($val['total_count']);
            $params[$key]['parent_username'] = isset($val['parent']['username']) ? $val['parent']['username'] : '';
        }
        return $params ? $params : [];
    }
}
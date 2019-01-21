<?php

namespace App\Strategies;

use App\Strategies\AppStrategy;

/**
 * 分页公共策略
 *
 * Class UserStrategy
 * @package App\Strategies
 */
class PageStrategy extends AppStrategy
{
    /**
     * @param $count
     * @param $pageSize
     * @param $pageNum
     * 分页
     */
    public static function getPage($count, $pageSize = 1, $pageNum = 5)
    {
        /*分页start*/
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset         = ($pageSize - 1) * $pageNum;
        $limit          = $pageNum;
        $page['limit']  = $limit;
        $page['offset'] = $offset;
        $page['pageCount'] = $countPage;
        return $page;
    }

}

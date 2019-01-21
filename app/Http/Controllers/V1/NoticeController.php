<?php
namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\NoticeFactory;
use App\Strategies\NoticeStrategy;
use Illuminate\Http\Request;

/**
 * Class NoticeController
 * @package App\Http\Controllers\V1
 * 通知
 */
class NoticeController extends Controller
{
    /**
     * @param Request $request
     * 通知——获取通知
     */
    public function fetchNoticeLists(Request $request)
    {
        $indent = $request->user()->indent;

        //查询广告通知
        $noticeArr = NoticeFactory::fetchNoticeLists();
        if (empty($noticeArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $noticeLists = NoticeStrategy::getNoticeLists($indent, $noticeArr);

        return RestResponseFactory::ok($noticeLists);
    }
}
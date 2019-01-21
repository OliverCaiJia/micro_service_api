<?php
namespace App\Http\Controllers\V1;

use App\Events\V1\UserInsuranceEvent;
use App\Events\V1\UserSpreadCountEvent;
use App\Events\V1\UserSpreadEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Orm\UserSpread;
use App\Models\Orm\UserSpreadLog;
use App\Models\Orm\UserSpreadType;
use App\Strategies\SpreadStrategy;
use Illuminate\Http\Request;
use App\Models\Factory\UserIdentityFactory;

/**
 * 推广
 * Class UserSpreadController
 * @package App\Http\Controllers\V1
 */
class UserSpreadController extends Controller
{
    /**
     * 推广接口
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insurance(Request $request)
    {
        $params = $request->all();
        $page = $request->input('page', 1);

        if ($page == 1)
        {
            //获取类型id
            $type_id = UserSpreadFactory::getTypeId(UserSpreadFactory::SPREAD_HEINIU_NID);
            //进行时间限制
            $createAt = UserSpreadFactory::getSpreadLogInfo($params['mobile'], $type_id);
            if(!empty($createAt))
            {
                $now = time();
                $createTime = strtotime($createAt) + (24 * 60 * 60);
                if($now < $createTime)
                {
                    return RestResponseFactory::ok();
                }
            }

            // 获取用户真实性别&生日等信息
            $params = SpreadStrategy::getUserInfo($page, $params);
            // 插入用户数据
            UserSpreadFactory::createOrUpdateUserSpread($params);
            // 是否赠险
            if (isset($params['is_insurance']) && $params['is_insurance'] == 1)
            {
                if ($type_id > 0)  // 赠险开启
                {
                    $params['type_id'] = $type_id;
                    $params['id'] = 0;

                    // 推广统计
                    $spread = UserSpreadFactory::getSpread($params['mobile']);
                    $spread['type_id'] = $type_id;
                    event(new UserSpreadCountEvent($spread->toArray()));

                    //判断用户是否有推送成功的
                    if(!UserSpreadFactory::checkIsSpread($params))
                    {
                        // 创建赠险流水
                        $params['log_id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($params);

                        // 触发赠险事件
                        event(new UserInsuranceEvent($params));
                    }
                }
            }
        } elseif ($page == 2) {
            // 获取用户真实性别&生日等信息
            $params = SpreadStrategy::getUserInfo($page, $params);
            // 触发推广事件
            event(new UserSpreadEvent($params));
            // 更新spread状态
            UserSpreadFactory::createOrUpdateUserSpread(['mobile' => $params['mobile'], 'status' => 1]);
        }

        return RestResponseFactory::ok();
    }

    /**
     * 检查当前用户是否被推广过 未被推广过 继续推广
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        $mobile = $request->input('mobile');

        $types = UserSpreadType::where('status', 1)->get()->toArray();
        foreach ($types as $type) {
            $spreadLog = UserSpreadLog::where('mobile', $mobile)->where('type_id', $type['id'])->where('status', 1)->first();
            if ($spreadLog) {
                break;
            }
        }

        return RestResponseFactory::ok(['status' => $spreadLog ? 1 : 0]);
    }

    /**
     * 合作机构
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function partner(Request $request)
    {
        $types = UserSpreadType::where('status', 1)->select(['name', 'logo'])->get()->toArray();
        $types = SpreadStrategy::getPartners($types);
        return RestResponseFactory::ok($types);
    }

    /**
     * 结果页面
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function result(Request $request)
    {
        $mobile = $request->input('mobile');
        $spread = UserSpread::where('mobile', $mobile)->first();
        if (empty($spread)) {
            return RestResponseFactory::ok();
        }

        $result = SpreadStrategy::getInfo($spread);
        $partner = SpreadStrategy::getRePushProduct($mobile);

        $result['partner'] = $partner;
        $result['insurance'] = UserSpreadFactory::getSpreadInsuranceStatus($mobile);

        return RestResponseFactory::ok($result);

    }

    // 第一页 [测试用]
    public function one(Request $request)
    {
        return view('vendor.spread.pageone');
    }

    // 第二页 [测试用]
    public function two(Request $request)
    {
        return view('vendor.spread.pagetwo');
    }

}
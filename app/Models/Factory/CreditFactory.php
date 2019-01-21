<?php

namespace App\Models\Factory;

use App\Constants\CreditConstant;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserCredit;
use App\Models\Orm\UserCreditProductConfig;
use App\Models\Orm\UserCreditProductLog;
use App\Strategies\CreditStrategy;
use App\Models\Orm\UserCreditLog;
use App\Models\Orm\UserCreditType;
use Illuminate\Support\Facades\DB;

class CreditFactory extends AbsModelFactory
{

    /** 我的积分
     * @param int $userId
     * @return mixed
     */
    public static function fetchCredit($userId)
    {
        $userCredit = UserCredit::select('balance')->where(['user_id' => $userId])->first();
        return $userCredit ? $userCredit->balance : CreditConstant::DEFAULT_EMPTY;
    }

    /** 积分榜首
     * @param array $data
     * @return mixed
     */
    public static function fetchCreditMax()
    {
        $max = UserCredit::select('balance')->max('balance');
        return $max ? $max : CreditConstant::DEFAULT_EMPTY;
    }

    //申请产品
    public static function fetchProductApply()
    {
        $applyArr = UserCreditProductConfig::select(['id', 'product_id', 'credits'])
            ->where(['status' => 0])
            ->get()->toArray();
        return $applyArr ? $applyArr : [];
    }

    /**
     * @param $productId
     * 修改产品申请状态
     */
    public static function updateProductApplyStatus($productId)
    {
        $status = UserCreditProductConfig::whereIn('product_id', $productId)
            ->update(['status' => 1]);
        return $status ?: false;
    }

    /**
     * 产品申请id
     */
    public static function fetchProductApplyId()
    {
        $applyArr = UserCreditProductConfig::select(['id', 'product_id', 'credits'])
            ->where(['status' => 0])
            ->pluck('product_id')
            ->toArray();
        return $applyArr ? $applyArr : [];
    }

    /**
     * @param $id
     * 产品是否申请
     */
    public static function getCreditProductLog($userId, $id)
    {
        $logObj = UserCreditProductLog::select(['id'])
            ->where(['user_id' => $userId, 'config_id' => $id])
            ->first();
        return $logObj ? $logObj->id : 0;
    }


    /**
     *添加积分log记录(加积分)
     * @author zhaoqiying
     * @param $data
     * @return mixed
     * 公式
     * credits = income - expend;
     */
    public static function createAddCreditLog($params)
    {
        #生成积分号
        $nid = CreditStrategy::creditNid();
        #获取当前用户的用户id
        $user_id = $params['user_id'];
        #通过code码获取邀请人的id
        $creditlogObj = new UserCreditLog();
        $creditlogObj->nid = $nid;
        $creditlogObj->user_id = $user_id;
        $creditlogObj->type = $params['type'];
        $creditlogObj->income = $params['income'];
        $creditlogObj->expend = 0;
        $creditlogObj->credit = $creditlogObj->income - $creditlogObj->expend;
        $creditlogObj->remark = $params['remark'];
        $creditlogObj->create_at = date('Y-m-d H:i:s', time());
        $creditlogObj->create_ip = Utils::ipAddress();
        return $creditlogObj->save();
    }

    /**
     *添加积分log记录(减少积分)
     * @author zhaoqiying
     * @param $data
     * @return mixed
     * 公式
     * credits = expend - income;
     */
    public static function createReduceCreditLog($params)
    {
        #生成积分号
        $nid = CreditStrategy::creditNid();
        #获取当前用户的用户id
        $user_id = $params['user_id'];
        #通过code码获取邀请人的id
        $creditlogObj = new UserCreditLog();
        $creditlogObj->nid = $nid;
        $creditlogObj->user_id = $user_id;
        $creditlogObj->type = $params['type'];
        $creditlogObj->income = 0;
        $creditlogObj->expend = $params['expend'];
        $creditlogObj->credit = $creditlogObj->expend - $creditlogObj->income;
        $creditlogObj->remark = $params['remark'];
        $creditlogObj->create_at = date('Y-m-d H:i:s', time());
        $creditlogObj->create_ip = Utils::ipAddress();
        return $creditlogObj->save();
    }

    /*
     * 更新用户积分(加积分)
     * @author zhaoqiying
     * @param $data
     * @return mixed
     * 公式
     * credits = income - expend;
     * credits = balance + frost;
     * balance = credits - frost;
    */
    public static function addUserCredit($params)
    {
        $score = isset($params['score']) ? $params['score'] : 0;
        $frost = isset($params['frost']) ? $params['frost'] : 0;
        //查询不在就创建一条数据
        $creditObj = UserCredit::lockForUpdate()
            ->where(['user_id' => $params['user_id'], 'status' => 0])
            ->first();
        if (empty($creditObj)) {
            $creditObj = new UserCredit();
        }

        $creditObj->user_id = $params['user_id'];
        $creditObj->income += intval($score);
        $creditObj->expend = isset($creditObj->expend) ? $creditObj->expend : 0;
        $creditObj->credits = bcsub($creditObj->income, $creditObj->expend);
        $creditObj->frost += $frost;
        $creditObj->balance = bcsub($creditObj->credits, $creditObj->frost);
        $creditObj->update_at = date('Y-m-d H:i:s', time());
        $creditObj->update_user_id = $params['user_id'];
        $creditObj->update_ip = Utils::ipAddress();
        return $creditObj->save();
    }

    /*
	 * 更新用户积分(减积分)
     * @author zhaoqiying
	 * @param $data
	 * @return mixed
     * 公式
     * credits = income - expend;
     * credits = balance + frost;
     * balance = credits - frost;
	*/
    public static function reduceUserCredit($params)
    {
        $score = isset($params['score']) ? $params['score'] : 0;
        $frost = isset($params['frost']) ? $params['frost'] : 0;
        $creditObj = UserCredit::updateOrCreate(['user_id' => $params['user_id']], [
            'user_id' => intval($params['user_id']),
            'status' => 0,
            'update_at' => date('Y-m-d H:i:s', time()),
            'update_user_id' => intval($params['user_id']),
            'update_ip' => Utils::ipAddress(),
        ]);
        $creditObj->user_id = $params['user_id'];
        // 收入积分 支出积分
        $creditObj->income += 0;
        $creditObj->expend += $score;
        // 当前积分
        $creditObj->credits = $creditObj->income - $creditObj->expend;
        // 冻结积分
        $creditObj->frost += $frost;
        // 剩余积分
        $creditObj->balance = $creditObj->credits - $creditObj->frost;
        return $creditObj->save();
    }

    /**
     * @param $user_id
     * @param $type
     * @return array
     * 查询用户积分流水数据
     */
    public static function fetchCreditLogData($userId, $type)
    {
        $creditLog = UserCreditLog::select()
            ->where(['user_id' => $userId, 'type' => $type])
            ->first();

        return $creditLog ? $creditLog->toArray() : [];
    }

    /**
     * @return array
     * 查询expend小于0的数据
     */
    public static function fetchExpends()
    {
        $expend = UserCredit::select()
            ->where('expend', '<', 0)->limit(100)->get()->toArray();

        return $expend ? $expend : [];
    }


    /**
     * @param $typeNid
     * @return int|mixed
     * @status 使用状态, 1 使用中, 0 未使用
     * 根据唯一标识获取对应加几分数
     */
    public static function fetchScoreByTypeNid($typeNid)
    {
        $score = UserCreditType::select(['score'])
            ->where(['type_nid' => $typeNid, 'status' => 1,])
            ->first();

        return $score ? $score->score : 0;
    }

    /**
     * @param $userId
     * @return int|mixed
     * 用户收入总积分
     */
    public static function fetchIncomeByUserId($userId)
    {
        $income = UserCredit::select('income')->where(['user_id' => $userId])->first();
        return $income ? $income->income : 0;
    }

    /**
     * @param $typeNid
     * @return int|mixed
     * @status 使用状态, 1 使用中, 0 未使用
     * 根据唯一标识获取对应id
     */
    public static function fetchIdByTypeNid($typeNid)
    {
        $res = UserCreditType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1,])
            ->first();

        return $res ? $res->id : 0;
    }

    /** 获取积分流水列表
     * @param $userId
     * @param $type_status
     * @return array
     */
    public static function fetchCreditLogs($data = [])
    {
        $start = date('Y:m:d H:i:s', strtotime('-6 month'));
        $end = date('Y:m:d H:i:s', time());
        $pageSize = isset($data['pageSize']) ? $data['pageSize'] : 1;
        $pageNum = isset($data['pageNum']) ? $data['pageNum'] : 10;

        $query = DB::table('sd_user_credit_log as l')->join('sd_user_credit_type as t', 'l.type', '=', 't.type_nid')
            ->where('l.user_id', $data['user_id'])->where('t.status', 1)->where('l.create_at', '>=', $start)
            ->where('l.create_at', '<=', $end)->where('frost', '=', 0)
            ->where('type', '!=', 'sd_rollback_deduct')->where('type', '!=', 'sd_rollback_add');

        if (in_array($data['status'], [0, 1])) {
            $query->where('t.type_status', $data['status']);
        }

        $query->orderBy('l.create_at', 'desc')->orderBy('l.id', 'desc');

        $count = $query->count();

        $countPage = ceil($count / $pageNum);
        $pageSize = $pageSize > $countPage ? $countPage : $pageSize;
        $offset = ($pageSize - 1) * $pageNum;

        $offsetFlag = 0;
        // 非第一页 偏移-1
        if ($pageSize > 1 && $pageSize <= $countPage)
        {
            $offset -= 1;
            $offsetFlag = 1;
        }

        $res = $query->limit($pageNum)->offset($offset)->get(['t.name', 'l.credit', 'l.create_at', 't.type_status', DB::raw('date(l.create_at) date'), DB::raw("date_format(l.create_at, '%Y-%m') title_date"), DB::raw('extract(day from l.create_at) day'), 't.type_nid'])->toArray();
        $result['list'] = $res ? $res : [];
        $result['pageCount'] = $countPage;
        $result['offset'] = $offsetFlag;

        return $result;
    }

    /** 汇总每月积分数据
     * @param $data
     * @param $userId
     * @return array
     */
    public static function getCreditTotal($data, $userId)
    {
        if (!empty($data))
        {
            // 获取当前数据中的月份
            $keys = [];
            foreach ($data as $item)
            {
                $keys[date('Y-m', strtotime($item->date))] = 1;
            }

            // 获取每个月的总积分
            $result = [];
            foreach ($keys as $key => $val)
            {
                $start = $key . '-01 00:00:00';
                $end = date('Y-m-d H:i:s', strtotime("+1 months", strtotime($start)));

                $query = DB::table('sd_user_credit_log as l')->join('sd_user_credit_type as t', 'l.type', '=', 't.type_nid')
                    ->where('l.user_id', $userId)->where('t.status', 1)->where('l.create_at', '>=',$start)->where('l.create_at', '<=', $end)
                    ->where('type', '!=', 'sd_rollback_deduct')->where('type', '!=', 'sd_rollback_add');
                $income_query = clone $query;
                $expend_query = clone $query;

                $income = $income_query->where('t.type_status', 1)->where('frost', 0)->sum('l.credit');
                $expend = $expend_query->where('t.type_status', 0)->where('frost', 0)->sum('l.credit');

                $result[] = [
                    'month' => $key,
                    'income' => $income,
                    'expend' => $expend
                ];
            }

            return $result;
        }

        return [];
    }
}

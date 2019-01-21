<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\Bank;
use App\Models\Orm\BankCity;
use App\Models\Orm\BankUsage;

/**
 * Class BanksFactory
 * @package App\Models\Factory
 * 拥有信用卡的银行工厂
 */
class BanksFactory extends AbsModelFactory
{
    /**
     * @param $areaId
     * @return array
     * @is_delete 是否删除 1删除,0 未删除
     * 城市id不为0时：根据城市id筛选出符合的银行id
     */
    public static function fetchDeviceBankIdsByDeviceId($areaId)
    {
        $deviceBankIds = BankCity::select(['bank_id'])
            ->where(['area_id' => $areaId, 'is_delete' => 0])
            ->pluck('bank_id')->toArray();

        return $deviceBankIds ? $deviceBankIds : [];
    }

    /**
     * @return array
     * 所有银行id
     */
    public static function fetchBankIds()
    {
        $bankIds = Bank::select(['id'])->pluck('id')->toArray();

        return $bankIds ? $bankIds : [];
    }

    /**
     * @return array
     * @is_delete 是否删除 1删除,0 未删除
     * 有定位的所有银行id
     */
    public static function fetchCityBankIds()
    {
        $cityBankIds = BankCity::select(['bank_id'])
            ->where(['is_delete' => 0])
            ->pluck('bank_id')->toArray();

        return $cityBankIds ? $cityBankIds : [];
    }

    /**
     * @param $data
     * @return array
     * @online_status 上下线状态,0 下线, 1 上线
     */
    public static function fetchHotsByDeviceIds($data)
    {
        //所有银行id
        $bankIds = $data['bankIds'];
        //城市id不为0时：根据城市id筛选出符合的银行id
        $deviceBankIds = $data['deviceBankIds'];
        //有定位的所有银行id
        $cityBankIds = $data['cityBankIds'];
        //查询银行内容
        $query = Bank::select(['id', 'bank_short_name', 'bank_logo'])
            ->where(['online_status' => 1]);
        //定位
        $diff = array_diff($bankIds, $cityBankIds);
        $deviceBankIdDatas = array_merge($diff, $deviceBankIds);
        $query->when($data['deviceId'], function ($query) use ($deviceBankIdDatas) {
            $query->whereIn('id', $deviceBankIdDatas);
        });
        //排序
        $query->orderBy('position_sort', 'asc');
        $query->orderBy('id', 'desc');

        $banks = $query->get()->toArray();
        return $banks ? $banks : [];

    }

    /**
     * @param $data
     * @return array
     * @is_process 是否可查进度, 0否,1是
     * @online_status   上下线状态,0 下线, 1 上线
     */
    public static function fetchProgressBanks($data)
    {
        //查询银行内容
        $query = Bank::select(['id', 'bank_short_name', 'bank_logo', 'process_link'])
            ->where(['online_status' => 1, 'is_process' => 1]);
        //排序
        $query->orderBy('position_sort', 'asc');
        $query->orderBy('id', 'desc');

        $banks = $query->get()->toArray();
        return $banks ? $banks : [];
    }

    /**
     * @param $data
     * @return array
     * @online_status   上下线状态,0 下线, 1 上线
     */
    public static function fetchHasCreditcardBanks($data)
    {
        //所有银行id
        $bankIds = $data['bankIds'];
        //城市id不为0时：根据城市id筛选出符合的银行id
        $deviceBankIds = $data['deviceBankIds'];
        //有定位的所有银行id
        $cityBankIds = $data['cityBankIds'];
        //查询银行内容
        $query = Bank::select(['id', 'bank_short_name'])
            ->where(['online_status' => 1]);
        //定位
        $diff = array_diff($bankIds, $cityBankIds);
        $deviceBankIdDatas = array_merge($diff, $deviceBankIds);
        $query->when($data['deviceId'], function ($query) use ($deviceBankIdDatas) {
            $query->whereIn('id', $deviceBankIdDatas);
        });
        //是否支持提醒
//        $isRemind = isset($data['is_remind']) ? $data['is_remind'] : 0;
//        $query->when($isRemind, function ($query) use ($isRemind) {
//            $query->where('is_remind', $isRemind);
//        });
        //排序
        $query->orderBy('position_sort', 'asc');
        $query->orderBy('id', 'desc');

        $banks = $query->get()->toArray();
        return $banks ? $banks : [];
    }

    /**
     * @param $bankId
     * @return array
     * @online_status 上下线状态,0 下线, 1 上线
     * 根据银行id获取单个银行信息
     */
    public static function fetchBanksById($bankId)
    {
        //查询银行内容
        $query = Bank::select()->where(['online_status' => 1])
            ->where(['id' => $bankId])->first();

        return $query ? $query->toArray() : [];
    }

    /**
     * @return array
     * @is_active 是否激活, 0否, 1是
     * @online_status 上下线状态,0 下线, 1 上线
     * 立即激活银行
     */
    public static function fetchActives()
    {
        $actives = Bank::select(['id', 'bank_short_name', 'bank_logo', 'active_link',])
            ->where(['online_status' => 1])
            ->where(['is_active' => 1])
            //排序
            ->orderBy('position_sort', 'asc')
            ->orderBy('id', 'desc')
            ->get()->toArray();

        return $actives ? $actives : [];
    }

    /**
     * @return array
     * @online_status 上下线状态,0 下线, 1 上线
     * @is_quota 是否提额, 0否,1是
     * 立即提额
     */
    public static function fetchQuotas($data)
    {
        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;
        $query = Bank::select(['id', 'bank_short_name', 'bank_logo', 'quota_link'])
            ->where(['online_status' => 1])
            ->where(['is_quota' => 1])
            //排序
            ->orderBy('quota_link', 'desc')
            ->orderBy('position_sort', 'asc')
            ->orderBy('id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $searches = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $datas['list'] = $searches;
        $datas['pageCount'] = $countPage ? $countPage : 0;

        return $datas ? $datas : [];
    }

    /**
     * @return array
     * @status 显示状态,0 不显示, 1 显示
     * 获取提醒银行表
     */
    public static function fetchBankUsages()
    {
        $bankUsage = BankUsage::select(['id', 'bank_short_name'])
            ->where(['status' => 1])
            ->orderBy('position_sort')
            ->get()->toArray();
        return $bankUsage ? $bankUsage : [];
    }

    /**
     * @param $bankId
     * @return array
     * @status 显示状态,0 不显示, 1 显示
     * 查询提醒银行内容
     */
    public static function fetchBankUsageById($bankId)
    {
        //查询提醒银行内容
        $query = BankUsage::select()->where(['status' => 1])
            ->where(['id' => $bankId])->first();

        return $query ? $query->toArray() : [];
    }
}
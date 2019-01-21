<?php

namespace App\Models\Factory;

use App\Constants\CreditcardConstant;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\Bank;
use App\Models\Orm\BankCreditcardSearchLog;
use App\Models\Orm\BankCreditcard;

/**
 * Class CreditcardFactory
 * @package App\Models\Factory
 * 信用卡工厂
 */
class CreditcardFactory extends AbsModelFactory
{
    /**
     * @param $data
     * @return array
     * @online_status  上线状态, 0下线,1上线
     */
    public static function fetchHots($data)
    {
        //所有银行id
        $bankIds = $data['bankIds'];
        //城市id不为0时：根据城市id筛选出符合的银行id
        $deviceBankIds = $data['deviceBankIds'];
        //有定位的所有银行id
        $cityBankIds = $data['cityBankIds'];
        //查询银行内容
        $query = Bank::from('sd_bank as b')
            ->join('sd_bank_creditcard as c', 'b.id', '=', 'c.bank_id')
            ->select(['c.card_name'])
            ->where(['b.online_status' => 1, 'c.online_status' => 1]);
        //定位
        $diff = array_diff($bankIds, $cityBankIds);
        $deviceBankIdDatas = array_merge($diff, $deviceBankIds);
        $query->when($data['deviceId'], function ($query) use ($deviceBankIdDatas) {
            $query->whereIn('b.id', $deviceBankIdDatas);
        });
        //排序
        $query->limit(6);
        $query->orderBy('c.position_sort', 'asc');
        $query->orderBy('c.id', 'desc');

        $datas = $query->get()->toArray();
        return $datas ? $datas : [];
    }

    /**
     * @param $data
     * @return array
     * 信用卡搜索
     * 排序规则：“申请量”由高到低排序，申请量相同按照通过率由高到低排序
     */
    public static function fetchSearches($data)
    {
        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;
        //所有银行id
        $bankIds = $data['bankIds'];
        //城市id不为0时：根据城市id筛选出符合的银行id
        $deviceBankIds = $data['deviceBankIds'];
        //有定位的所有银行id
        $cityBankIds = $data['cityBankIds'];
        //查询银行内容
        $query = Bank::from('sd_bank as b')
            ->join('sd_bank_creditcard as c', 'c.bank_id', '=', 'b.id')
            ->select(['c.id', 'c.card_name'])
            ->where(['b.online_status' => 1, 'c.online_status' => 1]);
        //查询字段
        $query->addSelect(['c.card_name', 'c.card_logo', 'c.card_h5_link', 'c.activity_content', 'c.total_apply_count']);
        //定位
        //dd($query->get()->toArray());
        $diff = array_diff($bankIds, $cityBankIds);
        $deviceBankIdDatas = array_merge($diff, $deviceBankIds);
        $query->when($data['deviceId'], function ($query) use ($deviceBankIdDatas) {
            $query->whereIn('b.id', $deviceBankIdDatas);
        });
        //搜索名称
        $searchName = $data['searchName'];
        $query->when($searchName, function ($query) use ($searchName) {
            $query->where(function ($query) use ($searchName) {
                $query->where('b.bank_name', 'like', '%' . $searchName . '%')->orWhere('c.card_name', 'like', '%' . $searchName . '%');
            });
        });
        //排序
        $query->orderBy('total_apply_count', 'desc')->orderBy('total_pass_rate', 'desc');
        //分页
        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */
        $datas = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $searches['list'] = $datas;
        $searches['pageCount'] = $countPage ? $countPage : 0;

        return $searches ? $searches : [];
    }

    /**
     * @param $data
     * @return bool
     * 搜索流水记录
     */
    public static function createSearchLog($data)
    {
        $log = new BankCreditcardSearchLog();
        $log->user_id = $data['userId'];
        $log->mobile = UserFactory::fetchMobile($data['userId']);
        $log->search_name = Utils::removeSpace($data['searchName']);
        $log->is_delete = 0;
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();
        return $log->save();
    }

    /**
     * @param $data
     * @return array
     * 信用卡筛选
     */
    public static function fetchCreditCardSearches($data)
    {
        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;
        //所有银行id
        $bankIds = $data['bankIds'];
        //城市id不为0时：根据城市id筛选出符合的银行id
        $deviceBankIds = $data['deviceBankIds'];
        //有定位的所有银行id
        $cityBankIds = $data['cityBankIds'];

        //信用卡
        $query = Bank::from('sd_bank as b')
            ->join('sd_bank_creditcard as c', 'b.id', '=', 'c.bank_id')
            ->select(['c.id', 'c.card_name'])
            ->where(['b.online_status' => 1, 'c.online_status' => 1]);
        //查询字段
        $query->addSelect(['c.card_name', 'c.card_logo', 'c.card_h5_link', 'c.activity_content', 'c.total_apply_count']);
        //排序
        $query->orderBy('total_apply_count', 'desc')->orderBy('total_pass_rate', 'desc')->orderBy('id', 'desc');

        //银行列表定位
        $diff = array_diff($bankIds, $cityBankIds);
        $deviceBankIdDatas = array_merge($diff, $deviceBankIds);
        $query->when($data['deviceId'], function ($query) use ($deviceBankIdDatas) {
            $query->whereIn('b.id', $deviceBankIdDatas);
        });

        //银行筛选
        $bankIds = $data['bankId'];
        $query->when($bankIds, function ($query) use ($bankIds) {
            $query->where('b.id', $bankIds);
        });

        //用途筛选
        $usageCreditcardId = $data['usageCreditcardId'];
        $usageTypeNid = $data['usageTypeNid'];
        $query->when($usageTypeNid, function ($query) use ($usageCreditcardId) {
            $query->whereIn('c.id', $usageCreditcardId);
        });

        //等级筛选
        $degreeCreditcardId = $data['degreeCreditcardId'];
        $degree = $data['degree'];
        $query->when($degree, function ($query) use ($degreeCreditcardId) {
            $query->whereIn('c.id', $degreeCreditcardId);
        });

        //费率筛选
        $feeId = $data['feeId'];
        $feeTypeNid = $data['feeTypeNid'];
        $query->when($feeTypeNid, function ($query) use ($feeId) {
            $query->where('c.annual_fee_type', $feeId);
        });

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
        $datas['pageCount'] = $countPage ? $countPage : 1;

        return $datas ? $datas : [];
    }

    /**
     * @param $data
     * @return array
     * 特色推荐信用卡列表
     */
    public static function fetchSpecials($data)
    {
        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        //默认3个
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 3;
        $type = isset($data['specialType']) ? $data['specialType'] : CreditcardConstant::SPECIAL_RECOMMEND;
        //所有银行id
        $bankIds = $data['bankIds'];
        //城市id不为0时：根据城市id筛选出符合的银行id
        $deviceBankIds = $data['deviceBankIds'];
        //有定位的所有银行id
        $cityBankIds = $data['cityBankIds'];

        //信用卡
        $query = Bank::from('sd_bank as b')
            ->join('sd_bank_creditcard as c', 'b.id', '=', 'c.bank_id')
            ->select(['c.id', 'c.card_name'])
            ->where(['b.online_status' => 1, 'c.online_status' => 1]);
        //查询字段
        $query->addSelect(['c.card_logo', 'c.card_h5_link', 'c.activity_content', 'c.total_apply_count']);

        //银行列表定位
        $diff = array_diff($bankIds, $cityBankIds);
        $deviceBankIdDatas = array_merge($diff, $deviceBankIds);
        $query->when($data['deviceId'], function ($query) use ($deviceBankIdDatas) {
            $query->whereIn('b.id', $deviceBankIdDatas);
        });

        //热门推荐
        if ($type == CreditcardConstant::SPECIAL_RECOMMEND) {
            //申请量  从大到小
            $query->orderBy('c.total_apply_count', 'desc')->orderBy('c.id', 'desc');
        } elseif ($type == CreditcardConstant::SPECIAL_AMOUNT) {
            //大额度 最大额度 从高到低
            $query->orderBy('c.loan_max', 'desc')->orderBy('c.id', 'desc');
        } elseif ($type == CreditcardConstant::SPECIAL_FAST_BATCH_CARD) {
            //快速批卡 批卡最快时间 从小到大
            $query->orderBy('c.approve_fast')->orderBy('c.id', 'desc');
        } elseif ($type == CreditcardConstant::SPECIAL_NEW_HAND) {
            //新手办卡 批卡率 从高到低
            $query->orderBy('c.approve_rate', 'desc')->orderBy('c.id', 'desc');
        }

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
     * 首页 推荐 信用卡
     * @param $data
     * @return array
     */
    public static function fetchHomeSpecials($data)
    {
        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        //默认3个
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 3;
        $type = isset($data['specialType']) ? $data['specialType'] : CreditcardConstant::SPECIAL_RECOMMEND;
        //所有银行id
        $bankIds = $data['bankIds'];
        //城市id不为0时：根据城市id筛选出符合的银行id
        $deviceBankIds = $data['deviceBankIds'];
        //有定位的所有银行id
        $cityBankIds = $data['cityBankIds'];

        //信用卡
        $query = Bank::from('sd_bank as b')
            ->join('sd_bank_creditcard as c', 'b.id', '=', 'c.bank_id')
            ->select(['c.id', 'c.card_name'])
            ->where(['b.online_status' => 1, 'c.online_status' => 1]);
        //查询字段
        $query->addSelect(['c.card_logo', 'c.card_h5_link', 'c.activity_content', 'c.total_apply_count']);

        //银行列表定位
        $diff = array_diff($bankIds, $cityBankIds);
        $deviceBankIdDatas = array_merge($diff, $deviceBankIds);
        $query->when($deviceBankIdDatas, function ($query) use ($deviceBankIdDatas) {
            $query->whereIn('b.id', $deviceBankIdDatas);
        });

        //热门推荐
        if ($type == CreditcardConstant::SPECIAL_RECOMMEND) {
            //申请量  从大到小
            $query->orderBy('c.total_apply_count', 'desc')->orderBy('c.id', 'desc');
        } elseif ($type == CreditcardConstant::SPECIAL_AMOUNT) {
            //大额度 最大额度 从高到低
            $query->orderBy('c.loan_max', 'desc')->orderBy('c.id', 'desc');
        } elseif ($type == CreditcardConstant::SPECIAL_FAST_BATCH_CARD) {
            //快速批卡 批卡最快时间 从小到大
            $query->orderBy('c.approve_fast')->orderBy('c.id', 'desc');
        } elseif ($type == CreditcardConstant::SPECIAL_NEW_HAND) {
            //新手办卡 批卡率 从高到低
            $query->orderBy('c.approve_rate', 'desc')->orderBy('c.id', 'desc');
        }

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
     * @is_special 办卡有礼开关, 1是, 0否
     * @online_status 上线状态, 0下线,1上线
     * 办卡有礼推广信用卡
     */
    public static function fetchSpecialGifts($data)
    {
        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;
        //信用卡
        $query = Bank::from('sd_bank as b')
            ->join('sd_bank_creditcard as c', 'b.id', '=', 'c.bank_id')
            ->select(['c.id', 'c.card_name'])
            ->where(['b.online_status' => 1, 'c.online_status' => 1])
            ->where(['is_special' => 1]);
        //查询字段
        $query->addSelect(['c.card_img', 'c.card_h5_link', 'c.activity_content']);
        //排序
        $query->orderBy('c.special_sort', 'desc')->orderBy('id', 'desc');

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

    /** 根据id获取信用卡信息
     * @param $cardId
     * @return mixed
     */
    public static function fetchCreditCard($cardId)
    {
        $card = BankCreditcard::where('id', $cardId)->first();
        return $card ? $card->toArray() : [];
    }
}
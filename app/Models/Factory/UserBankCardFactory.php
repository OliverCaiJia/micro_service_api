<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\Banks;
use App\Models\Orm\UserBanks;
use App\Services\Core\Store\Qiniu\QiniuService;

/**
 * Class UserBankCardFactory
 * @package App\Models\Factory
 * 绑定银行卡
 */
class UserBankCardFactory extends AbsModelFactory
{
    /**
     * 绑定银行列表
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param $data
     * @return array
     */
    public static function fetchUserbanks($data)
    {
        $pageSize = $data['pageSize'];
        $pageNum = $data['pageNum'];

        $query = UserBanks::select(['id', 'user_id', 'bank_id', 'account', 'card_default'])
            ->where([
                'user_id' => $data['userId'],
                'card_type' => $data['cardType'],
                'card_use' => 1,
                'status' => 0,
            ]);

        //储蓄卡按默认在前
        if ($data['cardType'] == 1) {
            $query->orderBy('card_default', 'desc');
        }
        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $userbanks = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $params['list'] = $userbanks;
        $params['pageCount'] = $countPage ? $countPage : 0;

        return $params ? $params : [];
    }

    /**
     * 获取银行信息，用户信息
     * @param array $params
     * @return array
     */
    public static function fetchUserbanksinfo($params = [])
    {
        $data = [];
        foreach ($params as $key => $val) {
            $bankinfo = BankFactory::fetchBankinfoById($val['bank_id']);
            $data[$key]['user_bank_id'] = $val['id'];
            $data[$key]['bankname'] = empty($bankinfo['sname']) ? $bankinfo['name'] : $bankinfo['sname'];
            $data[$key]['banklogo'] = QiniuService::getImgs($bankinfo['litpic']);
            $data[$key]['account'] = $val['account'];
            $realname = UserIdentityFactory::fetchRealnameById($val['user_id']);
            $data[$key]['realname'] = $realname;
            $data[$key]['card_default'] = $val['card_default'];
        }

        return $data ? $data : [];
    }

    /**
     * 获取卡片信息
     * @param $id
     * @param $userid
     * @return array
     */
    public static function getBankCardInfo($id, $userid)
    {
        $res = UserBanks::select()
            ->where(['id' => $id, 'user_id' => $userid, 'card_use' => 1])
            ->where('status', '!=', 9)
            ->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 添加银行卡，天创4要素验证
     * @param $params
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @return bool
     */
    public static function createOrUpdateUserBank($params)
    {
        $userbanks = UserBanks::select(['id'])->where(['status' => 0,
            'user_id' => $params['userId'], 'card_use' => 1, 'account' => $params['account']])
            ->first();
        if (!$userbanks) {
            //入库
            $userbanks = new UserBanks();
            $userbanks->created_ip = Utils::ipAddress();
            $userbanks->created_at = date('Y-m-d H:i:s');
        }
        $userbanks->user_id = $params['userId'];
        $userbanks->bank_id = $params['bankId'];
        $userbanks->account = $params['account'];
        $userbanks->bank_name = $params['bankname'];
        $userbanks->card_type = $params['cardtype'];
        $userbanks->card_default = $params['card_default'];
        $userbanks->card_use = 1;
        $userbanks->card_last_status = $params['card_last_status'];
        $userbanks->card_mobile = $params['mobile'];
        $userbanks->updated_ip = Utils::ipAddress();
        $userbanks->updated_at = date('Y-m-d H:i:s');
        $userbanks->save();

        $data['id'] = $userbanks->id;
        return $data;
    }


    /**
     * 通过银行简称获取银行信息
     * @param $bankcode
     * @return array
     */
    public static function getBankInfoByBankcode($bankcode)
    {
        $bankinfo = Banks::select('id', 'litpic', 'sname', 'name')->where(['nid' => $bankcode, 'status' => 0,])->first();
        return $bankinfo ? $bankinfo->toArray() : [];
    }


    /**
     * 获取默认银行卡信息
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param $userid
     * @return mixed|string
     */
    public static function getDefaultBankCardIdById($userId)
    {
        $res = UserBanks::select('id')
            ->where(['user_id' => $userId, 'status' => 0, 'card_type' => 1, 'card_use' => 1, 'card_default' => 1])->pluck('id');
        return $res ? $res->toArray() : [];
    }

    /**
     * 储蓄卡是否存在
     * @param $userid
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @return array
     */
    public static function fetchCarddefaultById($userid)
    {
        $res = UserBanks::select('id')
            ->where(['user_id' => $userid, 'status' => 0, 'card_type' => 1, 'card_use' => 1])
            ->first();
        return $res ? $res->toArray() : [];
    }

    /**
     * 查询上次支付银行卡是否存在
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @card_last_status  最后使用支付的银行卡状态 【0未使用，1已使用】
     * @param $userId
     * @return array
     */
    public static function fetchCardLastPayById($userId)
    {
        $res = UserBanks::select()
            ->where(['user_id' => $userId, 'status' => 0, 'card_use' => 1, 'card_last_status' => 1])
            ->first();
        return $res ? $res->toArray() : [];
    }

    /**
     * 设置一张支付卡
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @card_last_status  最后使用支付的银行卡状态 【0未使用，1已使用】
     * @param $userId
     * @return bool
     */
    public static function updateCardLastPayStatus($userId)
    {
        $query = UserBanks::select(['id'])
            ->where(['card_use' => 1, 'user_id' => $userId, 'status' => 0])
            ->whereIn('card_type', [1, 2]);
        //排序 储蓄卡在上，默认储蓄卡在前，后添加卡在前
        $query->orderBy('card_type', 'asc')
            ->orderBy('card_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        $status = $query->limit(1)->update(['card_last_status' => 1]);

        return $status;
    }

    /**
     * 根据时间倒叙排列储蓄卡 选出最近一张储蓄卡id
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param $userId
     * @return array
     */
    public static function fetchCarddefaultIdByTime($userId)
    {
        $default = UserBanks::select(['id'])
            ->where(['user_id' => $userId, 'status' => 0, 'card_type' => 1, 'card_use' => 1])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();

        return $default ? $default->toArray() : [];
    }

    /**
     * 删卡
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @card_last_status  最后使用支付的银行卡状态 【0未使用，1已使用】
     * @param array $params
     * @return bool
     */
    public static function deleteUserBankById($params = [])
    {
        $res = UserBanks::where([
            'id' => $params['userbankId'],
            'user_id' => $params['userId'],
            'card_type' => $params['cardType'],
            'card_use' => 1,
        ])
            ->update(['status' => 9, 'card_default' => 0, 'card_last_status' => 0]);

        return $res;
    }


    /**
     * 设置默认卡储蓄卡
     * @param array $params
     * @return bool
     */
    public static function setDefaultById($params = [])
    {
        return UserBanks::where([
            'id' => $params['userbankId'],
            'card_type' => 1,
            'card_use' => 1,
            'status' => 0,
            'user_id' => $params['userId'],
        ])
            ->update(['card_default' => 1]);
    }


    /**
     * 取消默认卡储蓄卡
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param array $data
     * @return bool
     */
    public static function deleteDefaultById($data = [])
    {
        return UserBanks::where([
            'user_id' => $data['userId'],
            'card_type' => 1,
            'card_use' => 1,
            'status' => 0,
        ])->whereIn('id', $data['ids'])
            ->update(['card_default' => 0]);
    }


    /**
     * 获取最新添加的非默认储蓄卡
     * @param $userid
     * @return array
     */
    public static function getLastestNotDefaultCard($userid)
    {
        $where = [
            'card_type' => 1,
            'card_use' => 1,
            'card_default' => 0,
            'user_id' => $userid,
        ];
        $res = UserBanks::select('id')->where($where)
            ->orderByDesc('created_at')->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 获取最后使用的银行卡信息
     *
     * @param $userId
     * @return mixed
     */
    public static function getDefaultBankCard($userId)
    {
        $res = UserBanks::where([
            'card_use' => 1,
            'user_id' => $userId,
            'card_last_status' => 1,
            'status' => 0,
        ])->first(['id', 'bank_id', 'account', 'bank_name', 'sbank_name', 'card_type', 'card_default', 'card_use', 'card_last_status', 'card_mobile', 'status']);

        return $res ? $res : [];
    }

    /**
     * 根据用户id与绑定银行卡id获取绑定银行信息
     * @param array $params
     * @return array
     */
    public static function fetchUserBankInfoById($params = [])
    {
        $res = UserBanks::select()
            ->where(['user_id' => $params['userId'], 'id' => $params['id']])
            ->where(['status' => 0, 'card_use' => 1])
            ->first();
        return $res ? $res->toArray() : [];
    }

    /**
     * 获取使用卡片的列表
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param array $data
     * @return array
     */
    public static function getUsedCardList($data)
    {
        $userid = $data['userId'];
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);

        $query = UserBanks::select(['id', 'bank_id', 'account', 'bank_name', 'sbank_name', 'card_type', 'card_default', 'card_use', 'card_last_status', 'card_mobile', 'status'])
            ->where(['card_use' => 1, 'user_id' => $userid, 'status' => 0])
            ->whereIn('card_type', [1, 2]);
        //排序 储蓄卡在上，默认储蓄卡在前，后添加卡在前
        $query->orderBy('card_type', 'asc')
            ->orderBy('card_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;

        $arr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $banks['list'] = $arr;
        $banks['pageCount'] = $countPage ? $countPage : 0;

        return $banks;
    }

    /**
     * 获取logo链接
     *
     * @param int $bankId 银行ID
     * @return mixed
     */
    public static function getBankLogo($bankId)
    {
        return Banks::where(['id' => $bankId])->value('litpic');
    }

    /**
     * 设置最近一次支付使用的银行卡
     * @param $bankcard_id
     * @param $userid
     */
    public static function setLastestUsedCard($bankcard_id, $userid)
    {

        UserBanks::where(['user_id' => $userid,])->where('id', '!=', $bankcard_id)
            ->update(['card_last_status' => 0]);

        UserBanks::where(['user_id' => $userid,])->where('id', '=', $bankcard_id)
            ->update(['card_last_status' => 1]);
    }

    /**
     * 用户储蓄卡总张数
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param $data
     * @return int
     */
    public static function fetchSavingCountById($data)
    {
        $count = UserBanks::select(['id'])
            ->where([
                'user_id' => $data['userId'],
                'card_type' => $data['cardType'],
                'card_use' => 1,
                'status' => 0,
            ])->count();

        return $count ? $count : 0;
    }

    /**
     * 用户绑定卡总张数
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param $data
     * @return int
     */
    public static function fetchUserBanksCount($data)
    {
        $count = UserBanks::select(['id'])
            ->where([
                'user_id' => $data['userId'],
                'card_use' => 1,
                'status' => 0,
            ])->count();

        return $count ? $count : 0;
    }

    /**
     * 查询银行卡支付状态
     * @card_use  使用状态【0信用资料，1认证银行】
     * @card_last_status 最后使用支付的银行卡状态 【0未使用，1已使用】
     * @return array|\Illuminate\Support\Collection
     */
    public static function fetchLastBankIdsByStatus($userId)
    {
        $ids = UserBanks::select(['id'])
            ->where(['user_id' => $userId])
            ->where(['card_last_status' => 1, 'card_use' => 1])
            ->pluck('id');

        return $ids ? $ids->toArray() : [];
    }

    /**
     * 查询以前是否有银行卡支付
     * @param array $params
     * @return array
     */
    public static function fetchLastPaymentById($params = [])
    {
        $ids = UserBanks::select(['id'])
            ->where(['card_last_status' => 1])
            ->where(['user_id' => $params['userId'], 'card_use' => 1])
            ->pluck('id');

        return $ids ? $ids->toArray() : [];
    }

    /**
     * 取消上次支付状态
     * @param array $params
     * @return bool
     */
    public static function deleteCardLastStatusByIds($params = [])
    {
        return UserBanks::where(['card_use' => 1])
            ->where(['user_id' => $params['userId']])
            ->whereIn('id', $params['ids'])
            ->update(['card_last_status' => 0]);
    }

    /**
     * 修改支付卡片状态
     * @param $id
     * @return bool
     */
    public static function updateCardLastStatusById($params = [])
    {
        return UserBanks::where(['id' => $params['userbankId'], 'user_id' => $params
        ['userId'], 'status' => 0, 'card_use' => 1])
            ->update(['card_last_status' => 1]);
    }

    /**
     * 判断是否已添加过该银行卡
     * @card_type 银行卡类型 【1:储蓄卡,2:信用卡】
     * @card_default 默认状态【0未默认，1已默认】
     * @card_use  使用状态【0信用资料，1认证银行】
     * @param array $params
     * @return array
     */
    public static function fetchUserBankByAccount($params = [])
    {
        $userbank = UserBanks::select(['id'])
            ->where([
                'user_id' => $params['userId'],
                'account' => $params['account'],
                'card_use' => 1,
                'status' => 0,
            ])->first();

        return $userbank ? $userbank->toArray() : [];
    }

}
<?php

namespace App\Models\Factory;


use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\PlatformProductVip;
use App\Models\Orm\SystemConfig;
use App\Models\Orm\User;
use App\Models\Orm\UserInfo;
use App\Models\Orm\UserVip;
use App\Models\Orm\UserVipPrivilege;
use App\Models\Orm\UserVipPrivilegeRelation;
use App\Models\Orm\UserVipType;
use Illuminate\Support\Facades\DB;

class UserVipFactory extends AbsModelFactory
{
    /**
     * 获取vip特权信息
     *
     * @param $privilegeId
     * @return array
     */
    public static function getVipPrivilegeInfo($privilegeId)
    {
        $res = UserVipPrivilege::where(['id' => $privilegeId, 'status' => 1, 'is_desc' => 1])->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 获取特权id集合
     *
     * @param $vipTypeId
     * @return array
     */
    public static function getVipPrivilegeIds($vipTypeId)
    {
        $pids = UserVipPrivilegeRelation::where(['type_id' => $vipTypeId, 'status' => 1])->pluck('privilege_id')->toArray();

        return $pids ? $pids : [];
    }

    /**
     * 更新插入vip信息
     *
     * @param array $data
     * @return mixed
     */
    public static function createVipInfo($data = [])
    {
        //SLogger::getStream()->info('vip续费', ['data' => $data]);
        $now = date('Y-m-d H:i:s', time());
        $message = UserVip::select()->where(['user_id' => $data['user_id'], 'vip_type' => $data['vip_type'],])->first();
        if (empty($message)) {
            $message = new UserVip();
            $message->status = 4;
            $message->created_ip = Utils::ipAddress();
            $message->created_at = date('Y-m-d H:i:s');
        } else {
            //判断是否是会员
            if ($message['status'] == 1 && $message['end_time'] < $now) {
                $message->end_time = '1970-01-01 00:00:00';
                $message->status = 3;
            }
//            else {
//                $message->end_time = date('Y-m-d H:i:s', time());
//            }
        }

        $message->user_id = $data['user_id'];
        $message->vip_no = $data['vip_no'];//会员编号
        $message->vip_type = $data['vip_type'];
        $message->start_time = date('Y-m-d H:i:s');
        $message->updated_ip = Utils::ipAddress();
        $message->updated_at = date('Y-m-d H:i:s');

        return $message->save();
    }

    /**
     * 获取会员信息
     *
     * @param $userId
     * @return array
     */
    public static function getUserVip($userId)
    {
        $data = UserVip::where(['user_id' => $userId, 'status' => 1])->first();

        return $data ? $data : [];
    }

    /**
     * 获取会员价格
     *
     * @return int|mixed
     */
    public static function getVipAmount()
    {
        $amount = UserVipType::where(['type_nid' => UserVipConstant::VIP_TYPE_NID, 'status' => 1])->value('vip_consume');;

        return $amount ? $amount : 0;
    }

    /**
     * 根据vip类型,获取价格
     *
     * @param $typeNid
     * @return int
     */
    public static function getReVipAmount($typeNid)
    {
        $amount = UserVipType::where(['type_nid' => $typeNid, 'status' => 1])->value('vip_consume');;

        return $amount ? $amount : 0;
    }

    /**
     * 获取会员类型ID
     *
     * @return mixed|string
     */
    public static function getVipTypeId()
    {
        $id = UserVipType::where(['type_nid' => UserVipConstant::VIP_TYPE_NID, 'status' => 1])->value('id');

        return $id ? $id : "";
    }

    /**
     * 根据不同的vip类型获取id
     *
     * @param $typeNid
     * @return string
     */
    public static function getReVipTypeId($typeNid)
    {
        $id = UserVipType::where(['type_nid' => $typeNid, 'status' => 1])->value('id');

        return $id ? $id : "";
    }

    /**
     * 获取普通用户类型ID
     *
     * @return int
     */
    public static function getCommonTypeId()
    {
        $id = UserVipType::where(['type_nid' => UserVipConstant::VIP_TYPE_NID_VIP_COMMON, 'status' => 1])->value('id');

        return $id ? $id : 1;
    }

    /**
     * 获取统计数值
     *
     * @param $typeId
     * @return int
     */
    public static function getStatistics($typeId)
    {
        $data = PlatformProductVip::select(DB::raw('count(*) as user_count, vip_type_id, product_id'))
            ->where(['vip_type_id' => $typeId])->first()->toArray();

        return $data;
    }


    /**
     * 获取特权信息
     *
     * @param $id
     * @return array
     */
    public static function getPrivilege($id)
    {
        //'type_nid' => $nid,
        $data = UserVipPrivilege::where(['id' => $id, 'status' => 1])->first();

        return $data ? $data : [];
    }

    /**
     * 获取特权的ID
     *
     * @param $typeId
     * @return array
     */
    public static function getPrivilegeId($typeId)
    {
        $ids = UserVipPrivilegeRelation::select('privilege_id')->where(['type_id' => $typeId, 'status' => 1])->get()->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 获取user_vip个数
     *
     * @return mixed
     */
    public static function getUserVipCount()
    {
        return UserVip::count();
    }

    /**
     * 获取user_vip限制的数据
     *
     * @param int $limit
     * @return mixed
     */
    public static function getUserVipLimit($limit = 10)
    {
        //->orderBy('id', $desc)
        $data = UserVip::select('user_id')->orderByRaw('RAND()')->limit($limit)->get()->toArray();

        return $data;
    }

    /**
     * 随机获取用户
     *
     * @param int $limit
     * @return mixed
     */
    public static function getUserLimit($limit = 10)
    {
        $distance = 1000;
        $uid = User::orderBy('sd_user_id', 'desc')->first();
        $from = $uid['sd_user_id'] - $distance;

        $data = User::select('sd_user_id as user_id')->whereBetween('sd_user_id', [$from, $uid['sd_user_id']])->orderByRaw('RAND()')->limit($limit)->get()->toArray();

        return $data;
    }

    /**
     * 获取用户表：昵称,手机号
     *
     * @param $userId
     * @return array
     */
    public static function getUser($userId)
    {
        $data = User::select(['username', 'mobile'])->where(['sd_user_id' => $userId])->first();

        return $data ? $data : [];
    }

    /**
     * 获取用户头像
     *
     * @param $userId
     * @return string
     */
    public static function getUserInfo($userId)
    {
        $data = UserInfo::where(['user_id' => $userId])->value('user_photo');

        return $data ? $data : "";
    }

    /**
     * 根据vip_type_nid 查找对应id
     * @param string $param
     * @return string
     */
    public static function fetchIdByVipType($param = '')
    {
        $id = UserVipType::where(['type_nid' => $param, 'status' => 1])->value('id');

        return $id ? $id : "";
    }

    /**
     * 获取会员信息【包括非正常状态】
     * @param $userid
     * @return array
     */
    public static function getInfo($userid)
    {
        $where = [
            'user_id' => $userid,
            'vip_type' => UserVipConstant::VIP_TYPE,
        ];

        $res = UserVip::select('*')->where($where)->first();
        return $res ? $res->toArray() : [];
    }


    /**
     * 获取会员VIP信息
     * @param $userid
     * @return array
     */
    public static function getVIPInfo($userid, $vipTypeId)
    {
        $now = date('Y-m-d H:i:s');
        $where = [
            'user_id' => $userid,
            'status' => 1,
            'vip_type' => $vipTypeId,
        ];

        $res = UserVip::select('*')->where($where)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 获取会员的最后的ID
     *
     * @return int|mixed
     */
    public static function getVipLastId()
    {
        $id = UserVip::orderBy('id', 'desc')->value('id');

        return $id ? $id : 1;
    }

    /**
     * 获取用户viptype主键id
     * @param $userId
     * @return int
     */
    public static function fetchUserVipToTypeByUserId($userId)
    {
        $now = date('Y-m-d H:i:s');
        $where = [
            'user_id' => $userId,
            'status' => 1,
        ];
        $res = UserVip::select('vip_type')->where($where)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->first();

        return $res ? $res->vip_type : 0;
    }

    /**
     * 获取vip type_nid 的值
     * @status 使用状态, 1 使用中, 0 未使用
     * @param $id
     * @return string
     */
    public static function fetchVipTypeById($id)
    {
        $type = UserVipType::select(['type_nid'])
            ->where(['id' => $id, 'status' => 1])
            ->first();

        return $type ? $type->type_nid : '';
    }
}
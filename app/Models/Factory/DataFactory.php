<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataBairongLog;
use App\Models\Orm\DataPosLog;
use App\Models\Orm\PlatformProductArea;
use App\Models\Orm\UserAreas;
use App\Models\Orm\UserDeviceLocation;
use App\Models\Orm\UserDeviceLocationLog;
use App\Models\Orm\UserIdfa;

/**
 * Data工厂
 * Class DataFactory
 * @package App\Models\Factory
 */
class DataFactory extends AbsModelFactory
{
    /**
     * post机申请流水
     *
     * @param array $data
     * @return bool
     */
    public static function insertPostLog($data = [])
    {
        $messages = new DataPosLog();
        $messages->name = $data['name'];
        $messages->mobile = $data['mobile'];
        $messages->address = $data['address'];
        $messages->content = $data['content'];
        $messages->created_at = date('Y-m-d H:i:s', time());
        $messages->created_ip = Utils::ipAddress();

        return $messages->save();
    }

    /**
     * 记录百融信息流水
     *
     * @param array $data
     * @return bool
     */
    public static function insertBairongLog($data = [])
    {
        $messages = new DataBairongLog();
        $messages->user_type = $data['user_type'];
        $messages->error_code = isset($data['code']) ? $data['code'] : 0;
        $messages->swift_number = isset($data['swift_number']) ? $data['swift_number'] : 0;
        $messages->mobile = $data['mobile'];
        $messages->content = $data['content'];
        $messages->created_at = date('Y-m-d H:i:s', time());
        $messages->created_ip = Utils::ipAddress();

        return $messages->save();
    }

    /**
     * 根据投放标识、用户id查数据
     * @param array $params
     * @return array
     */
    public static function fetchUserIdfaByUserIdEmpty($params = [])
    {
        $model = UserIdfa::where(['idfa_id' => $params['idfaId'], 'user_id' => 0])
            ->limit(1)
            ->first();

        return $model ? $model->toArray() : [];
    }

    /**
     * 根据投放标识查数据
     * @param array $params
     * @return array
     */
    public static function fetchUserIdfaByIdfaid($params = [])
    {
        $model = UserIdfa::where(['idfa_id' => $params['idfaId']])
            ->limit(1)
            ->first();

        return $model ? $model->toArray() : [];
    }

    /**
     * 创建投放数据
     * @param array $params
     * @return bool
     */
    public static function createUserIdfa($params = [])
    {
        $model = UserIdfa::where(['idfa_id' => $params['idfaId']])
            ->limit(1)
            ->first();
        if (empty($model)) {
            $model = new UserIdfa();
        }
        $model->idfa_id = $params['idfaId'];
        $model->user_id = $params['userId'];
        $model->updated_at = date('Y-m-d H:i:s', time());
        $model->updated_ip = Utils::ipAddress();

        return $model->save();
    }

    /**
     * 标识、用户信息
     * @param array $params
     * @return bool
     */
    public static function updateUserIdfaByIds($params = [])
    {
        $model = UserIdfa::where(['idfa_id' => $params['idfaId'], 'user_id' => $params['userId']])
            ->limit(1)
            ->first();

        if (empty($model)) {
            $model = new UserIdfa();
        }
        $model->idfa_id = $params['idfaId'];
        $model->user_id = $params['userId'];
        $model->updated_at = date('Y-m-d H:i:s', time());
        $model->updated_ip = Utils::ipAddress();

        return $model->save();
    }
}
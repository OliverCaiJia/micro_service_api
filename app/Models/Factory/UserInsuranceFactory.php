<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\UserInsuranceLog;
use App\Helpers\Utils;
use App\Constants\UserInsuranceConstant;

class UserInsuranceFactory extends  AbsModelFactory
{

    //增加一条记录
    public static function createInsurance($data=[])
    {
        $model = new UserInsuranceLog();

        $model->user_id = $data['user_id'];
        $model->type_nid = $data['type_nid'];
        $model->mobile = $data['mobile'];
        $model->certificate_no = $data['certificate_no'];
        $model->username = $data['realname'];
        $model->remark = $data['remark'];
        $model->channel_num = $data['channel_num'];
        $model->created_at = date('Y-m-d H:i:s',time());
        $model->updated_at = date('Y-m-d H:i:s',time());
        $model->created_ip =Utils::ipAddress();
        $model->updated_ip =Utils::ipAddress();

        return $model->save();

    }
    //增加一条记录更新记录
    public static function updatedInsurance($userId,$data=[])
    {
        $model = UserInsuranceLog::where('user_id',$userId)->orderBy('id','desc')->first();

        if($model){
            $model->status = $data['bizData']['code']=='000'?1:2;
            $model->result = json_encode($data);
            $model->updated_at = date('Y-m-d H:i:s',time());
            $model->updated_ip =Utils::ipAddress();
            $model->save();
            return $model;
        }
        return false;

    }


}

<?php

namespace App\Models\Factory;

use App\Constants\BankConstant;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\Banks;
use App\Models\Orm\EventUser;
use App\Models\Orm\UserAlipay;
use App\Models\Orm\UserBanks;

class EventUserFactory extends AbsModelFactory
{
    /**
     * 插入信息
     *
     * @param $data
     * @return bool
     */
    public static function insertEventUser($data)
    {
        $eventUser = new EventUser();
        $eventUser->mobile = $data['mobile'];
        $eventUser->status = $data['status'];
        $eventUser->created_at = date('Y-m-d H:i:s', time());
        $eventUser->created_ip = Utils::ipAddress();

        return $eventUser->save();
    }

}

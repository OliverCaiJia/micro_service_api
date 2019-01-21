<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserContacts;
use App\Models\Orm\UserContactsLog;

/**
 * Class ContactsFactory
 * @package App\Models\Factory
 * 通讯录
 */
class ContactsFactory extends AbsModelFactory
{
    /**
     * @param $data
     * @return bool
     * 通讯录流水
     */
    public static function createContactsLog($data)
    {
        $contacts = $data['contacts'];
        foreach ($contacts as $key => $val) {
            $log = new UserContactsLog();
            $log->user_id = $data['userId'];
            $log->device_id = $data['device_id'];
            $log->name = isset($val['name']) ? $val['name'] : '';
            $log->phone = Utils::removeSpaces($val['phone']);
            $log->email = isset($val['email']) ? $val['email'] : '';
            $log->address = isset($val['address']) ? $val['address'] : '';
            $log->company = isset($val['company']) ? $val['company'] : '';
            $log->birthday = isset($val['birthday']) ? $val['birthday'] : '';
            $log->user_agent = UserAgent::i()->getUserAgent();
            $log->status = 0;
            $log->created_at = date('Y-m-d H:i:s', time());
            $log->created_ip = Utils::ipAddress();
            $re = $log->save();
        }
        return $re ? $re : false;

    }

    /**
     * @param $data
     * @return bool
     * 创建或修改用户通讯录
     */
    public static function createOrUpdateContacts($data)
    {
        $contacts = $data['contacts'];
        foreach ($contacts as $key => $val) {
            $phone = Utils::removeSpaces($val['phone']);
            $contact = UserContacts::firstOrCreate(['user_id' => $data['userId'], 'phone' => $phone], [
                'user_id' => $data['userId'],
                'device_id' => $data['device_id'],
                'name' => isset($val['name']) ? $val['name'] : '',
                'phone' => $phone,
                'email' => isset($val['email']) ? $val['email'] : '',
                'address' => isset($val['address']) ? $val['address'] : '',
                'company' => isset($val['company']) ? $val['company'] : '',
                'birthday' => isset($val['birthday']) ? $val['birthday'] : '',
                'user_agent' => UserAgent::i()->getUserAgent(),
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s', time()),
                'created_ip' => Utils::ipAddress(),
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);
            $contact->user_id = $data['userId'];
            $contact->device_id = $data['device_id'];
            $contact->name = isset($val['name']) ? $val['name'] : '';
            $contact->email = isset($val['email']) ? $val['email'] : '';
            $contact->address = isset($val['address']) ? $val['address'] : '';
            $contact->company = isset($val['company']) ? $val['company'] : '';
            $contact->birthday = isset($val['birthday']) ? $val['birthday'] : '';
            $contact->user_agent = UserAgent::i()->getUserAgent();
            $contact->status = 0;
            $contact->updated_at = date('Y-m-d H:i:s');
            $contact->updated_ip = Utils::ipAddress();
            $re = $contact->save();
        }

        return $re ? $re : false;
    }
}
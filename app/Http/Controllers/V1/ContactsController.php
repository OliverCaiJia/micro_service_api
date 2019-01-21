<?php

namespace App\Http\Controllers\V1;

use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\ContactsFactory;
use Illuminate\Http\Request;

/**
 * Class ContactsController
 * @package App\Http\Controllers\V1
 * 通讯录
 */
class ContactsController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 获取用户通讯录信息
     */
    public function createOrUpdateContacts(Request $request)
    {
        $data['contacts'] = $request->input('contacts', '');
        $data['device_id'] = $request->input('device_id', '');
        $data['userId'] = $request->user()->sd_user_id;
        //联系人信息为空
        if (empty($data['contacts'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(9000), 9000);
        }

        /**
         *  暂停收集手机号
         */
        // 通讯录流水
        $contactsLog = ContactsFactory::createContactsLog($data);
        // 修改通讯录
        $contacts = ContactsFactory::createOrUpdateContacts($data);

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }
}
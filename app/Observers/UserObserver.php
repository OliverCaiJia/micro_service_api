<?php

namespace App\Observers;

use App\Models\Orm\User;
use App\Observers\AppObserver;

class UserObserver extends AppObserver
{

    /**
     * 监听用户创建的事件。
     *
     * @param  User  $user
     * @return void
     */
    public function created(User $user)
    {
        //
    }

    /**
     * 监听用户删除事件。
     *
     * @param  User  $user
     * @return void
     */
    public function deleting(User $user)
    {
        //
    }

}

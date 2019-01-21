<?php

namespace App\Events\Shadow;

use App\Constants\CreditConstant;
use App\Events\AppEvent;
use App\Helpers\Utils;
use App\Models\Orm\UserCredit;
use App\Models\Orm\UserInviteLog;
use App\Strategies\CreditStrategy;

/**
 * 用户马甲事件
 * Class UserRegEvent
 * @package App\Events
 */
class UserShadowEvent extends AppEvent
{

    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $event = [])
    {
        $this->data = isset($event) ? $event : [];
    }

}

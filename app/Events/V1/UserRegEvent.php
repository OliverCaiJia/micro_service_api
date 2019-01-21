<?php

namespace App\Events\V1;

use App\Constants\CreditConstant;
use App\Events\AppEvent;
use App\Helpers\Utils;
use App\Models\Orm\UserCredit;
use App\Models\Orm\UserInviteLog;
use App\Strategies\CreditStrategy;

/**
 * 注册有关事件
 * Class UserRegEvent
 * @package App\Events
 */
class UserRegEvent extends AppEvent
{

    public $invite;
    public $notice;
    public $count;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $event = [])
    {
	    $this->invite = isset($event['invite']) ? $event['invite'] : [];
        $this->notice = isset($event['notice']) ? $event['notice'] : [];
        $this->count = isset($event['count']) ? $event['count'] : [];
    }

}

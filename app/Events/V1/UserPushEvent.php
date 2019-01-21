<?php
namespace App\Events\V1;

use App\Events\AppEvent;

/**
 * Class PushEvent
 * @package App\Events\V1
 * 推送监听
 */
class UserPushEvent extends AppEvent
{
    public $push;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $event = [])
    {
        $this->push = isset($event['push']) ? $event['push'] : [];
    }
}
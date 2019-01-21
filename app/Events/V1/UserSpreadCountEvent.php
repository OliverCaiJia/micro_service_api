<?php

namespace App\Events\V1;

use App\Events\AppEvent;

/**
 * 推广统计事件
 * Class UserSpreadCountEvent
 * @package App\Events
 */
class UserSpreadCountEvent extends AppEvent
{

    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

}

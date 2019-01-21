<?php

namespace App\Events\V2;

use App\Events\AppEvent;

class UserLoginEvent extends AppEvent
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

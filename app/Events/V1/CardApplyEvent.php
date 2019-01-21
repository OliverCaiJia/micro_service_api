<?php

namespace App\Events\V1;

use App\Events\AppEvent;

class CardApplyEvent extends AppEvent
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

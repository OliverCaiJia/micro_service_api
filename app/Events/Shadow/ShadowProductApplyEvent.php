<?php

namespace App\Events\Shadow;

use App\Events\AppEvent;

class ShadowProductApplyEvent extends AppEvent
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

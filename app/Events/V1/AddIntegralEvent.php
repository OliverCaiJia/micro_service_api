<?php

namespace App\Events\V1;

use App\Events\AppEvent;

/**
 * Class AddCreditEvent
 * @package App\Events\V1
 * 加积分的事件
 */
class AddIntegralEvent extends AppEvent
{

    public $data;

    /**
     * AddIntegralEvent constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

}

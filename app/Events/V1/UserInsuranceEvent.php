<?php
namespace App\Events\V1;

use App\Events\AppEvent;

/**
 * 赠送保险事件
 * Class UserInsuranceEvent
 * @package App\Events\V1
 */
class UserInsuranceEvent extends AppEvent
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
<?php
namespace App\Events\V1;

use App\Events\AppEvent;

/**
 * 用户推广事件
 * Class UserInsuranceEvent
 * @package App\Events\V1
 */
class UserSpreadEvent extends AppEvent
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
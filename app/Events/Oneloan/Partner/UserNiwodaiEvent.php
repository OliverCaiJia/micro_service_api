<?php
namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;

/**
 * 你我贷事件
 * Class UserInsuranceEvent
 * @package App\Events\V1
 */
class UserNiwodaiEvent extends AppEvent
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
<?php

namespace App\Events\Oneloan\Partner;


use App\Events\AppEvent;

/**
 * 融时代
 * Class UserInsuranceEvent
 * @package App\Events\V1
 */
class UserRongshidaiEvent extends AppEvent
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
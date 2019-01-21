<?php
namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;

/**
 * 新一贷事件
 * Class UserInsuranceEvent
 * @package App\Events\V1
 */
class UserNewLoanEvent extends AppEvent
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
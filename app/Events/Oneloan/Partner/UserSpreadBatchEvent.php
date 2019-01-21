<?php
namespace App\Events\Oneloan\Partner;

use App\Events\AppEvent;

/**
 * 延迟推送事件
 *
 * Class UserSpreadBatchEvent
 * @package App\Events\V1
 */
class UserSpreadBatchEvent extends AppEvent
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
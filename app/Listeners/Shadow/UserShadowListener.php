<?php

namespace App\Listeners\Shadow;

use App\Events\AppEvent;
use App\Models\Chain\UserShadow\DoUserShadowHandler;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;

/** 用户马甲事件监听
 * Class AddCreditListener
 * @package App\Listeners\V1
 *
 */
class UserShadowListener extends AppListener
{

    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AppEvent  $event
     * @return void
     */
    public function handle(AppEvent $event)
    {
        // 责任链处理马甲用户
        $shadowHandler = new DoUserShadowHandler($event->data);
        $shadowHandler->handleRequest();
    }

}

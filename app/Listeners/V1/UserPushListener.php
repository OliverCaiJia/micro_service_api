<?php
namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Listeners\AppListener;
use App\Models\Chain\UserPush\DoUserPushHandler;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class PushListener
 * @package App\Listeners\V1
 * 推送监听
 */
class UserPushListener extends AppListener
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
        $push = $event->push;
        #调用推送责任链
        $pushObj = new DoUserPushHandler($push);
        $pushObj->handleRequest();

    }
}
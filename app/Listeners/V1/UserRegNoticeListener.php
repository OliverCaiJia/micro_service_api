<?php

namespace App\Listeners\V1;

use App\Constants\CreditConstant;
use App\Events\AppEvent;
use App\Helpers\Utils;
use App\Models\Orm\UserCredit;
use App\Models\Orm\UserInviteLog;
use App\Strategies\CreditStrategy;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Listeners\AppListener;
use DB;
use App\Helpers\Logger\SLogger;

class UserRegNoticeListener extends AppListener implements ShouldQueue
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
        $notice = $event['notice'];
        
    }

}

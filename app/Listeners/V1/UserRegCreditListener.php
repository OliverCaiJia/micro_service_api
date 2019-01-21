<?php

namespace App\Listeners\V1;

use App\Constants\CreditConstant;
use App\Constants\InviteConstant;
use App\Events\AppEvent;
use App\Helpers\Utils;
use App\Models\Chain\Invite\DoInviteHandler;
use App\Models\Factory\InviteFactory;
use App\Models\Factory\UserFactory;
use App\Models\Orm\UserCredit;
use App\Models\Orm\UserInviteLog;
use App\Strategies\CreditStrategy;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Listeners\AppListener;
use DB;
use App\Helpers\Logger\SLogger;

class UserRegCreditListener extends AppListener
{


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
        $invite = $event->invite;
	    if (isset($invite['sd_invite_code']) && !empty($invite['sd_invite_code'])) {
	    	//如果邀请码存在并且不为空,则根据邀请码获得邀请人id
		    $invite['user_id'] = InviteFactory::fetchInviteUserIdByCode($invite['sd_invite_code']);
	    }
	    if (!empty($invite['user_id'])) {
	    	//如果邀请人id不存在，则根据手机号和状态去邀请日志表中获得邀请人id
		    $invite['user_id'] =  InviteFactory::fetchInviteUserIdByMobileFromLog($invite['mobile']);
	    }
	    
//	    if (empty($invite['user_id']) && empty($invite['sd_invite_code'])) {
//	    	//如果邀请码和邀请人id都不存在,则去查邀请记录日志表中是否有这个人的手机号
//	    	$invitelog = InviteFactory::fetchInviteLog($invite);
//		    if ($invitelog) {
//		    	//如果存在，则更新日志表中的状态为2
//		    	InviteFactory::updateInviteLogStatus($invite['mobile'],$invite['invite_user_id']);
//		    }
//	    }
	    
	    #调用邀请责任链
	    $inviteObj = new DoInviteHandler($invite);
	    $inviteObj->handleRequest();
	    
    }
	
}

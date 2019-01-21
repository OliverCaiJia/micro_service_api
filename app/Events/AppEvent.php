<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

abstract class AppEvent implements ShouldBroadcast
{
    use SerializesModels;
	
	
	public function broadcastOn(){
		
	}
}

<?php
namespace App\Models;

abstract class AbsModelFactory
{
	/**
	 * Instantiate a new Controller instance.
	 */
	public function __construct()
	{
		date_default_timezone_set('Asia/Shanghai'); //时区配置
	}
    
}

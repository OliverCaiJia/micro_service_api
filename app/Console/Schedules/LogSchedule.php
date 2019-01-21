<?php

namespace App\Console\Schedules;

use App\Console\Schedules\AppSchedule;

/**
 * crontab -e | * * * * * php /path/api.jdt.com/artisan schedule:run 1>> /dev/null 2>&1
 */
// 切割日志每天调度器
class LogSchedule extends AppSchedule
{

    public function action()
    {
        
    }

}

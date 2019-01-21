<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\UserSpreadBatchCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    // 定时任务配置
    // * * * * * php /data/www/api.sudaizhijia.com/artisan schedule:run >> /dev/null 2>&1
    protected function schedule(Schedule $schedule)
    {
        $schedule->call('App\Console\Schedules\LogSchedule@action')->cron('0 */1 * * *');

        $schedule->command('UserSpreadBatchCommand')->cron('* * * * *')->withoutOverlapping();

    }

}

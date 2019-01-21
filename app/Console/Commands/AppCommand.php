<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'AppCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an AppCommands';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment(PHP_EOL . '==' . PHP_EOL);
    }

}

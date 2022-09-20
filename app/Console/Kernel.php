<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (config('schedule.enabled')) {
            $schedule->command('app:queue-update-followed-artists')->cron('0 0,12 * * *');
            $schedule->command('app:queue-add-followed-albums')->cron('2 0,12 * * *');
            $schedule->command('app:queue-add-new-releases')->cron('4 0,12 * * *');
            $schedule->command('app:queue-clear-artists')->cron('6 0,12 * * *');
            $schedule->command('app:queue-update-albums')->cron('8 0,12 * * *');
            $schedule->command('app:warm-up-cache')->cron('10 0,12 * * *');
            $schedule->command('app:scan-artists-with-missed-genres')->cron('0 1 * * *');
            $schedule->command('app:rate-limit-check')->everyThirtyMinutes();
            $schedule->command('app:cache-ttl-test')->everyFifteenMinutes();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

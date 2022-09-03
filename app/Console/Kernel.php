<?php

namespace App\Console;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (config('schedule.enabled')) {
            $schedule->command('app:queue-update-followed-artists')->everySixHours();
            $schedule->command('app:queue-add-followed-albums')->everySixHours();
            $schedule->command('app:queue-update-albums')->twiceDaily(2, 14);
            $schedule->command('app:queue-clear-artists')->daily();
            $schedule->command('app:queue-add-new-releases')->twiceDaily(1, 13);
            $schedule->command('app:scan-artists-with-missed-genres')->daily();
            $schedule->command('app:rate-limit-check')->everyThirtyMinutes();
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

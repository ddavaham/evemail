<?php

namespace EVEMail\Console;

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
        "EVEMail\Console\Commands\MailHeaderUpdater",
        "EVEMail\Console\Commands\PurgeOldMailBodies",
        "EVEMail\Console\Commands\ProcessQueue",
        "EVEMail\Console\Commands\PurgeDisabledTokens"
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('mail:update_headers')->everyMinute();
        $schedule->command('mail:process_queue')->everyMinute();
        $schedule->command('mail:purge_disabled_tokens')->everyMinute();
        $schedule->command('mail:purge_old_mails')->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}

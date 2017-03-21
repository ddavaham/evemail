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
        "EVEMail\Console\Commands\PurgeDisabledTokens",
        "EVEMail\Console\Commands\PurgeNewAccounts",
        "EVEMail\Console\Commands\TableSync"
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //Dont run this command during daily downtime. This will prevent our database filling up with a few hundred error messages
        $schedule->command('mail:update_headers')->everyMinute()->unlessBetween('11:00', '11:30');
        $schedule->command('mail:process_queue')->everyMinute();
        $schedule->command('mail:purge_old_mails')->hourly();
        $schedule->command('mail:purge_disabled_tokens')->hourly();
        //Runt this Command During Downtime.
        $schedule->command('mail:purge_new_accounts')->dailyAt('11:05');        
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

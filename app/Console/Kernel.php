<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CacheRates;
use App\Console\Commands\NotifyUserAboutSignalRenewal;
use App\Console\Commands\CheckUserSubscription;
use App\Console\Commands\SendSignalNotifications;
use App\Console\Commands\SendMessageNotifications;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cache:cryptoRates')->everyFifteenMinutes();
        $schedule->command('create:systemAccount')->everyFiveMinutes();
        $schedule->command('notify:userAboutRenewal')->everyFiveMinutes();
        $schedule->command('check:userSubscription')->everyFiveMinutes();
        $schedule->command('send:signalNotification')->everyMinute();
        $schedule->command('send:messageNotification')->everyFiveMinutes();
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

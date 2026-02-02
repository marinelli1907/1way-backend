<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Cancel stale trips (runs every minute)
        $schedule->command('trip-request:cancel')
            ->everyMinute()
            ->withoutOverlapping(2);

        // Repair drift between trip_requests.current_status and trip_status timeline (runs daily at 03:00)
        $schedule->command('trip:repair-statuses')
            ->dailyAt('03:00')
            ->withoutOverlapping(30)
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

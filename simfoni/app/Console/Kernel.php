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
        // cek notifikasi setiap hari (SIMFONI lama)
        $schedule->call(function () {
            app(\App\Http\Controllers\FenomenaController::class)->checkNotifications();
        })->daily();

        // scrape berita 7 hari sebelumnya jam 04:00 WIB
        $schedule->command('otomatisasi:scrape')
            ->dailyAt('04:00')
            ->timezone(\App\Console\Commands\ScrapeBeritaOtomatis::ZONA_WAKTU)
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

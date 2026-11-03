<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Pengingat Otomatis Pengembalian Barang Inventaris Kecamatan Cikampek
        // Berjalan setiap menit untuk mengecek jatuh tempo & jendela reminder sebelum batas jam pengembalian (WIB)
        $schedule->command('inventory:send-return-reminders')
            ->everyMinute()
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

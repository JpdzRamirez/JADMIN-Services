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
        'App\Console\Commands\ConductoresBloqueados',
        'App\Console\Commands\ConductoresDisponibles',
        'App\Console\Commands\ProgramadosPendientes',
        'App\Console\Commands\CuotasVencidas',
        //'App\Console\Commands\CuotasMensajes',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command('conductores:bloqueados')->everyMinute();

         $schedule->command('conductores:disponibles')->everyMinute();

         $schedule->command('programados:pendientes')->everyFiveMinutes();

         $schedule->command('mensajes:programados')->everyThirtyMinutes();

         $schedule->command('cuotas:vencidas')->dailyAt("00:01");

         //$schedule->command('cuotas:mensajes')->dailyAt("12:05");
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

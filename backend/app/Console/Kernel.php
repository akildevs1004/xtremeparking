<?php

namespace App\Console;

use App\Http\Controllers\AlramEventsController;
use App\Http\Controllers\Customers\Api\ApiAlarmDeviceTemperatureLogsController;
use App\Http\Controllers\Customers\CustomersController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SDKController;
use App\Models\AccessControlTimeSlot;
use App\Models\Company;
use App\Models\DeviceActivesettings;
use App\Models\PayrollSetting;
use App\Models\ReportNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

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




        $monthYear = date("M-Y");


        $schedule
            ->command("task:files-delete-old-log-files")
            ->dailyAt('23:30')
            //->withoutOverlapping()
            ->appendOutputTo(storage_path("kernal_logs/$monthYear-delete-old-logs.log"))

        ;;
        //send invoice mails
        $schedule
            ->command("task:customer-invoice-reminder-notifications")
            ->dailyAt('13:00')
            //->withoutOverlapping()
            ->appendOutputTo(storage_path("kernal_logs/$monthYear-invoice_reminders.log"))

        ;;


        /*------------------------ */
        $schedule->call(function () {
            (new AlramEventsController)->verifyOfflineDevices();
        })->everyFiveMinutes();



        /*------------------------ */
        $schedule->call(function () {
            return (new CustomersController)->clearDeviceNotification();
        })->dailyAt('00:00')

            ->appendOutputTo(storage_path("kernal_logs/" . date("d-M-Y") . "-notification-armed-with-shop-time.log"));


        if (env("APP_ENV") == "production") {
            $schedule
                ->command('task:db_backup')
                ->dailyAt('6:00')
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        //$this->commands[] = \App\Console\Commands\MqttListenCommand::class;
        $this->commands[] = \App\Console\Commands\MqttListenQRCommand::class;

        require base_path('routes/console.php');
    }
}

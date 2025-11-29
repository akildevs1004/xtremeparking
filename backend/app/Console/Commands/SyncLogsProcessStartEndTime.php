<?php

namespace App\Console\Commands;

use App\Http\Controllers\AbsentController;
use App\Http\Controllers\Customers\Api\ApiAlarmDeviceTemperatureLogsController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;

class SyncLogsProcessStartEndTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_alarm_logs_update_start_end_time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync sync_alarm_logs_update_start_end_time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // echo (new ApiAlarmDeviceTemperatureLogsController)->updateAlarmResponseTime();
    }
}

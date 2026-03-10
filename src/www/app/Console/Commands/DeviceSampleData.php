<?php

namespace App\Console\Commands;

use App\Http\Controllers\Alarm\DeviceSensorLogsController;
use App\Http\Controllers\AlarmLogsController;
use App\Http\Controllers\CompanyController;
use App\Models\AttendanceLog;
use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyIfLogsDoesNotGenerate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DeviceSampleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:device_sample_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Sample Data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $data = [
        //     "serialNumber" => "24000001",
        //     "temperature" =>  mt_rand(180, 260) / 10,
        //     "humidity" =>  mt_rand(400, 600) / 10,
        //     "fire_alarm" => 0,
        //     "smokeStatus" =>  0,
        //     "waterLeakage" =>  0,
        //     "acPowerFailure" =>  0,
        //     "doorOpen" =>  0,



        // ];
        // $response = Http::withoutVerifying()->withHeaders([
        //     'Content-Type' => 'application/json',
        // ])->post("https://backend.xtremeguard.org/api/alarm_device_status", $data);
        // $data = $response->body();

        // return $data;
    }
}

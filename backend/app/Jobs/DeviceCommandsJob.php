<?php

namespace App\Jobs;

use App\Services\MqttService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeviceCommandsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */



    public function handle()
    {

        $data = $this->data;

        if ($data["serialNumber"]) {
            $postData = $data;

            // Publish  to MQTT
            $mqtt = new MqttService();
            $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/{$data['serialNumber']}/config/request",  json_encode($postData), $data["serialNumber"]);
        }
    }
}

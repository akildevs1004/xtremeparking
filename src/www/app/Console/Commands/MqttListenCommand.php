<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;
use App\Services\MqttServiceQRCodePayment;

class MqttListenCommand extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Start MQTT subscribe and listen loop';

    public function handle(MqttService $mqtt)
    {
        $this->info("🚀 Starting MQTT listener service...");
        $mqtt->subscribeAndListen(); // this blocks and keeps running



    }
}

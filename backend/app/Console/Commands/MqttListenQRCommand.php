<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;
use App\Services\MqttServiceQRCodePayment;

class MqttListenQRCommand extends Command
{
    protected $signature = 'mqtt:qrbackgroundlistener';
    protected $description = 'Start MQTT QR subscribe and listen loop';

    public function handle(MqttServiceQRCodePayment $mqtt)
    {
        $this->info("🚀 Start MQTT QR subscribe and listen loop");


        $mqtt->subscribeAndListen(); // this blocks and keeps running
    }
}

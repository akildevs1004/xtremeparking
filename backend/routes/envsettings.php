<?php

use App\Exports\ParkingReports;
use App\Http\Controllers\SecurityLoginController;
use App\Http\Controllers\Dashboards\SOSRoomsControllers;
use App\Http\Controllers\DeviceCameraModel2Controller;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceStatusController;
use App\Http\Controllers\DeviceTemperatureSensorsController;
use App\Http\Controllers\ParkingCameraLogsController;
use App\Http\Controllers\SecuritySosRoomsController;
use App\Models\Device;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SecuritySosRoomsListController;


//tv cmds
Route::get('/envsettings',  function (Request $request) {

    // return [
    //     "MQTT_SOCKET_HOST" => "wss://mqtt.xtremeguard.org:8084",
    //     "MQTT_DEVICE_CLIENTID" => "xtremesos",
    //     "TV_COMPANY_ID" => "8",
    //     "BACKEND_URL2" => "https://165.22.222.17:8000",
    // ];

    // The IP/domain the client used to reach THIS backend (server public IP)
    $serverAddr = $request->server('SERVER_ADDR');          // often the server IP
    $serverName = $request->server('SERVER_NAME');          // domain (if any)

    // Fallback: if behind proxy/load balancer, you can also check configured APP_URL host
    // $appHost = parse_url(config('app.url'), PHP_URL_HOST);

    if ($serverAddr === '165.22.222.17') {
        return [
            "MQTT_SOCKET_HOST" => "wss://mqtt.xtremeguard.org:8084",
            "MQTT_DEVICE_CLIENTID" => "xtremesos",
            "TV_COMPANY_ID" => "8",
            "BACKEND_URL2" => "https://{$serverAddr}:8000",

            "MQTT_QRCODE_PAYMENT" => "mqtt://xtremeguard.org",
            "host" => "mqtts://{$serverAddr}:8083",
            "WATCH_DIR" => "D:\projects\vehicleparkingbills\parking_camera_logs\data",
            "COMPANY_ID" =>  "8",
            "API_URL" =>  "https://{$serverAddr}:8000/api/camera_log_listner",
            "API_KEY" =>  "",
            "MQTT_SERVER" =>  "wss://{$serverAddr}:8083",
            "MQTT_FRONTEND" =>  "mqtts://{$serverAddr}:8083",

            //live camera
            "BASE_HTTP_PORT" =>  "7081",
            "BASE_WS_PORT" =>  "9991",
        ];
    }

    // Default (dynamic from controller)
    $ip = (new ParkingCameraLogsController())->getServerIp();

    return [
        "MQTT_SOCKET_HOST" => "mqtt://{$ip}:8083",
        "MQTT_DEVICE_CLIENTID" => "xtremesos",
        "TV_COMPANY_ID" => "8",
        "BACKEND_URL2" => "http://{$ip}:8000",


        "MQTT_QRCODE_PAYMENT" => "mqtt://xtremeguard.org",
        "host" => "mqtt://{$ip}:8083",
        "WATCH_DIR" => "D:\projects\vehicleparkingbills\parking_camera_logs\data",
        "COMPANY_ID" =>  "8",
        "API_URL" =>  "http://{$ip}:8000/api/camera_log_listner",
        "API_KEY" =>  "",
        "MQTT_SERVER" =>  "ws://{$ip}:8083",
        "MQTT_FRONTEND" =>  "mqtt://{$ip}:8083",

        //live camera
        "BASE_HTTP_PORT" =>  "7081",
        "BASE_WS_PORT" =>  "9991",






    ];
});

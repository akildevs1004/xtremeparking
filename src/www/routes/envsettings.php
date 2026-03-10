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


    return (new ParkingCameraLogsController())->getEnvSettings($request);
});

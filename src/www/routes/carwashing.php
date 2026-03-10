<?php

use App\Http\Controllers\AccessControlController;
use App\Http\Controllers\CarWashing\CarDashboardController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Parking\CameraLogListenerController;
use App\Http\Controllers\ParkingCameraLogsController;
use App\Http\Controllers\ParkingDeviceController;
use App\Http\Controllers\ParkingMembersController;
use App\Http\Controllers\ParkingMembersVehiclesListController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Reports\DailyController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Reports\WeeklyController;
use App\Http\Controllers\Reports\MonthlyController;
use App\Http\Controllers\Reports\MonthlyMergeController;
use App\Http\Controllers\Reports\MonthlyMimoController;
use App\Http\Controllers\Reports\PDFController;
use App\Http\Controllers\Reports\PDFTestController;
use App\Http\Controllers\Reports\WeeklyMimoController;
use App\Http\Controllers\StripeController;
use App\Models\ParkingCameraLogs;
use App\Models\ParkingMembers;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


Route::get('/dashboard_carwashingrooms', [CarDashboardController::class, 'roomsListStatus']);
Route::get('/dashboard_carwashingstatistics', [CarDashboardController::class, 'Dashboardstatistics']);

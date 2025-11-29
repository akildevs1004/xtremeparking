<?php


use App\Http\Controllers;
use App\Http\Controllers\AlarmLogsController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementsCategoriesController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Customers\Alarm\AlarmNotificationController;
use App\Http\Controllers\Customers\Alarm\DeviceArmedLogsController;
use App\Http\Controllers\Customers\AlarmDashboard;
use App\Http\Controllers\Customers\AlarmDashboardController;
use App\Http\Controllers\Customers\AlarmDeviceTemperatureLogsController;
use App\Http\Controllers\Customers\Api\ApiAlarmDeviceSensorLogsController;
use App\Http\Controllers\Customers\Api\ApiAlarmDeviceTemperatureLogsController;
use App\Http\Controllers\Customers\CustomerAlarmEventsController;
use App\Http\Controllers\Customers\CustomerBuildingPicturesController;
use App\Http\Controllers\Customers\CustomerContactsController;
use App\Http\Controllers\Customers\CustomerPaymentsController;
use App\Http\Controllers\Customers\CustomersController;
use App\Http\Controllers\Customers\Reports\AlarmReportsController;
use App\Http\Controllers\Customers\SecurityLoginController;
use App\Http\Controllers\DeviceNotificationsManagersController;
use App\Http\Controllers\MasterDeviceSerialNumbersController;
use App\Http\Controllers\PlottingController;
use App\Models\AlarmLogs;
use App\Models\Customers\Customers;
use App\Models\DeviceArmedLogs;
use App\Models\MapKey;
use Illuminate\Support\Facades\Route;




Route::get('/master_dashboard', [CompanyController::class, 'getMasterDashboardCounts']);
Route::apiResource('master_device_serial_numbers', MasterDeviceSerialNumbersController::class);

<?php

use App\Http\Controllers\AccessControlController;
use App\Http\Controllers\CamerasListController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Parking\CameraLogListenerController;
use App\Http\Controllers\ParkingBlockedLogsController;
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
use App\Models\ParkingBlockedLogs;
use App\Models\ParkingCameraLogs;
use App\Models\ParkingMembers;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

Route::resource('parking_camera_logs', ParkingCameraLogsController::class);


Route::get('/parking_camera_logs_print_pdf', [ParkingCameraLogsController::class, 'ParkingCameraLogsPrintPdf']);
Route::get('/parking_camera_logs_download_pdf', [ParkingCameraLogsController::class, 'ParkingCameraLogsDownloadPdf']);
Route::get('/parking_camera_logs_export_excel', [ParkingCameraLogsController::class, 'ParkingCameraLogsDownloadCSV']);



Route::post('camera_log_listner', [CameraLogListenerController::class, 'CameraLogProcessing']);
Route::get('parking-cameras', [CameraLogListenerController::class, 'CamerasList']);


Route::get('parking_record_info', [ParkingCameraLogsController::class, 'parkingRecordInfo']);
Route::get('parking_dashboard_statistics', [ParkingCameraLogsController::class, 'parkingDashboardStatistics']);
Route::post('parking_payment_process', [ParkingCameraLogsController::class, 'parkingPaymentUpdate']);
// Route::post('parking_payment_update', [ParkingCameraLogsController::class, 'parkingPaymentUpdate']);



Route::resource('parking_members', ParkingMembersController::class);
Route::resource('parking_blocked_logs', ParkingBlockedLogsController::class);






Route::get('members_all', [ParkingMembersController::class, "membersAll"]);

Route::post('parking_member_product_invoice_submition',  [ParkingMembersController::class, 'MemberProductInvoiceSubmition']);
Route::post('parking_member_payments',  [ParkingMembersController::class, 'MemberPayments']);

Route::post('parking_open_gate',  [ParkingDeviceController::class, 'OpenGate']);
// Route::post('parking_close_gate',  [ParkingDeviceController::class, 'CloseGate']);

Route::post('device_acknowledged_from_device',  [ParkingDeviceController::class, 'DeviceAcknowledged']);


Route::post('command_call_device_to_arduino', [DeviceController::class, 'commandCallSocketToDevice']);

// Route::get('get_mqtt_server', [CameraLogListenerController::class, 'getMQTT']);
// Route::get('envsettings', [CameraLogListenerController::class, 'envsettings']);



Route::get('parking_log_live', [ParkingCameraLogsController::class, 'getLiveVehicleLogs']);

Route::post('parking_members/import-csv/preview', [ParkingMembersController::class, 'preview']);
Route::post('parking_members/import-csv/create',  [ParkingMembersController::class, 'createFromCSV']);








Route::post('parking_qr_get_vehicle_details', [CameraLogListenerController::class, 'getQROutVehicleDetails']);
Route::post('parking_qr_paymentresponse', [ParkingCameraLogsController::class, 'ParkingPaymentResponseUpdate']);
Route::get('/parking-receipts/{id}/print', [ParkingCameraLogsController::class, 'print']);
Route::post('/parking_qr_pay_extra_minutes', [ParkingCameraLogsController::class, 'qrParkingExtraPayment']);

Route::get('/parking_camera_logs/{company}/{filename}', [ParkingCameraLogsController::class, 'ParkingCameraImage']);







//generate the credit card form
Route::post('/stripe/create-payment-intent', [StripeController::class, 'createPaymentIntent']);

//display link from stripe and popup
Route::get('/stripe/create-payment-link', [StripeController::class, 'createLink']);
Route::get('/stripe/session/{id}',  [StripeController::class, 'getSession']); // fetch details










Route::post('/stripe/webhook', [StripeController::class, 'webhook']); // remember to skip CSRF for this route




// Route::get('get_customer_sensor_payment_package_details',  [CustomerPaymentsController::class, 'GetCustomerSensorsPaymentPackage']);


Route::resource('parking_members_vehiclesList', ParkingMembersVehiclesListController::class);
Route::post('parking_members_add_balance', [ParkingMembersVehiclesListController::class, "ParkingMembersAddBalance"]);





Route::get('test_generate_qrcode', function () {
    $url = "https://buy.stripe.com/test_00w28t0IU4eQgCm2ns7Re00"; // your custom URL

    $qr = QrCode::size(300)
        ->format('png')
        ->generate($url);

    return response($qr)->header('Content-Type', 'image/png');
});



Route::apiResource('cameraslist', CamerasListController::class);

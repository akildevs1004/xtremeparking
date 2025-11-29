<?php



use App\Http\Controllers\Customers\AlarmDashboardController;
use App\Http\Controllers\Customers\SecurityLoginController;
use App\Models\Customers\SecurityLogin;
use Illuminate\Support\Facades\Route;






//security dashboard
Route::get('security_device_alarm_burglary_stats', [AlarmDashboardController::class, 'alarmEventBurglaryStatistics']);
Route::get('security_device_alarm_medical_stats', [AlarmDashboardController::class, 'alarmEventMedicalStatistics']);
Route::get('security_device_alarm_water_stats', [AlarmDashboardController::class, 'alarmEventWaterStatistics']);
Route::get('security_device_alarm_temperature_stats', [AlarmDashboardController::class, 'alarmEventTemperatureStatistics']);
Route::get('security_device_alarm_fire_stats', [AlarmDashboardController::class, 'alarmEventFireStatistics']);
Route::get('buildingtype_customer_stats', [AlarmDashboardController::class, 'buildingtypeCustomerStats']);
Route::get('customers_accounts_expire_stats', [AlarmDashboardController::class, 'customersAccountsExpireStats']);


Route::post('security-documents', [SecurityLoginController::class, "documentStore"]);
Route::get('security-documents/{id}', [SecurityLoginController::class, "documentShow"]);
Route::post('security-documents/{id}', [SecurityLoginController::class, "documentUpdate"]);
Route::get('security-documents', [SecurityLoginController::class, "documentList"]);
Route::delete('security-documents/{id}', [SecurityLoginController::class, "documentDestroy"]);

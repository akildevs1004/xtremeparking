<?php


use App\Http\Controllers;
use App\Http\Controllers\AlarmLogsController;
use App\Http\Controllers\AlarmSensorTypesController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementsCategoriesController;

use App\Http\Controllers\Customers\Alarm\AlarmNotificationController;
use App\Http\Controllers\Customers\Alarm\DeviceArmedLogsController;
use App\Http\Controllers\Customers\AlarmDashboard;
use App\Http\Controllers\Customers\AlarmDashboardController;
use App\Http\Controllers\Customers\AlarmDeviceTemperatureLogsController;
use App\Http\Controllers\Customers\Api\ApiAlarmDeviceSensorLogsController;
use App\Http\Controllers\Customers\Api\ApiAlarmDeviceTemperatureLogsController;
use App\Http\Controllers\Customers\CustomerAlarmEventsController;
use App\Http\Controllers\Customers\CustomerBuildingPhotosController;
use App\Http\Controllers\Customers\CustomerBuildingPicturesController;
use App\Http\Controllers\Customers\CustomerCamerasController;
use App\Http\Controllers\Customers\CustomerContactsController;
use App\Http\Controllers\Customers\CustomerPaymentsController;
use App\Http\Controllers\Customers\CustomersController;
use App\Http\Controllers\Customers\Reports\AlarmReportsController;
use App\Http\Controllers\Customers\SecurityLoginController;
use App\Http\Controllers\Customers\TechnicianLoginsController;
use App\Http\Controllers\Customers\TicketsController;
use App\Http\Controllers\DeviceNotificationsManagersController;
use App\Http\Controllers\DeviceProductServicesController;
use App\Http\Controllers\DeviceZonesController;
use App\Http\Controllers\DeviceZoneTypesController;
use App\Http\Controllers\PlottingController;
use App\Http\Controllers\RolePermissionsController;
use App\Http\Controllers\SalesBusinessSourceTypesController;
use App\Http\Controllers\SalesInquiryController;
use App\Http\Controllers\SalesQuotationFallowupsController;
use App\Http\Controllers\SalesQuotationsController;
use App\Models\AlarmLogs;
use App\Models\Customers\Customers;
use App\Models\Customers\SecurityLogin;
use App\Models\DeviceArmedLogs;
use App\Models\DeviceProductServices;
use App\Models\MapKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Ramsey\Uuid\Type\Integer;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

Route::apiResource('customers', CustomersController::class);
Route::get('customerinfo', [CustomersController::class, "customerinfo"]);
Route::get('customers_for_map', [CustomersController::class, "customersForMap"]);


Route::get('alarm_customers__for_map', [CustomersController::class, "alarmCustomersForMap"]);

Route::get('customers-all', [CustomersController::class, "customersAll"]);

//customer

Route::post('customers_primary_contact_update', [CustomersController::class, 'updatePrimaryContacts']);
Route::post('customers_component_contact_update', [CustomersController::class, 'updateComponentContactsUpdate']);
Route::delete('delete_customer_contact', [CustomersController::class, 'deleteCustomerContact']);



Route::post('customers_secondary_contact_update', [CustomersController::class, 'updateSecondaryContacts']);
Route::post('customers_building_contact_update', [CustomersController::class, 'updatebuildingContacts']);
Route::post('customer-update', [CustomersController::class, 'updateCustomer']);
Route::post('customers_contact_update', [CustomersController::class, 'updateCustomerContact']);
Route::apiResource('customers_building_picture', CustomerBuildingPicturesController::class);
Route::apiResource('customers_building_photos', CustomerBuildingPhotosController::class);
Route::apiResource('customers_building_cameras', CustomerCamerasController::class);


Route::apiResource('customer_contact', CustomerContactsController::class);
Route::post('update_customer_settings', [CustomersController::class, 'updateCustomerSettings']);
Route::get('customer_device_types', [CustomersController::class, 'customerDeviceTypes']);
Route::get('customer_device_sensor_names', [CustomersController::class, 'customerDeviceSensorNames']);

Route::get('customers_list', [CustomersController::class, 'getCustomersList']);
Route::post('reset_customer_alarm_pin', [CustomersController::class, 'resetCustomerPin']);
Route::post("security_customers_update", [CustomersController::class, "SecurityCustomersListUpdate"]);
Route::post("security_customers_single_update", [CustomersController::class, "SecurityCustomersSingleUpdate"]);

Route::post("delete_customer", [CustomersController::class, "deleteCustomer"]);
Route::get("customer_devices_sensors_zones", [CustomersController::class, "getCustomerDevicesSensorZones"]);


// Route::get('get_master_device_serial_numbers', [CustomersController::class, 'getMasterDeviceSerialNumbers']);
Route::get('get_customer_new_serial_numbers', [CustomersController::class, 'getCustomerNewSerialNumbers']);


//Devices
Route::get('building_types', [CustomersController::class, 'buildingTypes']);
Route::get('alarm_types', [CustomersController::class, 'alarmTypes']);

Route::get('address_types', [CustomersController::class, 'addressTypes']);


Route::get('device_models', [CustomersController::class, 'deviceModels']);
Route::get('device_types', [CustomersController::class, 'deviceTypes']);
Route::post('device_zones_update', [CustomersController::class, 'updateDeviceZones']);
Route::get('customer_temperature_devices', [CustomersController::class, 'getCustomerTemperatureDevices']);

Route::get('sensor_types', [CustomersController::class, 'getSensorsList']);

Route::get('get_device_zones', [CustomersController::class, 'getDeviceZonesList']);
Route::post('update_device_zone', [CustomersController::class, 'updateDeviceZoneIndividual']);
Route::post('create_device_zone', [CustomersController::class, 'createDeviceZoneIndividual']);
Route::delete('delete_device_zone', [CustomersController::class, 'deleteDeviceZoneIndividual']);

Route::apiResource('alarm_sensor_types',  AlarmSensorTypesController::class);
Route::get('device_zone_types_dropdown',  [DeviceZoneTypesController::class, "zonetypesList"]);
Route::get('device_sensor_types_dropdown',  [DeviceZoneTypesController::class, "sensorTypesList"]);




Route::apiResource('device_zone_types', DeviceZoneTypesController::class);


Route::post('update_zone_type', [DeviceZoneTypesController::class, 'update']);
Route::post('create_zone_type', [DeviceZoneTypesController::class, 'create']);
Route::delete('delete_zone_type', [DeviceZoneTypesController::class, 'destroy']);

Route::post('update_sensor_type', [AlarmSensorTypesController::class, 'update']);
Route::post('create_sensor_type', [AlarmSensorTypesController::class, 'create']);
Route::delete('delete_sensor_type', [AlarmSensorTypesController::class, 'destroy']);






//api alarm logs
Route::get('api_temperature_logs',  [ApiAlarmDeviceTemperatureLogsController::class, 'ApiTemperatureLogs']);
Route::post('api_temperature_logs', [ApiAlarmDeviceTemperatureLogsController::class, 'ApiTemperatureLogs']);
Route::get('api_alarm_logs',  [ApiAlarmDeviceTemperatureLogsController::class, 'AlarmLogs']);
Route::post('api_alarm_logs', [ApiAlarmDeviceTemperatureLogsController::class, 'AlarmLogs']);
Route::get('update_logs_duration', [ApiAlarmDeviceTemperatureLogsController::class, 'updateAlarmResponseTime']);
Route::get('read_csv_file', [ApiAlarmDeviceSensorLogsController::class, 'readCSVLogFile']);

//alarm logs
Route::apiResource('alarmevents', CustomerAlarmEventsController::class);

Route::get('alarm_dashboard_get_temparature_latest', [AlarmDeviceTemperatureLogsController::class, 'getDeviceLatestTemperature']);

Route::get('alarm_dashboard_get_hourly_data', [AlarmDeviceTemperatureLogsController::class, 'getDeviceTodayHourlyTemperature']);
Route::get('alarm_logs_data_month_data', [AlarmDeviceTemperatureLogsController::class, 'getAlarmLogsMonthwise']);

Route::get('get_alarm_logs', [CustomerAlarmEventsController::class, 'getAlarmLogs']);

Route::get('get_alarm_events', [CustomerAlarmEventsController::class, 'getAlarmEvents']);
Route::get('get_alarm_events_technician', [CustomerAlarmEventsController::class, 'getAlarmEventsTechnician']);

Route::get('technicians_list', [TechnicianLoginsController::class, 'getTechniciansList']);




Route::get('get_alarm_events_customer_group', [CustomerAlarmEventsController::class, 'getAlarmEventsCustomersGroup']);

Route::get('alarm_event_by_id', [CustomerAlarmEventsController::class, 'getAlarmEventById']);



Route::get('get_alarm_notification_display', [CustomerAlarmEventsController::class, 'getAlarmNotificationsList']);
Route::get('get_alarm_notification_display_technician', [CustomerAlarmEventsController::class, 'getAlarmNotificationsListTechnician']);




Route::get('get_alarm_events_notes', [CustomerAlarmEventsController::class, 'getAlarmEventsNotes']);

Route::get('security_alarm_notes_print_pdf', [CustomerAlarmEventsController::class, 'securityAlarmNotesPrintPdf']);
Route::get('security_alarm_notes_export_excel', [CustomerAlarmEventsController::class, 'securityAlarmNotesExportExcel']);
Route::get('security_alarm_notes_download_pdf', [CustomerAlarmEventsController::class, 'securityAlarmNotesDownloadPdf']);

Route::post('customer_add_event_notes', [CustomerAlarmEventsController::class, "createEventNotes"]);
Route::delete('delete-notes', [CustomerAlarmEventsController::class, "destroyNotes"]);
Route::delete('delete-event', [CustomerAlarmEventsController::class, "destroyEvent"]);
Route::post('update-device-alarm-event-status-off', [CustomerAlarmEventsController::class, "stopEvent"]);


Route::apiResource('automation', DeviceNotificationsManagersController::class);
Route::delete('delete-automation', [DeviceNotificationsManagersController::class, "destroy"]);

Route::apiResource('customer_payments', CustomerPaymentsController::class);
Route::delete('delete-payment', [CustomerPaymentsController::class, "destroy"]);
Route::post('customer_product_invoice_submition',  [CustomerPaymentsController::class, 'CustomerProductInvoiceSubmition']);
Route::get('get_customer_sensor_payment_package_details',  [CustomerPaymentsController::class, 'GetCustomerSensorsPaymentPackage']);



Route::get('plotting', [PlottingController::class, "index"]);
Route::post('plotting', [PlottingController::class, "store"]);
Route::post('reset_plotting', [PlottingController::class, "resetPlotting"]);
Route::post('reset_plotting_all', [PlottingController::class, "resetPlottingAll"]);
Route::get('plotting_with_customer_id', [PlottingController::class, "plottingWithCustomerId"]);












//security

Route::apiResource('security', SecurityLoginController::class);
Route::get("security-dropdownlist", [SecurityLoginController::class, "securityDropdownlist"]);





Route::get('get-map-key', function () {
    return MapKey::where("isActive", 1)->value("key");
});

//download report
Route::get('/alarm_events_print_pdf', [AlarmReportsController::class, 'alarmEventsPrintPdf']);
Route::get('/alarm_events_download_pdf', [AlarmReportsController::class, 'alarmEventsDownloadPdf']);
Route::get('/alarm_events_export_excel', [AlarmReportsController::class, 'alarmEventsExportCsv']);

Route::get('/device_armed_logs_print_pdf', [AlarmReportsController::class, 'deviceArmedLogsPrintPdf']);
Route::get('/device_armed_logs_download_pdf', [AlarmReportsController::class, 'deviceArmedLogsDownloadPdf']);
Route::get('/device_armed_logs_export_excel', [AlarmReportsController::class, 'deviceArmedLogsExportExcel']);


Route::get('/alarm_events_customer_group_print_pdf', [AlarmReportsController::class, 'alarmEventsCustomersGroupPrintPdf']);
Route::get('/alarm_events_customer_group_download_pdf', [AlarmReportsController::class, 'alarmEventsCustomersGroupDownloadPdf']);
Route::get('/alarm_events_customer_group_export_excel', [AlarmReportsController::class, 'alarmEventsCustomersGroupDownloadCSV']);


Route::get('/alarm_events_customer_group_list_individual_print_pdf', [AlarmReportsController::class, 'alarmEventsCustomersGroupListIndividualPrintPdf']);
Route::get('/alarm_events_customer_group_list_individual_download_pdf', [AlarmReportsController::class, 'alarmEventsCustomersGroupListIndividualDownloadPdf']);
Route::get('/alarm_events_customer_group_export_excel', [AlarmReportsController::class, 'alarmEventsCustomersGroupDownloadCSV']);

Route::get('/alarm_events_customer_group_list_individual_notes_print_pdf', [AlarmReportsController::class, 'alarmEventsCustomersGroupListIndividualNotesPrintPdf']);
Route::get('/alarm_events_customer_group_list_individual_notes_download_pdf', [AlarmReportsController::class, 'alarmEventsCustomersGroupListIndividualNotesDownloadPdf']);
Route::get('/alarm_events_customer_group_export_excel', [AlarmReportsController::class, 'alarmEventsCustomersGroupDownloadCSV']);




Route::get('/sample_pdf_print', [AlarmReportsController::class, 'sample_pdf_pagenumbers']);


//notifications
// alarm_notification_icons
// device_sensor_types

Route::post("alarm_forward_notification", [AlarmNotificationController::class, "sendAlarmForwardNotification"]);
Route::get("alarm_notification_icons", [AlarmNotificationController::class, "getAlarmNotificationIcons"]);
// Route::get("get_google_icons", [AlarmNotificationController::class, "getGoogleIcons"]);


//operator
Route::get('get_operator_alarm_events', [CustomerAlarmEventsController::class, 'getAlarmEventsMapOperator']);
Route::get('customers_for_map_operator', [CustomersController::class, "customersForMapOperator"]);
Route::get('events_for_map_operator', [CustomersController::class, "eventsForMapOperator"]);
Route::get('alarm_event_operator_statistics', [AlarmDashboardController::class, "alarmEventOperatorStatistics"]);

Route::post('operator_live_update', [SecurityLoginController::class, "updateLiveStatus"]);
Route::get('operators_live_status', [SecurityLoginController::class, "getOperatorLiveStatus"]);



//reprots
Route::get('device_armed_reports', [DeviceArmedLogsController::class, "report"]);

//pdf
Route::get('device_armed_report_export_excel', [AlarmReportsController::class, 'deviceArmedReportsDownloadExcel']);
Route::get('device_armed_report_print_pdf', [AlarmReportsController::class, 'deviceArmedReportsPrintPdf']);
Route::get('device_armed_report_download_pdf', [AlarmReportsController::class, 'deviceArmedReportsDownload']);


Route::get('technician_test_results_print_pdf', [AlarmReportsController::class, 'technicianTestResultsPrintPdf']);
Route::get('technician_test_results_export_excel', [AlarmReportsController::class, 'technicianTestResultsExportExcel']);
Route::get('technician_test_results_download_pdf', [AlarmReportsController::class, 'technicianTestResultsDownloadPdf']);



Route::get('alarm_notes_print_pdf', [AlarmReportsController::class, 'alarmEventsNotesPrintPdf']);
Route::get('alarm_notes_download_pdf', [AlarmReportsController::class, 'alarmEventsNotesDownload']);

Route::get('invoice_print_pdf', [AlarmReportsController::class, 'invoicePrintPdf']);
Route::get('quotation_print_pdf', [AlarmReportsController::class, 'quotationPrintPdf']);



Route::get('alarm_notes_summary_print_pdf', [AlarmReportsController::class, 'alarmEventsNotesSummaryPrintPdf']);
Route::get('alarm_notes_summary_download_pdf', [AlarmReportsController::class, 'alarmEventsNotesSummaryDownload']);

//settings permissions
Route::post('role_update_permission_pages', [RolePermissionsController::class, 'roleUpdatePermissions']);
Route::get('role_get_permission_pages', [RolePermissionsController::class, 'getRolePermissions']);
Route::get('get_page_roles_menu_data', [RolePermissionsController::class, 'getPageRolesMenuList']);
Route::post('create_default_roles', [RolePermissionsController::class, 'createDefaultRoles']);


Route::apiResource('device_product_services',  DeviceProductServicesController::class);
Route::get('device_product_services_group',  [DeviceProductServicesController::class, 'DeviceProductServicesGroup']);



//sales
Route::get('get_sales_business_types',  [SalesInquiryController::class, 'GetBusinessSourceTypes']);
Route::apiResource('sales_inquiry',   SalesInquiryController::class);
Route::apiResource('sales_quotations',   SalesQuotationsController::class);
Route::apiResource('quotation_fallowup',   SalesQuotationFallowupsController::class);
Route::post('customer_invoice_reminder_mail',   [CustomerPaymentsController::class, "CustomerInvoiceReminderMail"]);
Route::post('customer_quotation_reminder_mail',   [SalesQuotationsController::class, "CustomerQuotationReminderMail"]);











//tickets
Route::get('admin_tickets_statistics',  [TicketsController::class, 'adminTicketsStatistics']);
Route::post('ticket_update_technician',  [TicketsController::class, 'updateTicketTechnician']);







Route::get("qrcodeevent", function (Request $request) {

    if ($request->filled("content")) {
        $event_id =  (int)$request->content * 9999;
        $content = env("WEBSITE_LINK") . '/qrcodeevent?eventid=' . $event_id;

        $qrCode = QrCode::size(300)->generate($content);

        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }
});

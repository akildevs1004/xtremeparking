<?php


use App\Http\Controllers\Alarm\DeviceSensorLogsController;
use App\Http\Controllers\AlarmLogsController;
use App\Http\Controllers\AlramEventsController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\Customers\Alarm\AlarmNotificationController;
use App\Http\Controllers\Customers\Api\ApiAlarmDeviceSensorLogsController;
use App\Http\Controllers\Customers\Api\ApiAlarmDeviceTemperatureLogsController;
use App\Http\Controllers\Customers\CustomerPaymentsController;
use App\Http\Controllers\Customers\CustomersController;
use App\Http\Controllers\DeviceCameraController;
use App\Http\Controllers\DeviceCameraModel2Controller;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\KeyGeneratorController;
use App\Http\Controllers\Parking\ImageProcessingController;
use App\Http\Controllers\ParkingDeviceController;
use App\Http\Controllers\SDKController;
use App\Http\Controllers\Shift\AutoShiftController;
use App\Http\Controllers\Shift\FiloShiftController;
use App\Http\Controllers\Shift\MultiInOutShiftController;
use App\Http\Controllers\Shift\NightShiftController;
use App\Http\Controllers\Shift\RenderController;
use App\Http\Controllers\Shift\SingleShiftController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\WhatsappController;
use App\Imports\excelEmployeesData;
use App\Mail\EmailContentDefault;
use App\Mail\ReportNotificationMail;
use App\Mail\TestMail;
use App\Models\AlarmEvents;
use App\Models\AlarmEventsTechnician;
use App\Models\AlarmLogs;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Customers\AlarmSensorTypes;
use App\Models\Customers\CustomerPayments;
use App\Models\Device;
use App\Models\DeviceActivesettings;
use App\Models\DeviceSensorTypes;
use App\Models\Employee;
use App\Models\ReportNotification;
use App\Models\SecurityCustomers;
use App\Models\Shift;
use App\Services\MqttService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log as Logger;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Document\Security;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use SimpleSoftwareIO\QrCode\QrCodeServiceProvider;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Http\Controllers\Parking\CameraLogListenerController;
// Route::redirect('/', 'api/test');
use Carbon\Carbon;

Route::get("testingcamera1", function (Request $request) {

 

 $dt=Carbon::createFromFormat('YmdHisv', "20260225132321000" , 'UTC'); // set TZ if needed
 return $formattedLogTime = $dt->format('Y-m-d H:i:s');        // "2025-09-10 12:53:02"


// Logger::channel('custom')->info(date("Y-m-d H:i:s"));
//     Logger::channel('custom')->info(json_encode($request->all()));



$jsonPayload = '{
    "timestamp":"20260214162016000",
    "filename":"I-75945_1771071025.jpg",
    "vehicle_id":"I-75945",
    "event_category":"TrafficJunction",
    "event_type":"TrafficJunction",
    "camera_code":"BH09F2DPAJEECF9",
    "direction":"Head",
    "lane":"1",
    "tag":"I-75945",
    "company_id":8,
    "fields":null
}';

// Decode JSON to array
$data = json_decode($jsonPayload, true);

// Create a Request object
$request = new Request($data);

// Now you can access fields like normal
$timestamp      = $request->input('timestamp');
$filename       = $request->input('filename');
$vehicleId      = $request->input('vehicle_id');
$eventCategory  = $request->input('event_category');
$eventType      = $request->input('event_type');
$cameraCode     = $request->input('camera_code');
$direction      = $request->input('direction');
$lane           = $request->input('lane');
$tag            = $request->input('tag');
$companyId      = $request->input('company_id');
$fields         = $request->input('fields');



 $DeviceController =  new CameraLogListenerController();
             return    $response=    $DeviceController->CameraLogProcessing($request); 




    return $request->all();
    
});

// Route::get("cgi-bin/NotifyEvent", function (Request $request) {

// Logger::channel('custom')->info(date("Y-m-d H:i:s"));
//     Logger::channel('custom')->info(json_encode($request->all()));


//     return $request->all();
    
// });


Route::get('test', function (Request $request) {
 Logger::channel('custom')->info("Test");
    Logger::channel('custom')->info(date("Y-m-d H:i:s"));
    Logger::channel('custom')->info(json_encode($request->all()));


    return $request->all();
});

Route::get("openexitgate", function (Request $request) {

    $postData = [
        "action" => "UPDATE_CONFIG",
        "serialNumber" => 'XTP100001',
        "config" => ["relay0" => true],
    ];
    if ($postData["serialNumber"]) {
        // Publish  to MQTT
        $mqtt = new MqttService();
        $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/{$postData['serialNumber']}/config/request",  json_encode($postData), $postData["serialNumber"]);
    }


    $postData = [
        "action" => "UPDATE_CONFIG",
        "serialNumber" => 'XTP100001',
        "config" => ["relay0" => false],
    ];
    if ($postData["serialNumber"]) {
        // Publish  to MQTT
        $mqtt = new MqttService();
        $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/{$postData['serialNumber']}/config/request",  json_encode($postData), $postData["serialNumber"]);
    }

   
});



Route::get("openentrygate", function (Request $request) {

    $postData = [
        "action" => "UPDATE_CONFIG",
        "serialNumber" => 'XTP100001',
        "config" => ["relay1" => true],
    ];
    if ($postData["serialNumber"]) {
        // Publish  to MQTT
        $mqtt = new MqttService();
        $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/{$postData['serialNumber']}/config/request",  json_encode($postData), $postData["serialNumber"]);
    }


    $postData = [
        "action" => "UPDATE_CONFIG",
        "serialNumber" => 'XTP100001',
        "config" => ["relay1" => false],
    ];
    if ($postData["serialNumber"]) {
        // Publish  to MQTT
        $mqtt = new MqttService();
        $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/{$postData['serialNumber']}/config/request",  json_encode($postData), $postData["serialNumber"]);
    }

   
});

Route::get("closegate111", function (Request $request) {

    $postData = [
        "action" => "UPDATE_CONFIG",
        "serialNumber" => 'XTP100001',
        "config" => ["relay1" => false],
    ];
    if ($postData["serialNumber"]) {
        // Publish  to MQTT
        $mqtt = new MqttService();
        $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/{$postData['serialNumber']}/config/request",  json_encode($postData), $postData["serialNumber"]);
    }
});


Route::get("imageText", function (Request $request) {



    $imagePath = public_path('/parking_camera_logs/20250910125302986_17297_VEHICLE_DETECTION_EXIT_FORWARD_EMI_BACKGROUND.jpg');

    $rawText = (new TesseractOCR($imagePath))
        ->lang('eng') // English
        ->psm(6)      // Assume a uniform block of text
        ->oem(3)      // Default OCR Engine
        ->run();

    $rawText =  "Camera Info." . explode("Camera Info.", $rawText)[1];
    $rawText = str_replace("\n", "", $rawText);


    $result =  (new ImageProcessingController())->processOcrText($rawText);

    return response()->json($result); // gives raw, clean, one_line, and parsed fields

    // return response()->json([
    //     'extracted_text' => $text
    // ]);
});
Route::get("testOfflineDevices", function (Request $request) {



    (new AlramEventsController)->verifyOfflineDevices();

    //return (new ApiAlarmDeviceSensorLogsController())->closeOfflineAlarmsBySerialNumber('M014200892110002626');
});

Route::get("testnotification", function (Request $request) {
    (new AlramEventsController)->verifyOfflineDevices();

    //return (new ApiAlarmDeviceSensorLogsController())->closeOfflineAlarmsBySerialNumber('M014200892110002626');
});


Route::get("closealarm", function (Request $request) {
    if ($request->filled('serial_number')) {
        (new ApiAlarmDeviceSensorLogsController())->endAllAlarmsBySerialNumber($request->serial_number, date("Y-m-d H:i:s"));

        $device = Device::where("serial_number", $request->serial_number)->first();
        (new ApiAlarmDeviceTemperatureLogsController)->createAlarmEventsJsonFile($device->company_id);
    }
});

Route::get("updatearmedCompanyIds", function (Request $request) {



    (new ApiAlarmDeviceSensorLogsController())->updateArmedTableCompanyLogs();

    (new ApiAlarmDeviceSensorLogsController())->updateDisarmTableCompanyLogs();
});


// Route::get("closealarm", function (Request $request) {


//     return (new ApiAlarmDeviceSensorLogsController())->endAllAlarmsBySerialNumber($request->serial_number, date("Y-m-d H:i:s"));
// });
Route::get("testWhatsappNotification", function (Request $request) {


    return (new WhatsappController())->sendWhatsappNotification(["id" => 8], "Test Message", "971552205149", []);
});

Route::get("testNotification", function (Request $request) {


    // $response = (new WhatsappController())->sendWhatsappNotification($value['company'], $body_content1, $value['whatsapp_number'], []);





    // $logsArray = AlarmLogs::with(["company", "device.company", "devicesensorzones"])->where("serial_number", "W12345")
    //     ->where("company_id", '>', 0)
    //     ->where("alarm_status", 1)
    //     //->where("event_code", "!=", null)
    //     ->where("verified", false)
    //     ->where("time_duration_seconds", '>=', 5)

    //     ->orderBy("log_time", "ASC")->get();

    $logsArray = AlarmLogs::with(["company", "device.company", "devicesensorzones"])->where("id", 10437)->get();

    return (new ApiAlarmDeviceTemperatureLogsController)->SendMailWhatsappNotification("intruder", "intruder", "",  $logsArray[0]["device"], date("Y-m-d"),  1,   [],   [], $logsArray[0]);



    return (new ApiAlarmDeviceTemperatureLogsController)->updateAlarmResponseTime();
});
Route::get("alarm_device_status", function (Request $request) {


    return "AlarmTesting";
});
Route::post("alarm_device_status", function (Request $request) {


    return "AlarmTesting";
});

Route::get("create_test_sos_alarm", function (Request $request) {


    //return (new ApiAlarmDeviceTemperatureLogsController)->createAlarmEventsJsonFile(8);;

    $date = date("d-m-Y");
    $csvPath = "alarm-sensors/sensor-logs-$date.csv";

    $area = '';
    $zone = '';
    if ($request->filled("area"))
        $area  =   $request->area;

    if ($request->filled("zone"))
        $zone  =   $request->zone;

    $content = $request->serial_number . ",9999," . date('Y-m-d H:i:s') . ",R0L0," . $area . "," . $zone . PHP_EOL;;

    exec("chmod 775 " . escapeshellarg(Storage::path($csvPath)));

    Storage::append($csvPath,  $content);

    return (new ApiAlarmDeviceSensorLogsController)->readCSVLogFile();
});

Route::get("create_test_tampred_alarm", function (Request $request) {


    //return (new ApiAlarmDeviceTemperatureLogsController)->createAlarmEventsJsonFile(8);;

    $date = date("d-m-Y");
    $csvPath = "alarm-sensors/sensor-logs-$date.csv";

    $area = '';
    $zone = '';
    if ($request->filled("area"))
        $area  =   $request->area;

    if ($request->filled("area"))
        $zone  =   $request->zone;

    $content = $request->serial_number . ",1137," . date('Y-m-d H:i:s') . ",R0L0," . $area . "," . $zone;



    Storage::append($csvPath,  $content);

    return $result = (new ApiAlarmDeviceSensorLogsController)->readCSVLogFile();
});

Route::get("test_sendMail", function (Request $request) {


    $name = "Testing";

    $alarm = AlarmEvents::whereId("1735")->first();


    $email = "venuakil2@gmail.com";
    $alarm_id = 181;
    $external_cc_email = "venuakil2@gmail.com";

    return (new AlarmNotificationController())->sendMail($name, $alarm, $email, $alarm_id, $external_cc_email);
});


Route::get("create_test_alarm_technician", function (Request $request) {



    $data = [
        "company_id" => 8,
        "serial_number" => $request->serial_number,
        "alarm_start_datetime" => date("Y-m-d H:i:s"),
        "alarm_status" => 1,
        "customer_id" => 6,
        "zone" => $request->zone ?? null,
        "area" =>  $request->area ?? null,
        "alarm_type" => $request->zone ? $request->zone : "SOS",
        "alarm_category" => 1,
        "sensor_zone_id" => null,
        "alarm_source" => 78,
        "security_name" => "Test",
        "security_id" => 1,
        "technician_id" => 1,
        "ticket_id" => 184,
    ];


    $offlineDevices[] = $data;


    //AlarmEvents::create($data);
    AlarmEventsTechnician::create($data);
    return (new ApiAlarmDeviceTemperatureLogsController)->createAlarmEventsJsonFile(8);;

    return false;
});

Route::get("update_json_file", function (Request $request) {


    (new ApiAlarmDeviceTemperatureLogsController)->createAlarmEventsJsonFile(8);;
});

Route::get("create_test_alar11111111111", function (Request $request) {


    return [DeviceSensorTypes::pluck("alarm_type", "name"), [
        'Water Leakage Sensor' => 'Water',
        'Fire Sensor' => 'Fire',
        'Medical Sensor' => 'Medical',
        'SOS Sensor' => 'SOS',
        'Temperature Sensor' => 'Temperature',
        'Gas Sensor' => 'Gas',

    ]];

    // $data = [
    //     "company_id" => 8,
    //     "serial_number" => $request->serial_number,
    //     "alarm_start_datetime" => date("Y-m-d H:i:s"),
    //     "alarm_status" => 1,
    //     "customer_id" => $request->customer_id,
    //     "zone" => $request->zone ?? null,
    //     "area" =>  $request->area ?? null,
    //     "alarm_type" => $request->zone ? $request->zone : "SOS",
    //     "alarm_category" => 1,
    //     "sensor_zone_id" => null,
    //     "alarm_source" => 78,
    //     "security_name" => "Test",
    //     "security_id" => 1,
    // ];
    // $offlineDevices[] = $data;
    // AlarmEvents::create($data);

    // $records  = [
    //     "serial_number" => $request->serial_number,
    //     "log_time" => date("Y-m-d H:i:s"),
    //     "alarm_status" => 1,
    //     "alarm_type" => $alarm_type,
    //     "area" => $area,
    //     "zone" => $zone,
    //     "alarm_source" => $alarm_source,
    //     "event_code" => $event,
    // ];

    // $insertedRecord = AlarmLogs::create($records);

    (new ApiAlarmDeviceTemperatureLogsController)->createAlarmEventsJsonFile(8);;

    return (new ApiAlarmDeviceTemperatureLogsController)->updateAlarmResponseTime();


    return false;



    // $date = date("d-m-Y");
    // $csvPath = "alarm-sensors/sensor-logs-$date.csv";

    // $area = '';
    // $zone = '';
    // if ($request->filled("area"))
    //     $area  =   $request->area;

    // if ($request->filled("area"))
    //     $zone  =   $request->zone;

    // $content = $request->serial_number . ",1137," . date('Y-m-d H:i:s') . ",R0L0," . $area . "," . $zone;



    // Storage::append($csvPath,  $content);

    // return $result = (new ApiAlarmDeviceSensorLogsController)->readCSVLogFile();
});

Route::get("readCSVLogFile", function (Request $request) {

    return (new ApiAlarmDeviceSensorLogsController)->readCSVLogFile();;

    return (new ApiAlarmDeviceTemperatureLogsController)->updateAlarmResponseTime();

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://47.88.11.117:8807/all/queryTransCmdJson?id=fsuiop00&access_token=c7499d5a-3167-472b-8d30-ca3d75a9bd89',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
	"Cmd":7101,
	"Id":"fsuiop00",
	"User":12345678,
	"Def":"JSON_CMD_GET_VERSION"
}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
});





Route::get("test900device", function (Request $request) {




    $device = Device::where("device_id", "M014200892110001835")->first();
    return  $responseData['data'] = (new DeviceCameraModel2Controller($device->camera_sdk_url))->TestLive($device);





    $device = Device::where("device_id", "M014200892110001835")->first();
    return  $responseData['data'] = (new DeviceCameraModel2Controller($device->camera_sdk_url))->getSettings($device);
});
Route::get("test900device2", function (Request $request) {

    $device = Device::where("device_id", "M014200892110002761")->first();
    return  $responseData['data'] = (new DeviceCameraModel2Controller($device->camera_sdk_url))->getSettings($device);
});

Route::get("testqrcode", function (Request $request) {
    $qrCode = QrCode::size(300)->generate('Hello, Laravel 11!');

    return response($qrCode)->header('Content-Type', 'image/svg+xml');




    return  QrCode::format('png')->size(300)->generate('https://example.com', public_path('qr-codes/qr_code.png'));

    QrCode::size(500)
        ->format('png')
        ->generate('www.google.com', storage_path('app/public/111.png'));
    exit;
});
Route::get("whatsappqrcode", function (Request $request) {

    $device = Device::where("device_id", "M014200892110002761")->first();
    return  $responseData['data'] = (new DeviceCameraModel2Controller($device->camera_sdk_url))->getSettings($device);

    // phpinfo();
    // exit;

    QrCode::size(500)
        ->format('png')
        ->generate('www.google.com', storage_path('app/public/111.png'));
    exit;

    // exit;
    // QrCode::size(250)->generate('www.google.com');
    $url = 'https://example.com';
    // $qrCode = QrCodeServiceProvider::format('png')->size(300)->generate($url);


    $fileName = 'qrcode.png';
    $filePath = storage_path('app/public/' . $fileName);

    // Save the QR code to a file
    // Storage::put('public/' . $fileName, $qrCode);

    echo  QrCode::size(250)->generate('www.google.com', $filePath);
    exit;
    //send email notification and whatsapp notification
    $attachments = [];
    $attachments["media_url"] =  env('BASE_URL') . 'app/public/' . $fileName;
    //$attachments["media_url"] =  "https://backend.mytime2cloud.com/api/donwload_storage_file?file_name=app%2Fpdf%2F2%2Fdaily_missing.pdf";

    $attachments["filename"] = $fileName;


    $company = Company::where('company_id', 2);
    (new WhatsappController())->sendWhatsappNotification($company, $fileName, "971552205149", $attachments);
});
Route::get("compare2images", function (Request $request) {
    // $image1 = storage_path('venu2.jpeg');;
    // $image2 = storage_path('arav1.jpg');;

    // // $imageComparator = new ImageComparator();

    // $similarity = $imageComparator->compare($image1, $image2);
    // return  $similarity;
});
Route::get("test111password", function (Request $request) {
    ///////return (new AttendanceLogController)->storemissing();

    //return Hash::make("AkiL@2211");
});
Route::get("pdf-merge-test", [TestController::class, "pdfMergeTest"]);

Route::post("encrypt-devices", [DeviceController::class, "encrypt"]);
Route::post("decrypt-devices", [DeviceController::class, "decrypt"]);





// Route::get('/test/getLogs', function (Request $request) {


//     return (new DeviceSensorLogsController)->updateCompanyIds();
//     $curl = curl_init();



//     $device_id = $request->device_id;

//     curl_setopt_array($curl, array(
//         CURLOPT_URL => "https://sdk.mytime2cloud.com/$device_id/GetRecord",
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_ENCODING => '',
//         CURLOPT_MAXREDIRS => 10,
//         CURLOPT_TIMEOUT => 0,
//         CURLOPT_FOLLOWLOCATION => true,
//         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//         CURLOPT_CUSTOMREQUEST => 'POST',
//     ));

//     $response = curl_exec($curl);

//     curl_close($curl);
//     return  json_decode($response, true);
// });
Route::get('/test/resetLogCount', function (Request $request) {
    $curl = curl_init();



    $device_id = $request->device_id;

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://sdk.mytime2cloud.com/$device_id/ResetRecord",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return  json_decode($response, true);
});
Route::get('/alarmtest', function (Request $request) {

    return (new AlarmLogsController())->store();
});
Route::get('/syncLogsScript', function (Request $request) {

    return time();

    // return [
    //     (new FiloShiftController)->render(),
    //     (new SingleShiftController)->render()
    // ];


    // return (new RenderController)->renderAuto($request);




    // return [
    //     "MultiInOut" => (new MultiInOutShiftController)->processByManual($request),
    //     // "Single" => (new SingleShiftController)->processByManual($request),
    //     // "Auto" => (new AutoShiftController)->processByManual($request)
    // ];
});
Route::get('/donwloadpdffile', function (Request $request) {


    return  $model = ReportNotification::with(["managers", "company.company_mail_content"])->where("id", 43)->first();

    // // Define the path to the file in the public folder
    // $filePath = Storage::url('app/payslips/8/8_3_8_2023_payslip.pdf');;;

    // $filePath = storage_path('app/payslips/8/8_3_8_2023_payslip.pdf');

    // // Check if the file exists
    // if (file_exists($filePath)) {
    //     // Create a response to download the file
    //     return response()->download($filePath, 'myfile.pdf');
    // } else {
    //     // Return a 404 Not Found response if the file doesn't exist
    //     return 'File not exist';
    //  }
});

Route::get('/testAttendanceRender111test', function (Request $request) {

    Logger::channel('custom')->info(json_encode($request->request->all()));
});

// Route::get('/testAttendanceRender111test', function (Request $request) {

//     Excel::import(new excelEmployeesData(), $request->file('file'));
// });

Route::post('/testAttendanceRender111test', function (Request $request) {

    Logger::channel('custom')->info(json_encode($request->request->all()));

    $base64Data = $request->request->all()['photo'];
    // Extract the image extension (e.g., 'jpeg', 'png') from the base64 data



    // Generate a unique filename
    $filename = 'image_' . time() .   '.jpg';

    // Decode the base64 data
    $decodedData = base64_decode($base64Data);

    // Save the decoded data to a file
    file_put_contents($filename, $decodedData);

    echo 'Image saved as ' . $filename;
    Logger::channel('custom')->info($filename);
});

Route::get('/testAttendanceRender111', function (Request $request) {




    $id = 13;
    $date = '2024-01-23';
    $requestArray = array(
        'date' => '',
        'UserID' => '',
        'updated_by' => 26,
        'company_ids' => array($id),
        'manual_entry' => true,
        'reason' => '',
        'employee_ids' => [],
        'dates' => array($date, $date),
        'shift_type_id' => 1,
        'auto_render' => false
    );

    //calling manual render method to pull all
    $renderRequest = Request::create('/render_logs', 'get', $requestArray);

    return ((new RenderController())->renderLogs($renderRequest));
});

Route::get("/testemployee", function (Request $request) {

    return Storage::url('8_3_8_2023_payslip.pdf');

    //$data = (new EmployeeController())->getSingleEmployeeProfileAll();


    //return  View('pdf.test', ["employees" => $data]);; //->donwload();
});
Route::get('/donwloadfile', function (Request $request) {
    // Define the path to the file in the public folder
    $filePath = Storage::url('app8_3_8_2023_payslip.pdf');; //public_path("1666190454.jpg");

    // Check if the file exists
    if (file_exists($filePath)) {
        // Create a response to download the file
        return response()->download($filePath, 'myfile.png');
    } else {
        // Return a 404 Not Found response if the file doesn't exist
        abort(404);
    }
});
Route::get('/handleNotification', function (Request $request) {

    $test = new DeviceController();
    return  $test->handleNotification(8);
});
Route::get('/test/test/3', function (Request $request) {



    // return  $response = Http::withoutVerifying()->get("https://ezwhat.com/api/send.php", [
    //     'number' => "919701226007",
    //     'type' => 'text',
    //     'message' => "Hello",
    //     'instance_id' => "650300B673EFA",
    //     'access_token' => "a27e1f9ca2347bb766f332b8863ebe9f",
    // ]);


    return defaultCards();

    Logger::channel('custom')->info('This is a custom log message.');

    return;

    $filePath = Storage::path("data.csv"); // replace with the path to your CSV file

    // Open the CSV file
    $file = fopen($filePath, 'r');

    // Read the CSV file and convert it to an array
    $data = [];
    $header = fgetcsv($file); // Get the header row
    while (($row = fgetcsv($file)) !== false) { // Loop through the remaining rows
        $data[] = array_combine($header, $row); // Combine the header row with the current row
        list($num, $msg) = $row;
        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
        ])->get("https://ezwhat.com/api/send.php?number={$num}&type=text&message={$msg}&instance_id=64466B01B7926&access_token=a27e1f9ca2347bb766f332b8863ebe9f");

        // check if the request was successful
        if ($response->ok()) {
            $request["status"] = true;
            $request["message"] = "success";
        } else {
            $request["status"] = false;
            $request["message"] = "false";
        }
    }

    // Close the CSV file
    fclose($file);

    return $data;

    // Use the $data array as needed
    foreach ($data as $row) {
        $num = $row['number'];
        $msg = $row['message'];
        // Process the data
    }

    return;

    // $Attendance = new AttendanceController;
    // return $result = $Attendance->syncLogsScript();

    die;

    // if($request->company_id) {
    //     $user_ids = Employee::where("company_id", "=",$request->company_id)->pluck("user_id");
    //     return User::whereIn("id",$user_ids)->update(["company_id" => $request->company_id]);
    // }

    echo phpversion();

    echo "<br>";

    $one = 1;
    $arr1 = [&$one, 2, 3];
    $arr2 = [0, ...$arr1];
    var_dump($arr2);

    die;

    $data = [
        "from" => "14157386102",
        "to" => "971502848071",
        "message_type" => "text",
        "text" => "This is a WhatsApp Message sent from the ideahrms",
        "channel" => "whatsapp",
    ];

    // return (new WhatsappController)->toSendNotification($data);
    // WhatsappJob::dispatch($data);
    return 'done';
    // $newLog[] = [
    //     "out" => "01:01",
    // ];

    // $attendance = Attendance::where('date', '2022-12-19')->where('employee_id', 681);
    // $found = $attendance->first();

    // $oldLog = $found->logs;

    // return [
    //     $oldLog, $newLog
    // ];

    // $result = array_merge($oldLog, $newLog);

    // $found->logs = $result;
    // return $found->save();

    // // return   $found ? $attendance->update($items) : Attendance::create($items);

    // return $request->user();
    // return $dd = Auth::user();
    // return "Awesome APIs";
});

Route::get('/open_door_old', function (Request $request) {

    $curl = curl_init();

    $device_id = $request->device_id;

    // $device_id = 'OX-8862021010076';

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://139.59.69.241:5000/$device_id/OpenDoor",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;

    // return "Awesome APIs";
});

Route::post('/upload-users', function (Request $request) {

    try {

        $url = "https://sdk.ideahrms.com/{$request->device_id}/AddPerson";

        $request["expiry"] = "2089-12-31 23:59:59";

        // make the POST request using Laravel's HTTP client

        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $request->all());

        // check if the request was successful
        if ($response->ok()) {
            $request["status"] = true;
            $request["message"] = $request->name . " " . "has been uploaded to " . $request->device_id;
        } else {
            $request["status"] = false;
            $request["message"] = $request->name . " " . "cannot upload to " . $request->device_id;
            // ...
        }
    } catch (\Throwable $th) {
        $request["status"] = false;
        $request["message"] = $request->name . " " . "cannot upload to " . $request->device_id;
    }

    if ($response["status"] == 102 || $response["status"] == 103) {
        $request["status"] = false;
        $request["message"] = "The device is not connected to the server or is not registered.";
    }

    return $request->all();
});

Route::get('/open_door_always_old', function (Request $request) {

    $curl = curl_init();

    $device_id = $request->device_id;

    // $device_id = 'OX-8862021010076';

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://139.59.69.241:5000/$device_id/HoldDoor",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;

    // return "Awesome APIs";
});

Route::get('/test_device_timeslots', function (Request $request) {
    //Schedule Device Access Control


    return  $result = (new SDKController)->handleCommand("FC-8300T20094123", "HoldDoor");;

    $date = date('Y-m-d');
    $devices =  DeviceActivesettings::with(["devices"])->where(function ($q) {
        $q->orWhere('date_from', ">=", date("Y-m-d"));
        $q->orWhere('date_to', "<=", date("Y-m-d"));
    })->get();

    $weekDays = [0 => "Mon", 1 => "Tue", 2 => "Wed", 3 => "Thu", 4 => "Fri", 5 => "Sat", 6 => "Sun"];

    foreach ($devices as $key => $device) {

        $openJson =  $device['open_json'];

        $openJsonArray = json_decode($openJson, true);

        foreach ($openJsonArray as  $key => $time) {

            if (isset($time[0])) {
                $key1 = $time[0];
                if (count($time) == 1) {
                    $key1 = 0;
                }


                if ($weekDays[$key1] == date("D")) {





                    // $schedule
                    //     ->command("task:AccessControlTimeSlots {$device->device_id} HoldDoor")
                    //     // ->everyThirtyMinutes()
                    //     ->everyMinute()
                    //     ->dailyAt($time)
                    //     ->withoutOverlapping()
                    //     ->appendOutputTo(storage_path("logs/$date-access-control-time-slot-logs.log"))
                    //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
                }
            }
        }
        //

        $closeJson =  $device['close_json'];

        $closeJsonArray = json_decode($closeJson, true);

        foreach ($closeJsonArray as  $key => $time) {
            if (isset($time[0])) {
                $key1 = $time[0];
                if (count($time) == 1) {
                    $key1 = 0;
                }

                if ($weekDays[$key1] == date("D")) {
                    // $schedule
                    //     ->command("task:AccessControlTimeSlots {$device->device_id} CloseDoor")
                    //     // ->everyThirtyMinutes()
                    //     ->everyMinute()
                    //     ->dailyAt($time)
                    //     ->withoutOverlapping()
                    //     ->appendOutputTo(storage_path("logs/$date-access-control-time-slot-logs.log"))
                    //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
                }
            }
        }
    }
    return;

    $jsonString = '{"2":"01:00"}';

    // Step 2: Decode the JSON string
    $decodedArray = json_decode($jsonString, true);
    // return $decodedArray[2];

    $date = date('Y-m-d');
    $devices =  DeviceActivesettings::where(function ($q) {
        $q->orWhere('date_from', ">=", date("Y-m-d"));
        $q->orWhere('date_to', "<=", date("Y-m-d"));
    })->get();

    $weekDays = [0 => "Mon", 1 => "Tue", 2 => "Wed", 3 => "Thu", 4 => "Fri", 5 => "Sat", 6 => "Sun"];

    foreach ($devices as $key => $device) {

        $openJson =  $device['open_json'];

        $openJsonArray = json_decode($openJson, true);

        foreach ($openJsonArray as  $key => $time) {

            if ($weekDays[$key] == date("D")) {





                // $schedule
                //     ->command("task:AccessControlTimeSlots {$device->device_id} HoldDoor")
                //     // ->everyThirtyMinutes()
                //     ->everyMinute()
                //     ->dailyAt($time)
                //     ->withoutOverlapping()
                //     ->appendOutputTo(storage_path("logs/$date-access-control-time-slot-logs.log"))
                //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            }
        }
        //

        $closeJson =  $device['close_json'];

        $closeJsonArray = json_decode($closeJson);

        foreach ($closeJsonArray as  $key => $time) {

            if ($weekDays[$key] == date("D")) {
                // $schedule
                //     ->command("task:AccessControlTimeSlots {$device->device_id} CloseDoor")
                //     // ->everyThirtyMinutes()
                //     ->everyMinute()
                //     ->dailyAt($time)
                //     ->withoutOverlapping()
                //     ->appendOutputTo(storage_path("logs/$date-access-control-time-slot-logs.log"))
                //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            }
        }
    }

    return $devices;
});


// $date = date("M-Y");

// $devices = AccessControlTimeSlot::get();


Route::get('/check_device_health_old', function (Request $request) {

    $devices = Device::pluck("device_id");

    $total_iterations = 0;
    $online_devices_count = 0;
    $offline_devices_count = 0;

    foreach ($devices as $device_id) {
        $curl = curl_init();

        curl_setopt_array($curl, array(

            // CURLOPT_URL => "https://sdk.ideahrms.com/CheckDeviceHealth/$device_id",
            // CURLOPT_URL => "http://139.59.69.241:5000/CheckDeviceHealth/$device_id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $status = json_decode($response)->status;

        if ($status !== 200) {
            $offline_devices_count++;
        } else {
            $online_devices_count++;
        }

        Device::where("device_id", $device_id)->update(["status_id" => $status == 200 ? 1 : 2]);

        $total_iterations++;
    }

    echo "$offline_devices_count Devices offline. $online_devices_count Devices online. $total_iterations records found.";
});


function checkSDKServerStatus($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return $httpCode;
}

Route::get('/checkSDKServerStatus/{url}', function ($url) {
    return checkSDKServerStatus($url) ? "Server is running" : "Server is down";
});

// Route::get('/close_door_old', function (Request $request) {

//     $curl = curl_init();

//     $device_id = $request->device_id;

//     // $device_id = 'OX-8862021010076';

//     curl_setopt_array($curl, array(
//         CURLOPT_URL => "http://139.59.69.241:5000/$device_id/CloseDoor",
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_ENCODING => '',
//         CURLOPT_MAXREDIRS => 10,
//         CURLOPT_TIMEOUT => 0,
//         CURLOPT_FOLLOWLOCATION => true,
//         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//         CURLOPT_CUSTOMREQUEST => 'POST',
//     ));

//     $response = curl_exec($curl);

//     curl_close($curl);
//     echo $response;

//     // return "Awesome APIs";
// });

// Route::post('/generate_log', [AttendanceLogController::class, 'GenerateLog']);
Route::get('/cameraevents', [CameraController::class, 'readLog']);
Route::post('/cameraevents', [CameraController::class, 'readLog']);

Route::get('/cameraevents-xml', [CameraController::class, 'readXml']);


Route::get('/generate_attendance_log', function (Request $request) {

    $arr = [];
    for ($i = 1; $i <= 5; $i++) {
        for ($j = 13; $j <= 13; $j++) {
            for ($k = 1; $k <= 1; $k++) {
                $time = rand(8, 20);
                $time = $time < 10 ? '0' . $time : $time;
                $arr[] = [
                    'UserID' => $i,
                    'LogTime' => "2022-10-$j $time:00:00",
                    'DeviceID' => "OX-8862021010097",
                    'company_id' => "1",
                ];
            }
        }
    }
    // return $arr;
    DB::table('attendance_logs')->insert($arr);
});

Route::get('/test-re', function (Request $request) {

    return "Hello";    // Employee::truncate();
    // DB::statement('DELETE FROM users WHERE id > 2');

    // return 'done';
});

Route::get('/test-date', function (Request $request) {

    // $start = date('Y-m-d');
    // $end = date('Y-m-d');

    $start = date('Y-m-1'); // hard-coded '01' for first day
    $end = date('Y-m-t');

    $model = Attendance::query();
    return $model->whereBetween('date', [$start, $end])
        ->get();

    return 'done';
});

Route::get('/storage', function (Request $request) {
    Storage::put('example.csv', 'francis');
});

Route::post('/upload', function (Request $request) {
    $file = $request->file->getClientOriginalName();
    $request->file->move(public_path('media/employee/file/'), $file);
    return $product_image = url('media/employee/file/' . $file);
    $data['file'] = $file;
});

Route::get('/testAttendance', function () {


    // return $devicesListArray->clone()->where("device_id", "=", 'OX-9662022091021')->pluck('id')[0];
    // return (new AttendanceController)->seedDefaultData(20, [218], '');
});
Route::get('/getActiveSessionId', function () {

    //return (new DeviceCameraController())->getActiveSessionId();
    return (new DeviceCameraController(''))->getActiveSessionId();
});
Route::get('/pushUserToCameraDevice', function () {

    //return (new DeviceCameraController())->getActiveSessionId();
    // Get the image data from the URL
    $imageData = file_get_contents("https://backend.mytime2cloud.com/media/employee/profile_picture/1696868606.jpg");

    if ($imageData !== false) {
        // Convert the image data to base64 format
        $imageData = base64_encode($imageData);
        return (new DeviceCameraController(''))->pushUserToCameraDevice("Venu1",  "9191",  $imageData);
    }
});
Route::get('/testGetDevices', function () {

    // return Device::where("company_id", 1)->get();

    // return AttendanceLog::with(["device"])->where("company_id", 1)->where("LogTime", ">=", '2024-01-15 00:00:00')->distinct("DeviceID")->get()->pluck("DeviceID");
});
Route::get('/updateCameraDeviceLiveStatus', function () {

    //return (new DeviceCameraController())->getActiveSessionId();
    return (new DeviceCameraController(''))->updateCameraDeviceLiveStatus();
});

Route::get('/writeLastAttendanceLogTime', function () {
    return (new AttendanceLogController)->writeLastAttendanceLogTime('', '');
});
Route::get('/verifyDuplicate', function () {
    return (new AttendanceLogController)->store();
});


Route::get('/nightshift', function () {
    // return (new NightShiftController)->render();
});

Route::post('/cameratesting', function (Request $request) {

    $requestData = $request->all();
    //return $request;
    $requstJson = json_encode($requestData);


    DB::table('test_camera_api')
        ->insert([
            'json_content' => $requstJson,
        ]);
});

Route::get('testgetpendinginvoice', function () {





    //monthly invoices remind before 5 days
    $date = date("Y-m-d", strtotime("+5 day", strtotime(date("Y-m-d"))));

    $invoices1 = CustomerPayments::with(["customer.user", "company", "customer.primary_contact", "customer_product_services"])
        ->where("invoice_date", $date)
        ->where("status", "Pending")
        ->whereHas("customer_product_services", function ($q) use ($date) {
            $q->where("payment_type", "Monthly");
        })

        ->get();


    //$this->callMails($invoices1);

    //quaterly invoice 10 days before
    $date = date("Y-m-d", strtotime("+10 day", strtotime(date("Y-m-d"))));
    $invoices2 = CustomerPayments::with(["customer.user", "company", "customer.primary_contact", "customer_product_services"])
        ->where("invoice_date", $date)
        ->where("status", "Pending")
        ->whereHas("customer_product_services", function ($q) use ($date) {
            $q->where("payment_type", "Quarter");
        })->get();
    //$this->callMails($invoices2);

    //yearly invoice 30 days before
    $date = date("Y-m-d", strtotime("+30 day", strtotime(date("Y-m-d"))));
    $invoices3 = CustomerPayments::with(["customer.user", "company", "customer.primary_contact", "customer_product_services"])
        ->where("invoice_date", $date)
        ->where("status", "Pending")
        ->whereHas("customer_product_services", function ($q) use ($date) {
            $q->where("payment_type", "Yearly");
        })->get();




    return (new CustomerPaymentsController())->sendReminderMail($invoices1[0]);





    return "Hello";
});
Route::get('testqueuemail2', function () {

    $data = [
        'subject' => "  Alarm Notification  " . date("Y-m-d H:i:s"),
        'body' => "  Alarm Notification  " . date("Y-m-d H:i:s"),
        'to' => "venuakil2@gmail.com",
    ];
    return (new ClientController())->sendExternalMail($data);

    return $response = Http::timeout(30)
        ->withoutVerifying()
        ->asForm() // Sends as multipart/form-data
        ->post("https://tanjore.hyderspark.com/xtremeguard_mail.php", $data);


    return Mail::to('venuakil2@gmail.com')->queue(new TestMail());
});
Route::get('testqueuemail', function () {



    return Mail::to('venuakil2@gmail.com')->queue(new TestMail());
});

Route::get('test-email2', function () {

    return Mail::to('venuakil2@gmail.com')->queue(new TestMail());


    Mail::raw('This is a test email from Laravel using Gmail SMTP.', function ($message) {
        $message->to('venuakil2@gmail.com')->subject('Test Email');
    });
    return 'Test email sent!';
});
Route::get('/testmail', function () {

    // abort(500, 'Test error for email');

    Mail::to("venuakil2@gmail.com")->send(new TestMail());
});
Route::get('/test_attachment', function () {
    $test = new RenderController();
    return  $test->renderOffCron(8);

    return  $model = ReportNotification::with(["managers", "company.companyMailContent"])->where("id", "8")->first();

    $models = ReportNotification::get();

    foreach ($models as $model) {

        return $model;

        if ($model->frequency == "Daily") {
            if (in_array("Email", $model->mediums)) {
                Mail::to($model->tos)
                    ->cc($model->ccs)
                    ->bcc($model->bccs)
                    ->queue(new ReportNotificationMail($model));
            }
            // if (in_array("Whatsapp", $model->mediums)) {
            //     Mail::to($model->tos)->send(new TestMail($model));
            // }
        }
    }
    return "done";
});

Route::post('/ardino_testing', function (Request $request) {

    $requestData = $request->all();
    //return $request;
    $requstJson = json_encode($requestData);


    DB::table('test_camera_api')
        ->insert([
            'json_content' => $requstJson,
        ]);
});


// Francis
Route::post("test-alarm-event", [TestController::class, "AlarmEvent"]);

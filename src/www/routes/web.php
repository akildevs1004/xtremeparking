<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyIfLogsDoesNotGenerate;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Http\Request;
use App\Http\Controllers\Parking\CameraLogListenerController;
use App\Services\MqttService;
// Route::redirect('/', 'api/test');
Route::get("/", function (Request $request) {
 Logger::channel('custom')->info("Default");


Logger::channel('custom')->info(date("Y-m-d H:i:s"));
    Logger::channel('custom')->info(json_encode($request->all()));


    return $request->all();
    
});
  Route::post("/NotificationInfo/DeviceInfo", function (Request $request) {
// //  Logger::channel('custom')->info("Default");


// // Logger::channel('custom')->info(date("Y-m-d H:i:s"));
//     Logger::channel('custom')->info("DeviceInfo",$request->all());


     return $request->all();
    
  });
Route::get("/NotificationInfo/TollgateInfo", function (Request $request) {
//  Logger::channel('custom')->info("Default");


// Logger::channel('custom')->info(date("Y-m-d H:i:s"));
    Logger::channel('custom')->info("TollgateInfo",json_encode($request->all()));


    return $request->all();
    
});


 
Route::any('/EventHttpUpload/keepalive', function (Request $request) {
    // Logger::channel('custom')->info('EventHttpUpload ping received',[]);

   // Logger::channel('custom')->info('EventHttpUpload ping received', $request->all());
    $data = $request->all();

    // Check for base64 file content
    if (!empty($data['file_content'])) {
        $base64 = $data['file_content'];

        // Remove "data:image/..." prefix if exists
        if (Str::contains($base64, 'base64,')) {
            $base64 = explode('base64,', $base64)[1];
        }

        // Decode image
        $image = base64_decode($base64);

        // Determine filename (use payload or fallback)
        $filename = $data['filename'] ?? time() . '.jpg';

        // Use ENV path or default
        $storagePath = config('app.PARKING_CAMERA_STORAGE_PATH_NODE', 'parking/cameras');

        // Build full path: company/camera_code/date/filename
        $companyId  = $data['company_id'] ?? '0';
        $cameraCode = $data['camera_code'] ?? 'unknown';
        $datePath   = date('Y/m/d');

        $fullPath = $storagePath . '/' . $companyId . '/' . $cameraCode . '/' . $datePath;

        // Save file
        Storage::disk('public')->put($fullPath . '/' . $filename, $image);

        
    }
    else
        {
//Logger::channel('custom')->info('No File Content', $request->all());

        }
    return response()->json(['status' => 'ok']);
});

Route::any('/PictureHttpUpload/keepalive', function (Request $request) {
    // Logger::channel('custom')->info('PictureHttpUpload ping received',[]);
    //Logger::channel('custom')->info('PictureHttpUpload ping received',$request->all());

    $data = $request->all();

    // Check for base64 file content
    if (!empty($data['file_content'])) {
        $base64 = $data['file_content'];

        // Remove "data:image/..." prefix if exists
        if (Str::contains($base64, 'base64,')) {
            $base64 = explode('base64,', $base64)[1];
        }

        // Decode image
        $image = base64_decode($base64);

        // Determine filename (use payload or fallback)
        $filename = $data['filename'] ?? time() . '.jpg';

        // Use ENV path or default
        $storagePath = config('app.PARKING_CAMERA_STORAGE_PATH_NODE', 'parking/cameras');

        // Build full path: company/camera_code/date/filename
        $companyId  = $data['company_id'] ?? '0';
        $cameraCode = $data['camera_code'] ?? 'unknown';
        $datePath   = date('Y/m/d');

        $fullPath = $storagePath . '/' . $companyId . '/' . $cameraCode . '/' . $datePath;

        // Save file
        Storage::disk('public')->put($fullPath . '/' . $filename, $image);

        
    }
    else
        {
//Logger::channel('custom')->info('No File Content', $request->all());

        }

    return response()->json(['status' => 'ok']);
}); 
Route::post('/cgi-bin/NotifyEvent222222222', function (Request $request) {

    $payload = $request->all();

    // Check if the camera captured a snapshot
    $withSnap = data_get($payload, 'Data.WithSnap', false);

    // Extract vehicle info for logging and filename
    $plate  = data_get($payload, 'Data.Vehicle.PlateNumber', 'unknown_plate');
    $camera = data_get($payload, 'Data.Vehicle.MachineName', 'unknown_camera');
    $lane   = data_get($payload, 'Data.Vehicle.Lane', 'unknown_lane');
    $companyId = $payload['company_id'] ?? '0';

    // Log basic info
   Logger::channel('custom')->info('ANPR event received - '. $withSnap, [
        'plate' => $plate,
        'camera' => $camera,
        'lane' => $lane,
        'with_image' => $withSnap,
    ]);

    // Only save image if WithSnap=true and file_content exists
    if ($withSnap && !empty($payload['file_content'])) {

        $base64 = $payload['file_content'];

        // Remove possible base64 prefix
        if (Str::contains($base64, 'base64,')) {
            $base64 = explode('base64,', $base64)[1];
        }

        $image = base64_decode($base64);

        // Filename: plate + timestamp
        $filename = $payload['filename'] ?? $plate . '_' . time() . '.jpg';

        // Get storage path from ENV
        $storagePath = env("PARKING_CAMERA_STORAGE_PATH_NODE");

        // Organize by company / camera / date
        $datePath = date('Y/m/d');
        $fullPath = $storagePath . '/' . $companyId . '/'    ;

        // Save image to storage/app/public
        Storage::disk('public')->put($fullPath . '/' . $filename, $image);

        \Log::info('ANPR image saved', [
            'plate' => $plate,
            'camera' => $camera,
            'path' => 'storage/' . $fullPath . '/' . $filename,
            'size_bytes' => strlen($image)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ANPR image saved',
            'image_path' => 'storage/' . $fullPath . '/' . $filename
        ]);
    }

    return response()->json([
        'status' => 'success',
        'message' => $withSnap ? 'WithSnap=true but no file_content sent' : 'No snapshot in payload'
    ]);
});

Route::post('/cgi-bin/NotifyEvent222', function (Request $request) {




// $payload = $request->all();

//     $withSnap = data_get($payload, 'Data.WithSnap', false);

//   Logger::channel('custom')->info('ANPR event received', [
//         'plate' => data_get($payload, 'Data.Vehicle.PlateNumber'),
//         'camera' => data_get($payload, 'Data.Vehicle.MachineName'),
//         'lane' => data_get($payload, 'Data.Vehicle.Lane'),
//         'with_image' => $withSnap,
//     ]);

//     return response()->json(['status'=>'ok']);








    $data = $request->all();

    // Check if payload contains file_content
    if (!empty($data['file_content'])) {
        // Log basic info without saving image
        Logger::channel('custom')->info('ANPR event received WITH image', [
            'vehicle_id'  => $data['vehicle_id'] ?? 'unknown',
            'camera_code' => $data['camera_code'] ?? 'unknown',
            'company_id'  => $data['company_id'] ?? 'unknown',
            'filename'    => $data['filename'] ?? 'unknown',
            'payload_size' => strlen($data['file_content']) // length of base64 string
            
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ANPR image detected in payload',
            'payload_size' => strlen($data['file_content'])
        ]);
    }

    // If no image
    Logger::channel('custom')->info('ANPR event received WITHOUT image', $data);

    return response()->json([
        'status' => 'success',
        'message' => 'No image found in payload'
    ]);
});

Route::post('/cgi-bin/NotifyEvent', function (\Illuminate\Http\Request $request) {


// Logger::channel('custom')->info('Vehicle ping received', []);
Logger::channel('custom')->info('Vehicle ping received', $request->all());

//  \Log::info('ANPR Event:', $request->all());

//save image 

// Get payload
    $data = $request->all();

    // Check for base64 file content
    if (!empty($data['file_content'])) {
        $base64 = $data['file_content'];

        // Remove "data:image/..." prefix if exists
        if (Str::contains($base64, 'base64,')) {
            $base64 = explode('base64,', $base64)[1];
        }

        // Decode image
        $image = base64_decode($base64);

        // Determine filename (use payload or fallback)
        $filename = $data['filename'] ?? time() . '.jpg';

        // Use ENV path or default
        $storagePath = config('app.PARKING_CAMERA_STORAGE_PATH_NODE', 'parking/cameras');

        // Build full path: company/camera_code/date/filename
        $companyId  = $data['company_id'] ?? '0';
        $cameraCode = $data['camera_code'] ?? 'unknown';
        $datePath   = date('Y/m/d');

        $fullPath = $storagePath . '/' . $companyId . '/' . $cameraCode . '/' . $datePath;

        // Save file
        Storage::disk('public')->put($fullPath . '/' . $filename, $image);

        
    }
    else
        {
Logger::channel('custom')->info('No File Content', $request->all());

        }


 if($request->input('Action')!="Start")
    {
//  \Log::info('ANPR Event:', ["duplicate Event"]);
 return response()->json(['status' => 'duplicate Event']);
    }



$plate = $request->input('Data.TrafficCar.PlateNumber');
    $timestamp = $request->input('Data.StartTime');
    $lane = $request->input('Data.TrafficCar.Lane');
    $direction = $request->input('Data.VehicleDirection');
    $cameraCode = $request->input('Data.TrafficCar.MachineName');
    $eventCategory = $request->input('Data.Code');
    $eventType = $request->input('Data.TrafficCar.Event');

   // $timestamp= str_pad(strtotime( $timestamp), 17, '000');

   $timestamp=str_replace("-",'', $timestamp);
   $timestamp=str_replace(" ",'', $timestamp);
   $timestamp=str_replace(":",'', $timestamp);
   $timestamp=$timestamp.'000';

  // Publish  to MQTT
        // $mqtt = new MqttService();
        // $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/8/cameralogs/new_event",  json_encode([]),"XTP100001");


 $mqtt = new MqttService();

    $payload =    new Request([
        'timestamp'      =>   $timestamp ,
        'filename'       => $plate . '_' . time() . '.jpg',
        'vehicle_id'     => $plate,
        'event_category' => $eventCategory,
        'event_type'     => $eventType,
        'camera_code'    => $cameraCode,
        'direction'      => $direction,
        'lane'           => (string) $lane,
        'tag'            => $plate,
        'company_id'     => 8, // Change to your real company id
        'fields'         => null, // full raw JSON
    ]);
Logger::channel('custom')->info(json_encode($payload->all()));
 

                $DeviceController =  new CameraLogListenerController();
                $response=    $DeviceController->CameraLogProcessing($payload); 


                // Publish  to MQTT
        

        // $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/8/cameralogs/new_event",  json_encode(["response" =>$response ]),"XTP100001");
        $mqtt->publish("xtremeparking/8/cameralogs/new_event",  json_encode(["response" =>$response->original  ]),"XTP100001");
        // $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/8/cameralogs/new_event",  json_encode(["response" =>$response->message ]),"XTP100001");



// $mqtt = new MqttService();
//         $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/8/cameralogs/new_event",  json_encode($response),"XTP100001");

        

 Logger::channel('custom')->info(env('MQTT_DEVICE_CLIENTID') . "/8/cameralogs/new_event");
 Logger::channel('custom')->info(json_encode(["response" =>$response->original  ]));

 

    return response()->json(['status' => 'ok' ]);
});

// \Log::info('ANPR Event:', $request->all());


// Logger::channel('custom')->info("NotifyEvent");
//     \Log::info('ANPR Event:', $request->all());


// Logger::channel('custom')->info(json_encode($request->all()));




// $plate = $request->input('Data.TrafficCar.PlateNumber');
//     $timestamp = $request->input('Data.StartTime');
//     $lane = $request->input('Data.TrafficCar.Lane');
//     $direction = $request->input('Data.VehicleDirection');
//     $cameraCode = $request->input('Data.TrafficCar.MachineName');
//     $eventCategory = $request->input('Data.Code');
//     $eventType = $request->input('Data.TrafficCar.Event');

//     $payload = [
//         'timestamp'      => $timestamp,
//         'filename'       => $plate . '_' . time() . '.jpg',
//         'vehicle_id'     => $plate,
//         'event_category' => $eventCategory,
//         'event_type'     => $eventType,
//         'camera_code'    => $cameraCode,
//         'direction'      => $direction,
//         'lane'           => (string) $lane,
//         'tag'            => $plate,
//         'company_id'     => 8, // Change to your real company id
//         'fields'         => null, // full raw JSON
//     ];
 

//                 $DeviceController =  new CameraLogListenerController();
//                 $response=    $DeviceController->CameraLogProcessing($requestOUT); 



// Logger::channel('custom')->info($response);








Route::get('/notifyFailure', function () {

    $data = [
        'title' => 'Introduction',
        'body' => 'this is from Alarm Xtremeguard',
    ];

    Mail::to(env("ADMIN_MAIL_RECEIVERS"))->send(new NotifyIfLogsDoesNotGenerate($data));
});





/*

$plate = $request->input('Data.TrafficCar.PlateNumber');
    $timestamp = $request->input('Data.StartTime');
    $lane = $request->input('Data.TrafficCar.Lane');
    $direction = $request->input('Data.VehicleDirection');
    $cameraCode = $request->input('Data.TrafficCar.MachineName');
    $eventCategory = $request->input('Data.Code');
    $eventType = $request->input('Data.TrafficCar.Event');

    $payload = [
        'timestamp'      => $timestamp,
        'filename'       => $plate . '_' . time() . '.jpg',
        'vehicle_id'     => $plate,
        'event_category' => $eventCategory,
        'event_type'     => $eventType,
        'camera_code'    => $cameraCode,
        'direction'      => $direction,
        'lane'           => (string) $lane,
        'tag'            => $plate,
        'company_id'     => 8, // Change to your real company id
        'fields'         => null, // full raw JSON
    ];
 

                $DeviceController =  new CameraLogListenerController();
                   $DeviceController->CameraLogProcessing($requestOUT); 


*/
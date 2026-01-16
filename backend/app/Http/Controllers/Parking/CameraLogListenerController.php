<?php

namespace App\Http\Controllers\Parking;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ParkingCameraLogsController;
use App\Http\Controllers\ParkingDeviceController;
use App\Models\CamerasList;
use App\Models\Company;
use App\Models\Device;
use App\Models\ParkingCameraLogs;
use App\Models\ParkingMembers;
use App\Models\ParkingMembersTransactions;
use App\Models\ParkingMembersVehiclesList;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use thiagoalessio\TesseractOCR\TesseractOCR;

class CameraLogListenerController extends Controller
{
    /**
     * Process incoming camera log webhook.
     */



    // public function getMQTT()
    // {
    //     return response()->json([
    //         'host' => env("MQTT_FRONTEND"),
    //         'WATCH_DIR' => env("WATCH_DIR"),
    //         'COMPANY_ID' => env("COMPANY_ID"),
    //         'API_URL' => env("API_URL"),
    //         'API_KEY' => env("API_KEY"),
    //         'MQTT_SERVER' => env("MQTT_SERVER"),
    //         'MQTT_FRONTEND' => env("MQTT_FRONTEND"),
    //         'BASE_HTTP_PORT' => env("BASE_HTTP_PORT"),
    //         'BASE_WS_PORT' => env("BASE_WS_PORT"),







    //     ]);
    // }
    public function CamerasList(Request $request)
    {

        return CamerasList::where("company_id", $request->company_id)->get();
        return [
            "data" => [

                [
                    "id" => 1,
                    "name" => "Entrance Camera",
                    "rtsp_url" => "rtsp://admin:hik@1234@192.168.2.218:554/Streaming/Channels/101",
                    "node_server_ip" => "192.168.2.67",
                ],
                [
                    "id" => 2,
                    "name" => "Exit Camera",
                    "rtsp_url" => "rtsp://admin:hik@1234@192.168.2.219:554/Streaming/Channels/101",
                    "node_server_ip" => "192.168.2.67",
                ],
                // [
                //     "id" => 1,
                //     "name" => "Entrance Camera",
                //     "rtsp_url" => "rtsp://admin:hik@1234@192.168.2.218:554/Streaming/Channels/101",
                //     "node_server_id" => "192.168.2.16",
                // ],
                // [
                //     "id" => 2,
                //     "name" => "Exit Camera",
                //     "rtsp_url" => "rtsp://admin:hik@1234@192.168.2.219:554/Streaming/Channels/101",
                //     "node_server_id" => "192.168.2.16",
                // ],
                // [
                //     "id" => 1,
                //     "name" => "Entrance Camera",
                //     "rtsp_url" => "rtsp://admin:hik@1234@192.168.2.218:554/Streaming/Channels/101",
                //     "node_server_id" => "192.168.2.16",
                // ],
                // [
                //     "id" => 2,
                //     "name" => "Exit Camera",
                //     "rtsp_url" => "rtsp://admin:hik@1234@192.168.2.219:554/Streaming/Channels/101",
                //     "node_server_id" => "192.168.2.16",
                // ]
            ]

        ];
    }


    public function CameraLogProcessing(Request $request)
    {
        // Validate required minimums (tweak rules as needed)
        $data = $request->validate([
            'timestamp'       => ['required'],
            'filename'        => ['required', 'string'],


            'vehicle_id'  => ['nullable', 'string'],
            'event_category'  => ['nullable', 'string'],
            'event_type'      => ['nullable', 'string'],
            'camera_code'     => ['nullable', 'string'],
            'direction'       => ['nullable', 'string'],
            'lane'            => ['nullable', 'string'],
            'tag'  => ['nullable', 'string'],

            'company_id'            => ['required'],
            'fields'            => ['nullable'],




        ]);



        $logId = (string)$data['timestamp'];

        $raw = $logId; // YmdHisv
        $dt  = Carbon::createFromFormat('YmdHisv', $raw, 'Asia/Dubai'); // set TZ if needed

        // Without milliseconds
        $formattedLogTime = $dt->format('Y-m-d H:i:s');        // "2025-09-10 12:53:02"


        // Centralized log helper
        $log = function (string $msg) use ($logId) {
            $line = now()->format('Y-m-d H:i:s') . " {$logId} : {$msg}\n";
            Storage::append('logs/parking-camera-logs-' . now()->format('d-m-Y') . '.log', $line);
        };

        $log('Payload received: ' . json_encode($request->all()));

        // Resolve image path under public disk: storage/app/public/parking_camera_logs/<filename>
        // Make sure you have: php artisan storage:link  (so /storage maps to public disk)
        $fileName = trim($data['filename']);
        // Basic filename hygiene (avoid path traversal)
        $fileName = basename($fileName);

        // $disk = public_path("");
        // $relativePath = env("PARKING_CAMERA_PUBLIC_FOLDER") . '/' . $request->company_id . "/" . $fileName;
        // $imagePath = public_path($relativePath);

        $imagePath =  env("PARKING_CAMERA_STORAGE_PATH") . '/' . $request->company_id . "/" . $fileName;




        if (! file_exists($imagePath)) {
            $log("Vehicle Background Image Not Found at {$imagePath}");
            return response()->json(['status' => 'error', 'message' => 'Image not found'], 404);
        }

        // ---- OCR stage ------------------------------------------------------
        $rawText = '';
        try {
            $log("Starting OCR on {$imagePath}");

            $rawText = (new TesseractOCR($imagePath))
                ->lang('eng') // adjust if you have additional language packs
                ->psm(6)      // Assume a uniform block of text
                ->oem(3)      // Default OCR engine
                ->run();

            // Normalize to the section starting at "Camera Info." if present
            $needle = 'Camera Info.';
            $pos = mb_stripos($rawText, $needle);
            if ($pos !== false) {
                $rawText = mb_substr($rawText, $pos);
            }

            // Strip newlines for your downstream parser
            $rawText = str_replace(["\r", "\n"], '', $rawText);

            $log('OCR complete. Text length=' . mb_strlen($rawText));
        } catch (\Throwable $e) {
            $log('OCR Error: ' . $e->getMessage());
            // Continue flow but with empty $rawText—parser should handle gracefully
        }

        // ---- Parse OCR footer via your existing helper ----------------------
        $imageText = [];
        $imageTextArr = [];
        try {
            // Your existing parser (make sure it handles empty/partial text)
            $imageTextArr = (new ImageProcessingController())->processOcrText($rawText) ?? [];

            $imageText = $imageTextArr["parsed"] ?? [];
        } catch (\Throwable $e) {
            $log('normalizeOcrFooter error: ' . $e->getMessage());
            $imageText = [];
        }

        // Device lookup (by OCR-extracted device_no)
        // $deviceNo = $imageText['device_no'] ?? null;
        $camera_code = $data['camera_code'] ?? null;



        if (! $camera_code) {
            $log('Background Image device_no not Found in OCR');
            $log($imageTextArr["raw"]);



            return response()->json(['status' => 'error', 'message' => $imageTextArr["raw"] . 'Background Image  device_no not found in OCR'], 422);
        }

        $functionIO = null;
        $device     = null;
        $deviceIn     = null;
        $deviceOut     = null;




        $device = Device::with("company")->where('camera_in_name', $camera_code)
            ->orWhere('camera_out_name', $camera_code)
            ->first();

        $functionIO = null;

        if ($device) {
            $isIn  = $device->camera_in_name  === $camera_code;
            $isOut = $device->camera_out_name === $camera_code;

            if ($isIn && $isOut) {
                $functionIO = 'auto';
            } elseif ($isIn) {
                $functionIO = 'in';
            } elseif ($isOut) {
                $functionIO = 'out';
            }
        }

        //check Parking slot is available or not

        if ($functionIO == 'in') {

            $totalParked   = ParkingCameraLogs::where('company_id', $request->company_id)->whereNull('out_time')->count();
            // $Total = Company::where('id', $request->company_id)->first()->parking_count ?? 0;

            $Total = $device->company->parking_count ?? 0;

            if ($Total - $totalParked <= 0) {
                $log('Parking is Full');
                return response()->json(['status' => 'error', 'message' => $camera_code . ' Parking is Full'], 404);
            }
        }




        // $device = Device::where('serial_number', $deviceNo)->first();

        if (! $device) {
            $log('Background Image device_no ' . $camera_code . ' not Registered: ' . $camera_code);
            return response()->json(['status' => 'error', 'message' => $camera_code . ' Device not registered'], 404);
        }

        // Common fields for DB record
        // $plateNo      = $imageText['plate_no']      ?? null;
        $plateNo      = $data['vehicle_id']      ?? null;


        $captureTime  = date("Y-d-m H:i:s", strtotime($imageText['capture_time'])  ?? null);
        $captureTime  = $formattedLogTime; // date("Y-d-m H:i:s", strtotime($imageText['capture_time'])  ?? null);



        $companyId    = $device->company_id;
        // $functionIO   = $device->function; // "In" or "Out"


        if (! $plateNo) {
            $log('Background Image plate_no/vehicle number  not Found in OCR');
            return response()->json(['status' => 'error', 'message' => 'plate_no/vehicle number not found in Image'], 422);
        }
        if (! $functionIO) {
            $log('Background Image functionIO not Found in OCR');
            return response()->json(['status' => 'error', 'message' => 'functionIO not found in Device Table'], 422);
        }
        if (! $captureTime) {
            $log('Background Image captureTime not Found in OCR');
            return response()->json(['status' => 'error', 'message' => 'captureTime not found in Image'], 422);
        }
        // Parse capture time safely (fallback to now)
        $parsedCapture = now();
        try {
            if (!empty($captureTime)) {
                $parsedCapture = \Carbon\Carbon::parse($captureTime);
            }
        } catch (\Throwable $e) {
            $log('capture_time parse error: ' . $e->getMessage());
        }

        // Build raw dump for traceability
        $rawDump = [
            'raw_device_no'       => $imageText['device_no']         ?? null,
            'raw_capture_time'    => $imageText['capture_time']      ?? null,
            'raw_plate_no'        => $imageText['plate_no']          ?? null,
            'raw_vehicle_color'   => $imageText['vehicle_color']     ?? null,
            'raw_vehicle_type'    => $imageText['vehicle_type']      ?? null,
            'raw_vehicle_brand'   => $imageText['vehicle_brand']     ?? null,
            'raw_moving_direction' => $imageText['moving_direction']  ?? null,
            'raw_validity'        => $imageText['validity']          ?? null,
            'raw_country_region'  => $imageText['country_region']    ?? null,
            'raw_plate_color'     => $imageText['plate_color']       ?? null,
            'raw_plate_size'      => $imageText['plate_size']        ?? null,
            'raw_plate_type'      => $imageText['plate_type']        ?? null,
            'raw_province'        => $imageText['province']          ?? null,
            'raw_camera_no'       => $imageText['camera_no']         ?? null,
        ];

        // ---- Handle IN / OUT -----------------------------------------------

        if ($functionIO == "auto") {
            // Handle automatic detection logic here

            $functionIO = "In";
            $open = ParkingCameraLogs::where('company_id', $companyId)
                ->where('log_vehicle_number', $plateNo)
                ->whereNull('out_time')
                ->orderByDesc('id')
                ->first();

            if ($open) {
                $functionIO = "Out";
            }
        }

        $parkingMember = ParkingMembers::where('company_id', $companyId)
            ->where('plate_number', $plateNo)
            ->first();
        // $parkingMember = ParkingMembers::where('company_id', $companyId)
        //     ->where('id', 1)
        //     ->first();


        //is member/tenant guest list vehicle
        $parkingMemberVehicle = $this->getMemberGuestVehicle($plateNo);
        $parking_member_guest_vehicle_id = null;

        if (!is_null($parkingMemberVehicle)) {
            $parkingMember = $parkingMemberVehicle->ParkingMember;
            $parking_member_guest_vehicle_id =  $parkingMemberVehicle->id;
        }


        //try {
        if (Str::lower($functionIO) === 'in') {





            if ($parkingMember) {
                if (!$parkingMember->is_active) {

                    $log(" Vehicle {$plateNo} is in  Block List");

                    $vehicleRecord = [
                        // "image" =>  url(env("PARKING_CAMERA_PUBLIC_FOLDER") . '/' . $companyId) . '/' . $fileName,
                        "image" =>  env("BASE_URL") . '/api/parking_camera_logs' . '/' . $companyId  . '/' . $fileName,
                        "message" => "{$plateNo} is in  Block List"
                    ];

                    return $this->response("Entry", $vehicleRecord, false);
                }
            }

            //guest is vehicles are not  allowed
            if (is_null($parkingMember)   && !$device->company->guset_vehicles) {


                $log("Guest Vehicles are Not Allowed.   {$plateNo} is not Allowed");

                $vehicleRecord = [
                    // "image" =>  url(env("PARKING_CAMERA_PUBLIC_FOLDER") . '/' . $companyId) . '/' . $fileName,
                    "image" =>  env("BASE_URL") . '/api/parking_camera_logs' . '/' . $companyId  . '/' . $fileName,
                    "message" => "Guest Vehicles are Not Allowed.     {$plateNo} is Not Allowed"
                ];

                return $this->response("Entry", $vehicleRecord, false);
            }



            $new_Extradata = [];

            if ($parkingMember) {
                if ($parkingMember->is_active) {
                    if ($parkingMember->membership_start <= date("Y-m-d") && $parkingMember->membership_end >= date("Y-m-d")) {
                        // Membership is valid
                        $new_Extradata["is_membership"] = "Yes";
                        $new_Extradata["membership_id"] = $parkingMember->id;
                        $new_Extradata["membership_status"] = "Active";

                        if (!is_null($parking_member_guest_vehicle_id) && $parkingMember->guest_parking_hours_count < 0) {
                            $log("{$parkingMember->first_name} {$parkingMember->last_name} Membership has No balance  .  {$plateNo} is Not Allowed");
                            $vehicleRecord = [
                                "image" =>  env("BASE_URL") . '/api/parking_camera_logs' . '/' . $companyId  . '/' . $fileName,
                                "message" => "{$parkingMember->first_name} {$parkingMember->last_name} Membership has No balance  .  {$plateNo} is Not Allowed"
                            ];
                            return $this->response("Entry", $vehicleRecord, false);
                        }
                    } else {
                        // Membership expired
                        $new_Extradata["is_membership"] = "Expired";
                        $new_Extradata["membership_id"] = $parkingMember->id;
                        $new_Extradata["membership_status"] = "Expired";

                        $log("{$parkingMember->first_name} {$parkingMember->last_name} Membership is expired on {$parkingMember->membership_end} .  {$plateNo} is Not Allowed");
                        $vehicleRecord = [
                            "image" =>  env("BASE_URL") . '/api/parking_camera_logs' . '/' . $companyId  . '/' . $fileName,
                            "message" => "{$parkingMember->first_name} {$parkingMember->last_name} Membership is expired on {$parkingMember->membership_end} .  {$plateNo} is Not Allowed"
                        ];
                        return $this->response("Entry", $vehicleRecord, false);
                    }

                    $new_Extradata["membership_start_date"] = $parkingMember->membership_start;
                    $new_Extradata["membership_end_date"] = $parkingMember->membership_end;
                    $new_Extradata["member_type"] = $parkingMember->member_type;
                }
            } else {
                $new_Extradata["is_membership"] = "No";
                $new_Extradata["membership_id"] = null;
                $new_Extradata["membership_status"] = "No";
            }


            $payload = [
                'membership_id'               => $parkingMember ? $parkingMember->id : null,
                // 'device_id'               => $device->id,
                'company_id'              => $companyId,
                'log_timestamp'           =>  (string)$data['timestamp'],
                'log_vehicle_number'      => $plateNo,
                // 'device_in_out'           => 'In',
                'device_id_in'           => $device->id,
                'in_background_file_name' => $fileName,
                'in_time'                 => $captureTime,
                'raw_info'                => ($imageTextArr["raw"]),
                'raw_event_category'      => $data['event_category'] ?? null,
                'raw_event_type'          => $data['event_type'] ?? null,
                'raw_camera_code'         => $data['camera_code'] ?? null,
                'raw_direction'           => $data['direction'] ?? null,
                'raw_lane'                => $data['lane'] ?? null,
                'member_guest_vehicle_id'                => $parking_member_guest_vehicle_id,
            ] + $rawDump;

            $new = ParkingCameraLogs::create($payload);


            // $new->public_image_url =  url(env("PARKING_CAMERA_PUBLIC_FOLDER") . '/' . $companyId);
            $new->public_image_url =  env("BASE_URL") . '/api/parking_camera_logs' . '/' . $companyId;

            $new = array_merge($new->toArray(), $new_Extradata);





            $request =    new Request([
                'company_id' => $new["company_id"],
                'event_id' => $new["id"],
                'device_id' => $new["device_id_in"],
                'function' => "in",


                'trigger' => 'automatic',
                'parking_gate_close_time' => $device->company->parking_gate_close_time,
                'device_serial_number' => $device->serial_number



            ]);
            $DeviceController =  new ParkingDeviceController($request);
            $DeviceController->OpenGate($request);

            $new["gate_open_automatically"] = "Vehicle Entry  - Gate Open Automatically";

            $new["function"] = "in";


            $log("New Parking Vehicle IN:  {$plateNo} id={$new['id']}");

            return $this->response("success", $new, true);
            // return response()->json(['status' => 'success', 'id' => $new->id], 201);
        }



        if (Str::lower($functionIO) === 'out') {
            // Example OUT logic (adjust to your business rules):
            // 1) Find the most recent unmatched IN record for the same plate & company (and optionally lane/camera)
            // 2) Set out_time, duration, fee, and store out_background_file_name

            if (!$plateNo) {
                $log('OUT event missing plate_no; cannot match previous IN');
                return response()->json(['status' => 'error', 'message' => 'Missing plate_no for OUT'], 422);
            }



            $open = ParkingCameraLogs::where('company_id', $companyId)
                ->where('log_vehicle_number', $plateNo)
                ->whereNull('out_time')
                ->orderByDesc('id')
                ->first();

            $previousInLog = $open;



            if (!$previousInLog) { //if previous IN time is not exist


                $log("No open IN record found for OUT plate={$plateNo}");
                // You may still want to create a raw OUT record for traceability
                $orphan = ParkingCameraLogs::create([

                    'membership_id'               => $parkingMember ? $parkingMember->id : null,
                    // 'device_id'                 => $device->id,
                    //'device_id_in'           => $device->id,

                    'company_id'                => $companyId,
                    'log_timestamp'             => (string)$data['timestamp'],
                    'log_vehicle_number'        => $plateNo,
                    // 'device_in_out'             => 'Out',
                    //'in_background_file_name'  => $fileName,
                    //'in_time'                  => $captureTime,
                    'raw_info'                  => json_encode($imageText),
                    'raw_event_category'        => $data['event_category'] ?? null,
                    'raw_event_type'            => $data['event_type'] ?? null,
                    'raw_camera_code'           => $data['camera_code'] ?? null,
                    'raw_direction'             => $data['direction'] ?? null,
                    'raw_lane'                  => $data['lane'] ?? null,
                ] + $rawDump);

                // return response()->json([
                //     'status'  => 'warning',
                //     'message' => 'No matching IN found; OUT recorded as orphan',
                //     'id'      => $orphan->id
                // ], 202);

                $open = ParkingCameraLogs::where("id", $orphan->id)->first();;
            }

            // Close the session

            //check QR Code Payment received or not
            if ($open->payment_datetime) {
                //check buffer valid time
                $QRPaymentStatus =  $this->checkQRCodePaymentValidTime($open, $captureTime);

                // In Buffer time
                if ($QRPaymentStatus) {

                    $open = ParkingCameraLogs::where("id", $open->id)->first();;

                    $open->gate_open_automatically = "QR Paid .  No Payment  Required.  Gate Open Automatically";

                    // $open->public_image_url =  url(env("PARKING_CAMERA_PUBLIC_FOLDER") . '/' . $companyId);

                    $open->public_image_url =  env("BASE_URL") . '/api/parking_camera_logs' . '/' . $companyId;


                    $request =    new Request([
                        'company_id' => $open->company_id,
                        'event_id' => $open->id,
                        'device_id' => $open->device_id_out,
                        "function" => "out",
                        'trigger' => 'automatic',
                        'device_serial_number' => $device->serial_number,
                        'parking_gate_close_time' => $device->company->parking_gate_close_time,

                    ]);
                    $DeviceController =  new ParkingDeviceController($request);
                    $DeviceController->OpenGate($request);



                    $log("Vehicle OUT closed: plate={$plateNo} id={$open->id}  ");
                    return $this->response("success2", $open, true);
                } else  ///create new Log Entry with Latest time
                {

                    $requestData = new Request(["log_id" => $open->id]);
                    $parkingLog = new ParkingCameraLogsController();
                    $response = $parkingLog->qrParkingExtraPayment($requestData);

                    if ($response instanceof \Illuminate\Http\JsonResponse) {
                        $payload = $response->getData(true); // <-- assoc array



                        if (!empty($payload['record']['id'])) {
                            $open = ParkingCameraLogs::where("id", $payload["record"]["id"])->first();;

                            $open->out_time = date("Y-m-d H:i:s");
                        }
                    } else {
                        $log("Invalid Vehicle Log Details {$plateNo} id={$open->id}  ");
                        return $this->response("error", "Invalid Vehicle Log Details", false);
                    }
                }
            } else {
                $open->out_time = $captureTime; // $parsedCapture->format('Y-m-d H:i:s');
            }

            $open->device_id_out = $device->id;

            $open->out_background_file_name = $fileName;


            //$open->total_amount   = 0;
            if ($open->in_time && $open->out_time) {
                // Duration (minutes)
                $inAt  = \Carbon\Carbon::parse($open->in_time);
                $outAt = \Carbon\Carbon::parse($open->out_time);
                $minutes = max(0, $inAt->diffInMinutes($outAt));



                // TODO: Calculate fee based on your tariff rules
                $feeArray = $this->calculateParkingFee($minutes, $device->company->parking_price_per_hour);


                $open->duration_in_minutes = $minutes;
                $open->duration_in_hours =  $feeArray['hours'];

                $open->duration_per_hour_amount = (int)  $feeArray['perHourRate'];
                $open->total_amount   = $feeArray['fee'];
            }

            //parkign member guest list vehicle


            //parking member own vehicle
            if ($parkingMember) {

                $open->membership_id = $parkingMember->id;

                if ($parkingMember->is_active) {

                    if ($parkingMember->membership_start <= date("Y-m-d") && $parkingMember->membership_end >= date("Y-m-d")) {



                        //check is vehicle is belongs to Member Guest List

                        if (!is_null($parking_member_guest_vehicle_id)) {

                            // debit balance from Parking member account
                            ParkingMembers::where("id", $parkingMember->id)->update(["guest_parking_hours_count" => $parkingMember->guest_parking_hours_count - $open->total_amount]);

                            $transactionData = [
                                'company_id' => $parkingMember->company_id,
                                'member_id' => $parkingMember->id,
                                'debit' => $open->total_amount,
                                "parking_log_id" => $open->id,
                                'notes' => "Parking charges",
                                'created_datetime' => date("Y-m-d H:i:s"),


                            ];
                            $record = ParkingMembersTransactions::create($transactionData);



                            $open->save(); //update




                            $open = ParkingCameraLogs::where("id", $open->id)->first();;

                            $open->is_membership = 1;
                            $open->membership_id = $parkingMember->id;
                            $open->membership_status = "Active";


                            $open->gate_open_automatically = "Money Debited from Member Account.  No Payment  Required.  Gate Open Automatically";

                            // $open->public_image_url =  url(env("PARKING_CAMERA_PUBLIC_FOLDER") . '/' . $companyId);

                            $open->public_image_url =  env("BASE_URL") . '/api/parking_camera_logs' . '/' . $companyId;


                            $request =    new Request([
                                'company_id' => $open->company_id,
                                'event_id' => $open->id,
                                'device_id' => $open->device_id_out,
                                "function" => "out",
                                'trigger' => 'automatic',
                                'device_serial_number' => $device->serial_number,
                                'parking_gate_close_time' => $device->company->parking_gate_close_time,

                            ]);
                            $DeviceController =  new ParkingDeviceController($request);
                            $DeviceController->OpenGate($request);



                            $log("Money Debited from Member Account.  No Payment  Required.  Gate Open Automatically.  plate={$plateNo} id={$open->id}  ");
                            return $this->response("success2", $open, true);
                        } else {
                            $open->total_amount   = 0;
                        }
                    }
                }
            }


            //free parking for guests
            if (is_null($parkingMember) && $device->company->guset_vehicles == true &&  $device->company->guset_vehicles_payment == false) {

                $open->total_amount   = 0;
            }
            $open->save(); //update



            //information Only

            if ($parkingMember) {

                $open->device_serrial_number = $device->serial_number;


                if ($parkingMember->is_active) {
                    if ($parkingMember->membership_start <= date("Y-m-d") && $parkingMember->membership_end >= date("Y-m-d")) {
                        // Membership is valid
                        $open->is_membership = 1;
                        $open->membership_id = $parkingMember->id;
                        $open->membership_status = "Active";
                        $open->total_amount   = 0;
                    } else {
                        // Membership expired
                        $open->is_membership = 1;
                        $open->membership_id = $parkingMember->id;
                        $open->membership_status = "Membership Expired";
                    }

                    $open->function = "out";

                    $open->membership_start_date = $parkingMember->membership_start;
                    $open->membership_end_date = $parkingMember->membership_end;
                    $open->member_type = $parkingMember->member_type;
                } else {
                    $open->membership_status = "In-Active";
                }
            } else {
                $open->is_membership = 0;
                $open->membership_id = null;
                $open->membership_status = "No";
            }

            //auto pass
            if ($open->total_amount == 0) {
                $request =    new Request([
                    'company_id' => $open->company_id,
                    'event_id' => $open->id,
                    'device_id' => $open->device_id_out,
                    "function" => "out",
                    'trigger' => 'automatic',
                    'device_serial_number' => $device->serial_number,
                    'parking_gate_close_time' => $device->company->parking_gate_close_time,

                ]);
                $DeviceController =  new ParkingDeviceController($request);
                $DeviceController->OpenGate($request);
                if ($parkingMember)
                    $open->gate_open_automatically = $parkingMember->member_type . "  is Active  - Gate Open Automatically";

                else
                    $open->gate_open_automatically = " Guest .  No Payment  Required.  Gate Open Automatically";
            }




            // $open->public_image_url =  url(env("PARKING_CAMERA_PUBLIC_FOLDER") . '/' . $companyId);


            $open->public_image_url =  env("BASE_URL") . '/api/parking_camera_logs' . '/' . $companyId;



            $log("Vehicle OUT closed: plate={$plateNo} id={$open->id}  ");
            return $this->response("success2", $open, true);

            // return response()->json(['status' => 'success', 'id' => $open->id, 'minutes' => $minutes, 'fee' => $fee], 200);
        }

        // Unknown device function
        $log('Device function neither IN nor OUT: ' . $functionIO);
        return response()->json(['status' => 'error', 'message' => 'Invalid device function'], 422);
        // } catch (\Throwable $e) {
        //     $log('DB/Create error: ' . $e->getMessage());
        //     return response()->json(['status' => 'error', 'message' => 'Internal error-' . json_encode($e)], 500);
        // }
    }

    /**
     * Simple fee calculator placeholder.
     * Replace this with your real tariff logic.
     */

    public function checkQRCodePaymentValidTime($open, $captureTime)
    {


        // Pull payment time & buffer (minutes). Fallback to 50 if null.
        $paymentAt =  Carbon::parse($open->payment_datetime);
        $bufferMin = is_numeric($open->parking_exit_buffertime) ? (int) $open->parking_exit_buffertime : 20;



        // Calculate expiry = payment time + buffer minutes
        $expiryAt = $paymentAt->copy()->addMinutes($bufferMin);
        $now = now(); // uses app timezone

        if ($now->gt($expiryAt)) {
            // Optional: log for audit
            return false; //expired
        }


        //update exit log details
        $open->out_time = $captureTime;
        $open->save();


        return true;
    }
    protected function calculateParkingFee(int $minutes, $perHourRate)
    {


        // $perHourRate = 5; // Example rate per hour

        if ($minutes <= 60)  $hours = 1;
        else
            $hours = (int)ceil(($minutes) / 60);

        $total = $hours * $perHourRate;

        return ["hours" => $hours, "fee" => ($total), "perHourRate" => $perHourRate];
    }

    public function getQROutVehicleDetails(Request $request)
    {
        $logId = date("YmdHis");


        // Centralized log helper
        $log = function (string $msg) use ($logId) {
            $line = now()->format('Y-m-d H:i:s') . " {$logId} : {$msg}\n";
            Storage::append('logs/parking-qr-logs-' . now()->format('d-m-Y') . '.log', $line);
        };
        if ($request->filled("vehicle_number")) {

            $vehicleNumber = $request->vehicle_number;

            $vehicleLog = ParkingCameraLogs::with("company")->where("log_vehicle_number", $vehicleNumber)->orderBy("in_time", "DESC")->first();
            if ($vehicleLog) {

                $data = [];

                if (!$vehicleLog->out_time || ($vehicleLog->total_amount > 0 && $vehicleLog->payment_mode == null)) {

                    $data["out_time"] = date("Y-m-d H:i:s");

                    if ($vehicleLog->in_time && $data["out_time"]) {
                        // Duration (minutes)
                        $inAt  = \Carbon\Carbon::parse($vehicleLog->in_time);
                        $outAt = \Carbon\Carbon::parse($data["out_time"]);
                        $minutes = max(0, $inAt->diffInMinutes($outAt));

                        // TODO: Calculate fee based on your tariff rules
                        $feeArray = $this->calculateParkingFee($minutes, $vehicleLog->company->parking_price_per_hour ?? 0);


                        $data["duration_in_minutes"] = $minutes;
                        $data["duration_in_hours"]  =  $feeArray['hours'];

                        $data["duration_per_hour_amount"]  = (int) $feeArray['perHourRate'];
                        $data["total_amount"]      = $feeArray['fee'];
                        $data["parking_price_per_hour"]      = $vehicleLog->company->parking_price_per_hour;
                    }

                    return ["vehicle" => $vehicleLog, "fee" => $data];
                }

                return ["vehicle" => $vehicleLog, "fee" => null];
            } else {
                $log("{$vehicleNumber} Vehicle Details are not found");

                return response()->json([
                    'status'  => 'error',
                    'message' => "{$vehicleNumber} Vehicle Details are not found"
                ], 422);
            }
        }
    }

    public function getMemberGuestVehicle($vehicleNumber)
    {

        return ParkingMembersVehiclesList::with("ParkingMember")->where("vehicle_number", $vehicleNumber)->first();
    }
}

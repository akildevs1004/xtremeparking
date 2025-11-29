<?php

namespace App\Http\Controllers\Customers\Api;

use App\Http\Controllers\AlarmEventsTechnicianController;
use App\Http\Controllers\API\ClientController;
use Illuminate\Support\Facades\Log as Logger;
use App\Http\Controllers\Controller;
use App\Http\Controllers\WhatsappController;
use App\Mail\DbBackupMail;
use App\Mail\EmailContentDefault;
use App\Mail\ReportNotificationMail;
use App\Models\AlarmDeviceTemperatureLogs;
use App\Models\AlarmEvents;
use App\Models\AlarmEventsTechnician;
use App\Models\AlarmLogs;
use Illuminate\Http\Request;
use App\Models\Community\AttendanceLog;
use App\Models\Company;
use App\Models\Customers\CustomerContacts;
use App\Models\Customers\Tickets;
use App\Models\Deivices\DeviceZones;
use App\Models\Device;
use App\Models\DeviceNotificationsManagers;
use App\Models\ReportNotification;
use App\Models\ReportNotificationLogs;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ApiAlarmDeviceTemperatureLogsController extends Controller
{

    public function updateAlarmResponseTime()
    {

        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '**********************************************************************************');
        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step4 updateDuration1 Create Event Process');


        // $devicesList = Device::with(["customer.mappedsecurity"])->where("serial_number", "!=", null)
        //     ->where("device_type", "!=", "Manual")
        //     ->where("serial_number", "!=", "Manual")
        //     ->where("serial_number", "!=", "Mobile")
        //     ->where("serial_number", "!=", "mobile")

        //     ->where("serial_number", "!=", null)->get();


        $devicesList = Device::with(["customer.mappedsecurity", "activealarmlogs"])->where("serial_number", "!=", null)
            ->where("device_type", "!=", "Manual")
            ->where("serial_number", "!=", "Manual")
            ->where("serial_number", "!=", "Mobile")
            ->where("serial_number", "!=", "mobile")

            ->where("serial_number", "!=", null)
            ->whereHas("activealarmlogs")
            ->get();



        $log[] = $this->updateDuration1($devicesList);
        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step4 updateDuration1');
        $log[] = $this->updateAlarmStartDatetime3($devicesList);
        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step5 updateAlarmStartDatetime3End ' . json_encode($log));

        return  json_encode($log);
    }
    public function updateDuration1($devicesList)
    {

        $message = [];

        foreach ($devicesList as $key => $device) {

            // try {

            //////////////$message[] = $this->stopOldAlarms($device);
            $data = AlarmLogs::where("serial_number", $device['serial_number'])
                ->where("time_duration_seconds", null)
                ->where("company_id", '>', 0)
                //->where("created_at", '<=',  date("Y-m-d H:i:s", strtotime("-3 seconds")))
                ->orderBy("created_at", "DESC")
                ->get();

            for ($i = 0; $i < count($data); $i++) {

                $currentLog = $data[$i];
                //$previousLogTime = isset($data[$i + 1]) ? $data[$i + 1]['log_time'] : date("Y-m-d H:i:0", strtotime("-10 minutes"));
                //$previousLogTime = isset($data[$i + 1]) ? $data[$i + 1]['log_time'] : false;
                $previousLogTime = false;
                $fisrtRecord = AlarmLogs::where("serial_number", $device['serial_number'])
                    ->where("company_id", '>', 0)
                    ->orderBy("log_time", "ASC")
                    ->first();
                $previousLogTime = date("Y-m-d H:i:0", strtotime("-10 minutes"));
                if ($fisrtRecord["id"] == $currentLog["id"]) {
                    $previousLogTime = date("Y-m-d H:i:0", strtotime("-10 minutes"));
                } else {

                    $previousRecord = AlarmLogs::where("serial_number", $device['serial_number'])
                        ->where("company_id", '>', 0)
                        ->where("alarm_type",    $currentLog["alarm_type"])
                        ->where("log_time", "<", $currentLog["log_time"])
                        ->orderBy("log_time", "DESC")
                        ->first();
                    if (isset($previousRecord["log_time"]))
                        $previousLogTime = $previousRecord["log_time"];
                }



                if ($previousLogTime) {

                    $latestLogTime = $currentLog['log_time'];
                    //$previousLogTime = $previousLog['log_time'];

                    $datetime1 = new DateTime($previousLogTime);
                    $datetime2 = new DateTime($latestLogTime);

                    $interval = $datetime1->diff($datetime2);
                    $secondsDifference = $interval->s + ($interval->i * 60) + ($interval->h * 3600) + ($interval->days * 86400);

                    //return [$secondsDifference, $latestLogTime, $nextLogTime];
                    AlarmLogs::where('id', $currentLog['id'])->update(["time_duration_seconds" => $secondsDifference]);
                }
            }
            // } catch (\Exception $e) {
            // }
        }

        return  $message;
    }
    /*
    public function updateAlarmEndDatetime2($devicesList)
    {
        $message = [];
        foreach ($devicesList as $key => $device) {

            $alarmData =  AlarmEvents::where("serial_number", $device['serial_number'])
                ->where("alarm_end_datetime", null)
                ->orderBy("alarm_start_datetime", "DESC")
                ->first();
            if (isset($alarmData["id"])) {


                $currentDateTime = date("Y-m-d H:i:s");;
                $alarm_start_datetime = $alarmData['alarm_start_datetime'];
                //if new Log is available
                $logsNewAlarmInitiated = AlarmLogs::where("serial_number", $device['serial_number'])
                    ->where("company_id", '>', 0)
                    ->where("alarm_status", 0)
                    ->where("verified", false)
                    ->where("zone", $alarmData['zone'])
                    ->where("area", $alarmData['area'])
                    /////->where("time_duration_seconds", "!=", null)
                    //->where("time_duration_seconds", '>', 30)
                    ->where("log_time", '>=',  $alarmData['alarm_start_datetime'])
                    // ->where("log_time", '<=', date("Y-m-d H:i:s", strtotime("-30 seconds")))  //wait for 1 minute to close the Alram
                    ->orderBy("log_time", "DESC")

                    ->first();

                $message["logsNewAlarmInitiated"] = $logsNewAlarmInitiated;


                $logs = null;
                if (isset($logsNewAlarmInitiated['log_time'])) {
                    $dateNewLogTime = $logsNewAlarmInitiated["log_time"];

                    $logs = AlarmLogs::where("serial_number", $device['serial_number'])
                        ->where("company_id", '>', 0)
                        ->where("alarm_status", 0)
                        ->where("zone", $alarmData['zone'])
                        ->where("area", $alarmData['area'])
                        //->where("time_duration_seconds", "!=", null)
                        ->where("log_time", '<=',  $dateNewLogTime)
                        ->where("log_time", '>=',  $alarmData['alarm_start_datetime'])
                        ->orderBy("log_time", "DESC")
                        ->first();


                    //$currentDateTime = $logs['log_time'];


                } else {
                    $logs = AlarmLogs::where("serial_number", $device['serial_number'])
                        ->where("company_id", '>', 0)
                        ->where("alarm_status", 0)
                        ->where("zone", $alarmData['zone'])
                        ->where("area", $alarmData['area'])
                        ///->where("time_duration_seconds", "!=", null)
                        //////// ->where("time_duration_seconds", '<', 30)
                        ->where("log_time", '>=',  $alarmData['alarm_start_datetime'])
                        ->orderBy("log_time", "DESC")
                        ->first();
                    if (isset($logs['log_time'])) {


                        $datetime1 = new DateTime(date("Y-m-d H:i:s"));
                        $datetime2 = new DateTime($logs['log_time']);
                        //   return [$datetime1, $datetime2];
                        $interval = $datetime1->diff($datetime2);
                        $secondsDifference = $interval->s + ($interval->i * 60) + ($interval->h * 3600) + ($interval->days * 86400);
                        // if ($secondsDifference <= 20) {
                        //     $logs = null;
                        // }
                    }
                }




                if (isset($logs['log_time'])) {
                    $message["NewAlarmInitiated"] = $logs['log_time'];

                    $currentDateTime = date("Y-m-d H:i:s");;
                    // $logTime = $logs['log_time'];

                    $datetime1 = new DateTime($logs['log_time']);
                    $datetime2 = new DateTime($alarm_start_datetime);
                    //   return [$datetime1, $datetime2];
                    $interval = $datetime1->diff($datetime2);
                    $secondsDifference = $interval->s + ($interval->i * 60) + ($interval->h * 3600) + ($interval->days * 86400);
                    if ($secondsDifference >= 0)  //close event only afer 60 seconds
                    { //as per cron job have to wait 1 minute

                        $datetime1 = new DateTime($logs['log_time']);
                        $datetime2 = new DateTime($alarmData["alarm_start_datetime"]);

                        $interval = $datetime1->diff($datetime2);

                        $minutesDifference = $interval->i + ($interval->h * 60) + ($interval->days * 1440); // i represents the minutes part of the interval



                        AlarmEvents::where("id",  $alarmData["id"])
                            ->update([
                                "alarm_end_datetime" => $logs['log_time'],
                                "response_minutes" => $minutesDifference,
                                "alarm_status" => 0
                            ]);

                        // AlarmLogs::where("serial_number", $logs['serial_number'])
                        //     ->where("company_id", '>', 0)
                        //     ->where("time_duration_seconds", "!=", null)
                        //     ->where("log_time", '<=', $logs['log_time'])
                        //     ->where("verified", false)->update(["verified" => true]);
                        AlarmLogs::where("id", $logs['id'])->update(["verified" => true]);
                        $data = [
                            "alarm_status" => 0,
                            "alarm_end_datetime" => $logs['log_time'],
                        ];

                        Device::where("serial_number", $logs['serial_number'])->update($data);

                        DeviceZones::where("device_id", $device['id'])
                            ->where("zone_code", $logs['zone'])
                            ->where("area_code", $logs['area'])
                            ->update($data);

                        $this->SendMailWhatsappNotification($logs['alarm_type'], $device['name'] . " - Alarm Stopped ",   $device['name'],  $device, $logs['log_time'],   []);

                        $message[] = "Notification Sent";
                    }
                }
            } else {
                $message[] = "No Log found to close alarm  ";
            }

            // sleep(2);
        } //events

        return $message;
    }
*/
    public function updateAlarmStartDatetime3($devicesList)
    {

        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step6 updateAlarmStartDatetime3 ' . count($devicesList));




        $counter = 0;
        $previousLog = [];
        $currentLog = [];
        foreach ($devicesList as $key => $device) {
            // try {

            $logsArray = AlarmLogs::with(["company", "device.company", "devicesensorzones"])->where("serial_number", $device['serial_number'])
                ->where("company_id", '>', 0)
                ->where("alarm_status", 1)
                //->where("event_code", "!=", null)
                ->where("verified", false)
                ->where("time_duration_seconds", '>=', 5)

                ->orderBy("log_time", "ASC")->get();

            Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step7 updateAlarmStartDatetime3 logsArray count' . count($logsArray));





            foreach ($logsArray  as   $logs) {



                if (isset($logs['log_time'])) {


                    $deviceZone =  DeviceZones::where("device_id", $device['id']);

                    if ($logs['area'] != '') {
                        if ($logs['area'] == '00') {
                            $deviceZone->where("area_code", null);
                        } else
                            $deviceZone->where("area_code", $logs['area']);
                    }

                    $deviceZone->where("zone_code", $logs['zone']);

                    $sensor_name = (clone $deviceZone)->pluck('sensor_name');
                    $sensor_name = $sensor_name[0] ?? null;

                    $sensor_zone_id = (clone $deviceZone)->pluck('id');
                    $sensor_zone_id = $sensor_zone_id[0] ?? null;
                    $alarm_catgory = $logs['alarm_type'] == 'SOS' || $sensor_name == '24H. Zone' || $sensor_name ==  'Emergency zone' ? 1 : 3;


                    // //verify Device , ZOne and Area - Is any alarm already active
                    $activeAlarmZoneCount = AlarmEvents::where("serial_number", $device['serial_number'])
                        ->where("zone", $logs['zone'])
                        ->where("area", $logs['area'])
                        ->where("alarm_type", $logs['alarm_type'])
                        ->where("alarm_status", 1)->count();
                    //Storage::append("testing.txt", $activeAlarmZoneCount);

                    Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step7 activeAlarmZoneCount  ' . $activeAlarmZoneCount);


                    if ($activeAlarmZoneCount == 0) {



                        //verify if OLD alram active count

                        $activeAlarmCount = AlarmEvents::where("serial_number", $device['serial_number'])
                            ->whereDate("alarm_start_datetime", date("Y-m-d"))
                            ->where("alarm_status", 1)->count();

                        if ($activeAlarmCount >= 3) {
                            $alarm_catgory = 1;
                        }

                        $security_name = null;
                        $security_id = null;
                        if ($device->customer && $device->customer->mappedsecurity && $device->customer->mappedsecurity->securityInfo) {
                            $security_name = $device->customer->mappedsecurity->securityInfo->first_name . ' ' . $device->customer->mappedsecurity->securityInfo->last_name;
                            $security_id =  $device->customer->mappedsecurity->securityInfo->id;
                        }

                        $data = [
                            "company_id" => $logs['company_id'],
                            "serial_number" => $logs['serial_number'],
                            "alarm_start_datetime" => $logs['log_time'],
                            "customer_id" => $logs['customer_id'],
                            "zone" => $logs['zone'],
                            "area" => $logs['area'],
                            "alarm_type" => $logs['alarm_type'],
                            "alarm_category" => $alarm_catgory,
                            "sensor_zone_id" => $sensor_zone_id,
                            "alarm_source" => $logs['alarm_source'],
                            "security_name" => $security_name,
                            "security_id" => $security_id,
                            "created_event_from" => "updateAlarmStartDatetime3",
                        ];

                        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step8 data  ' . json_encode($data));


                        $isTechnicianTesting = false;
                        try {
                            if ($device['ticket_id'] > 0) {
                                $isTechnicianTesting = true;
                            }
                        } catch (\Exception $e) {
                        }

                        $eventId = null;
                        if ($isTechnicianTesting) {

                            Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step9 Technician data  ' . json_encode($eventId));


                            $eventId = AlarmEvents::create($data);

                            $technician_id = Tickets::where("id", $device['ticket_id'])->pluck("technician_id")[0];

                            $data = array_merge($data ?? [], [
                                "alarm_status" => 1,
                                "technician_id" => $technician_id,
                                "ticket_id" => $device['ticket_id'] ?? null, // Prevent undefined index error
                            ]);

                            $eventId =  AlarmEventsTechnician::create($data);
                        } else {

                            Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step9 AlarmEvents data  ' . json_encode($eventId));


                            $eventId = AlarmEvents::create($data);
                        }

                        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step9 AlarmEvents data  ' . json_encode($eventId));


                        $this->createAlarmEventsJsonFile($logs['company_id']);

                        AlarmLogs::where("id",   $logs["id"])

                            ->where("verified", false)->update(["verified" => true]);
                        $data = [
                            "alarm_status" => 1,
                            "alarm_start_datetime" => $logs['log_time'],
                            "alarm_end_datetime" => null
                        ];
                        Device::where("serial_number", $logs['serial_number'])->update($data);


                        (clone  $deviceZone)->update($data);
                        Storage::disk('local')->append("notifications/notification_log" . date("Y-m-d") . ".txt", 'Created New Event');

                        if (!$isTechnicianTesting) {


                            $this->SendMailWhatsappNotification($logs['alarm_type'], $device['name'] . " - Alarm Started ",   $device['name'],  $device, $logs['log_time'], false, [], [], $logs);
                        }
                    } else {
                        //Logger::info(" Alarm Log Id " . $logs['id'] . " is already Active.");

                        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", " Alarm Log Id " . $logs['id'] . " is already Active.");
                    }
                } else {
                    Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step7  log_time is not available ');
                }
            }

            // } catch (\Exception $e) {
            // }
        }

        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '**********************************************************************************');
    }

    public function createAlarmEventsJsonFile($companyIdFilter = '')
    {


        $companyIds = Company::when($companyIdFilter != '', function ($query) use ($companyIdFilter) {
            return $query->where('id', $companyIdFilter);
        })->pluck('id');
        foreach ($companyIds as $companyId) {
            // $model = AlarmEvents::with([
            //     "device.customer.primary_contact",
            //     "device.customer.secondary_contact",
            //     "notes",
            //     "category",
            //     "zoneData"
            // ])->where('company_id', $companyId)->where('alarm_status', 1);

            $model = AlarmEvents::with([
                "device.customer" => function ($query) {
                    $query->without(['all_alarm_events', "devices", "contacts", "profile_pictures"]) // Exclude default all_alarm_events relation
                        ->with(['primary_contact', 'secondary_contact']);
                },
                "notes",
                "category",
                "zoneData"
            ])
                ->whereHas('device.customer')
                ->where('company_id', $companyId)
                ->where('alarm_status', 1);



            $model->orderBy("alarm_start_datetime", "DESC");
            $events = $model->get()->makeHidden(['alarm_forwarded']);

            // foreach ($events as $event) {
            //     if ($event->device && $event->device->customer) {
            //         $event->device->customer->makeHidden(['profile_pictures']);
            //     }
            // }

            $jsonFilePath = 'alarm-sensors/' . $companyId . '_live_events.json';


            $updatedJsonData = json_encode($events, JSON_PRETTY_PRINT);

            Storage::put($jsonFilePath, $updatedJsonData);



            if ($companyIdFilter != '')
                return $updatedJsonData;
        }
        //file_put_contents($jsonFilePath, $updatedJsonData);
    }

    public function stopOldAlarms($device)
    {
        try {

            $message = [];

            if ($device['alarm_status'] == 1) {

                $logs = AlarmLogs::where("serial_number", $device['serial_number'])
                    ->where("company_id", '>', 0)

                    ->orderBy("log_time", "DESC")
                    ->first();
                if (isset($logs['log_time'])) {

                    $datetimeC = date("Y-m-d H:i:s");
                    $datetime1 = new DateTime($datetimeC);
                    $datetime2 = new DateTime($logs['log_time']);
                    //   return [$datetime1, $datetime2];
                    $interval = $datetime1->diff($datetime2);
                    $message[] = $secondsDifference = $interval->s + ($interval->i * 60) + ($interval->h * 3600) + ($interval->days * 86400);
                    if ($secondsDifference >= 60) {


                        $alarmData =  AlarmEvents::where("serial_number", $device['serial_number'])
                            ->where("alarm_end_datetime", null)
                            ->orderBy("alarm_start_datetime", "DESC")
                            ->first();
                        if (isset($alarmData["alarm_start_datetime"])) {
                            $datetime1 = new DateTime($datetimeC);
                            $datetime2 = new DateTime($alarmData["alarm_start_datetime"]);

                            $interval = $datetime1->diff($datetime2);

                            $minutesDifference = $interval->i + ($interval->h * 60) + ($interval->days * 1440); // i represents the minutes part of the interval

                            AlarmEvents::where("id", $alarmData['id'])
                                ->update([
                                    "alarm_end_datetime" => $datetimeC,
                                    "alarm_status" => 0,
                                    "response_minutes" => $minutesDifference
                                ]);

                            AlarmLogs::where("serial_number", $device['serial_number'])
                                ->update([
                                    "alarm_status" => 0
                                ]);
                        }
                        Device::where("serial_number", $device['serial_number'])->update(["alarm_status" => 0, "alarm_end_datetime" => $datetimeC]);

                        // $this->SendMailWhatsappNotification($device['name'] . " - Alarm Stopped ",   $device['name'],  $device, $datetimeC, true);
                    }
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    /* -----------------------------------------*/
    public function AlarmLogs(Request $request)
    {
        Storage::append("logs/alarm/api-alarm-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") .  " : "    . json_encode($request->all()));

        $log_time = date('Y-m-d H:i:s');

        $device_serial_number = $request->serial_number;
        $alarm_status = 0;
        $alarm_type = '';

        $area = '';
        $zone = '';


        if ($device_serial_number != '' && $request->filled("alarm_type")) {
            $logs = [];
            $logs['serial_number'] = $request->serial_number;

            $logs['log_time'] = $log_time;
            if ($request->filled("alarm_status")) {
                $logs['alarm_status'] = $request->alarm_status;
            }
            if ($request->filled("alarm_type")) {
                $logs['alarm_type'] = $request->alarm_type;
            }
            if ($request->filled("area")) {
                $logs['area'] = $request->area;
            }
            if ($request->filled("zone")) {
                $logs['zone'] = $request->zone;
            }

            if ($logs['zone'] != '') {
                $insertedRecord = AlarmLogs::create($logs);

                $deviceModel = Device::where("serial_number", $device_serial_number)->first();
                if ($deviceModel) {
                    $company_id = $deviceModel->company_id;
                    $customer_id = $deviceModel->customer_id;
                    $device_time_zone = $deviceModel->utc_time_zone;

                    $log_time = new DateTime($log_time);
                    $log_time->setTimezone(new DateTimeZone($device_time_zone));

                    $data = [
                        "company_id" => $company_id,
                        "customer_id" => $customer_id,
                        "log_time" => $log_time->format('Y-m-d H:i:s')
                    ];
                    AlarmLogs::where("id", $insertedRecord["id"])->update($data);

                    try {
                        $this->updateAlarmResponseTime();
                    } catch (\Exception $e) {
                    }
                    return $this->response('Alarm Logs are created', null, true);
                }
            } else
                return $this->response('Zone is empty. Alarm Logs is not inserted', null, true);
        } else {
            return $this->response('Device Serial Number or Alarm Type is empty', null, false);
        }
    }
    /*
    public function ApiTemperatureLogs(Request $request)
    {



        $message = [];

        //try {
        Storage::append("logs/alarm/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") .  " : "    . json_encode($request->all()));

        $temperature = -1;
        $humidity = -1;


        $log_time = date('Y-m-d H:i:s');

        $max_temperature = '';
        $max_humidity = '';



        $device_serial_number = $request->serial_number;


        if ($device_serial_number != '') {



            if ($request->filled("temperature")) {
                $temperature = $request->temperature == 'NaN' ? 0 : $request->temperature;
                //$temperature = (float) $request->temperature;

            }
            if ($request->filled("humidity")) {
                $humidity = $request->humidity == 'NaN' ? 0 : $request->humidity;
            }


            if ($temperature == "NaN") {
                $temperature = 0;
            }
            if ($temperature == "nan") {
                $temperature = 0;
            }
            if (strtolower($temperature) == "nan") {
                $temperature = 0;
            }
            if (strtolower($humidity) == "nan") {
                $temperature = 0;
            }
            if ($humidity == "NaN") {
                $humidity = 0;
            }




            $logs["serial_number"] = $device_serial_number;
            $logs["temperature"] = $temperature;

            $logs["humidity"] = $humidity;

            $logs["log_time"] = $log_time;

            $insertedRecord = AlarmDeviceTemperatureLogs::create($logs);

            //(new AlarmDeviceTemperatureLogsController)->updateCompanyIds();

            $deviceModel = Device::where("serial_number", $device_serial_number);

            $deviceObj = $deviceModel->clone()->get();
            //update device live status
            $deviceModel->clone()->update(["status_id" => 1, "last_live_datetime" => date("Y-m-d H:i:s")]);
            if (count($deviceObj) == 0) {
                return $this->response('Device Information is not available', null, false);
            }
            $name = $deviceModel->clone()->first()->name;
            $deviceObj = $deviceObj[0];

            try {
                //update company_id;
                AlarmDeviceTemperatureLogs::where("id", $insertedRecord["id"])->update(["company_id" => $deviceObj["company_id"]]);

                if ($deviceModel->first()) {
                    $company_id = $deviceModel->company_id;
                    $customer_id = $deviceModel->customer_id;
                    $device_time_zone = $deviceModel->utc_time_zone;
                    $log_time = new DateTime($log_time);
                    $log_time->setTimezone(new DateTimeZone($device_time_zone));

                    $data = ["company_id" => $company_id, "customer_id" => $customer_id, "log_time" => $log_time->format('Y-m-d H:i:s')];
                    AlarmDeviceTemperatureLogs::where("id", $insertedRecord["id"])->update($data);
                    $this->response('Alarm Logs are created', null, true);
                }
            } catch (\Exception $e) {
            }

            $deviceModel = Device::where("serial_number", $device_serial_number);
            //notifications
            if ($deviceObj['threshold_temperature'] > 0 && $temperature != 'nan') {

                $temperature = floatval($temperature);

                if ($temperature >= $deviceObj['threshold_temperature']) {

                    $ignore15Minutes = false;


                    if ($deviceObj['temperature_alarm_status'] == 0) {
                        $row = [];
                        $row["temperature_alarm_status"] = 1;
                        $row["temperature_alarm_start_datetime"] = $log_time;
                        $row["temperature_alarm_end_datetime"] = null;
                        $deviceModel->clone()->update($row);
                        $ignore15Minutes = true;
                    }

                    return  $message[] =  $this->SendMailWhatsappNotification(
                        "Temperature",
                        $name . " -   Temperature Alarm is  ON",
                        $name,
                        $deviceModel->clone()->first(),
                        $log_time,

                        $ignore15Minutes,
                        ["temperature" => $temperature, "max_temperature" => $deviceObj['threshold_temperature'], $deviceObj]
                    );
                } else {



                    if ($deviceObj['temperature_alarm_status'] == 1) {
                        $ignore15Minutes = true;
                        $message[] =  $this->SendMailWhatsappNotification("Temperature", $name . " -  Temperature Alarm is  OFF",   $name, $deviceModel->clone()->first(), $log_time, $ignore15Minutes, ["temperature" => $temperature, "max_temperature" => $deviceObj['threshold_temperature']]);
                        $row = [];
                        $row["temperature_alarm_status"] = 0;

                        $row["temperature_alarm_end_datetime"] = $log_time;
                        $deviceModel->clone()->update($row);
                    }
                }
            }

            $row = [];



            return $this->response('Successfully Updated', null, true);
        } else {
            return $this->response('Device Serial Number is empty', null, false);
        }
        // } catch (\Exception $e) {
        //     Storage::append("logs/alarm_error/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") .  " : "    . json_encode($request->all()) . ' \n' . json_encode($e));

        //     return  $e->getMessage();
        // }

        return $this->response('Data error', null, false);
    }
*/
    public function SendMailWhatsappNotification($alrm_type, $issue, $room_name, $model1, $date,  $ignore15Minutes, $tempArray = [], $deviceObj = [], $alarmlog = null)
    {
        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", " step10 SendMailWhatsappNotification started");

        if ($alarmlog == null)
            return false;
        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '- Notification step1');


        $company_id = $model1->company_id;


        $reports = DeviceNotificationsManagers::with(["company.company_mail_content"])
            ->where("company_id", $company_id)
            ->where("customer_id", $alarmlog['customer_id'])
            ->where("zone_name", $alrm_type)
            ->get();
        //Storage::append("testing.txt", 'reports' . json_encode($reports));

        $contacts = CustomerContacts::where("company_id", $company_id)

            ->where("customer_id", $alarmlog['customer_id'])
            ->where(function ($query) {
                $query->whereRaw("address_type ILIKE 'primary'")
                    ->orWhereRaw("address_type ILIKE 'secondary'");
            })
            ->get()->toArray();;

        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-Step2 - count ' . count($contacts));

        foreach ($contacts as $key => $contact) {
            $reports->push([
                "name" => $contact["first_name"] . ' ' . $contact["last_name"],
                "company" => $alarmlog["company"], // ["name" => $alarmlog["company"]["name"]],
                "device" =>  $alarmlog["device"],
                "devicesensorzones" =>  $alarmlog["devicesensorzones"] ??  null,
                "zone_name" => null,
                "email" => $contact["email"],
                "company_id" => $company_id,
                "branch_id" => null,
                "id" => null,
                "whatsapp_number" =>  $contact["whatsapp"],
            ]);
        }

        $issue = $alarmlog['alarm_type'];
        foreach ($reports as $value) {

            if (!isset($value['device']['customer'])) {
                continue;
            }


            $location = "{$value['device']['customer']['latitude']},{$value['device']['customer']['longitude']}";

            Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-Step3 - Email ' . $value["email"]);
            if ($value["email"] != '') {




                $body_content1 = "Hello, {$value['name']} <br/><br/>";
                $body_content1 .= "Company:  {$value['company']['name']}<br/><br/>";
                $body_content1 .= "Event ID:  #{$alarmlog['id']}<br/>";
                $body_content1 .= "Customer:  {$value['device']['customer']['building_name']}<br/>";
                $body_content1 .= "This is Notifing you about {$issue} event <br/>";
                $body_content1 .= "DateTime:  {$alarmlog['log_time']}<br/>";



                $body_content1 .= "Address: {$value['device']['customer']['house_number']}<br/>";
                $body_content1 .= "City: {$value['device']['customer']['city']}<br/>";

                if (isset($value['devicesensorzones'])) {
                    $body_content1 .= "Sensor: {$value['devicesensorzones']['sensor_type']}<br/>";
                    $body_content1 .= "Location: {$value['devicesensorzones']['location']}<br/>";
                }
                $body_content1 .= "Google Map Link:  https://maps.google.com/?q={$location}<br/> ";


                // $body_content1 .= "Branch: {$branch_name}<br/><br/><br/><br/>";
                $body_content1 .= "*Xtreme Guard*<br/>";

                $data = [
                    'subject' => "{$issue} Alarm Notification #{$alarmlog['id']}",
                    'body' => $body_content1,
                ];
                Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-Step4 - Email Body' . $body_content1);

                $body_content1 = new EmailContentDefault($data);

                if ($value['email'] != '') {

                    try {
                        // Mail::to($value['email'])
                        //     ->send($body_content1);

                        Mail::to($value['email'])->queue($body_content1);


                        //send mail from mytime2cloud
                        $data["to"] = $value['email'];
                        (new ClientController())->sendExternalMail($data);
                    } catch (\exception $e) {
                    }

                    $data = [
                        "company_id" => $value['company_id'],
                        "branch_id" => $value['branch_id'],
                        "notification_manager_id" => $value['id'],
                        "email" => $value['email'],
                        "subject" => $issue,
                        "notification_id" => 0,
                        "created_datetime" => date("Y-m-d H:i:s"),
                    ];

                    ReportNotificationLogs::create($data);
                }
            } else {
                Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-Step3 - Email Not available' . $value["email"]);
            }

            Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-Step4 - Whatsapp  ' . $value['whatsapp_number']);

            if ($value['whatsapp_number'] != '') {

                // $branch_name = $value->branch->branch_name ?? '---';

                $body_content1 = "🚨 *{$issue}* Event Notification 🚨\n\n";
                $body_content1 .= "Company:  {$value['company']['name']}\n";

                $body_content1 .= "Hello, *{$value['name']}*\n";
                $body_content1 .= "Event ID:  *#{$alarmlog['id']}*\n";

                $body_content1 .= "Customer:  {$value['device']['customer']['building_name']}\n";

                $body_content1 .= "Alarm Type:  $issue\n";

                // $body_content1 .= "This is Notifing you about *{$issue}* event <br/>";
                $body_content1 .= "DateTime:  {$alarmlog['log_time']}\n";



                $body_content1 .= "Address: {$value['device']['customer']['house_number']}\n";
                $body_content1 .= "City: {$value['device']['customer']['city']}\n";



                if (isset($value['devicesensorzones'])) {
                    $body_content1 .= "Sensor: {$value['devicesensorzones']['sensor_type']}\n";
                    $body_content1 .= "Location: {$value['devicesensorzones']['location']}\n";
                }
                $body_content1 .= "Google Map Link:  https://maps.google.com/?q={$location}\n\n ";

                // $body_content1 .= "Branch:  {$branch_name}\n";
                $body_content1 .= "From *Xtreme Guard*\n";


                Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-Step5 - Whatsapp Body ' . $body_content1);

                // if (isset($value['company']['company_whatsapp_content']))
                //     $body_content1 .= $value['company']['company_whatsapp_content'][0]['content'];
                try {
                    $response = (new WhatsappController())->sendWhatsappNotification($value['company'], $body_content1, $value['whatsapp_number'], []);


                    Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-Final - Whatsapp Response ' . $response);
                } catch (\exception $e) {
                }

                $data = [
                    "company_id" => $value['company']['id'],
                    //"branch_id" => $manager['branch_id,
                    // "notification_id" => $value['notification_id,
                    "notification_manager_id" => $value['id'],
                    "whatsapp_number" => $value['whatsapp_number'],
                    "subject" => $issue,
                    "notification_id" => 0,
                ];

                ReportNotificationLogs::create($data);
            } else {
                Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-Step5 - Whatsapp Not available');
            }
        } //whatsapp

        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", '*******************************************************************************');
    }

    public function SendMailWhatsappNotification5MinutesDelay($alrm_type, $issue, $room_name, $model1, $date,  $ignore15Minutes, $tempArray = [], $deviceObj = [])
    {


        $company_id = $model1->company_id;
        $branch_id = $model1->branch_id;

        //$reports = ReportNotification::where("company_id", $model1->company_id)->where("branch_id", $model1->branch_id)->get();

        return   $reports = DeviceNotificationsManagers::with(["company.company_mail_content"])
            ->where("company_id", $company_id)
            ->where("zone_name", $alrm_type)

            ->where("zone_name", $alrm_type)


            ->get();

        foreach ($reports as $value) { {
                $minutesDifference = 1000;

                //wait 5 minutes to send notification
                $notificationSentLogs = ReportNotificationLogs::where("notification_id", $value->notification_id)
                    ->where("notification_manager_id", $value->id)
                    ->where("email", $value->email)
                    ->where("subject", $issue)

                    ->orderBy("created_at", "DESC")->first();

                if ($notificationSentLogs) {
                    $datetime1 = new DateTime(date("Y-m-d H:i"));
                    $datetime2 = new DateTime($notificationSentLogs["created_at"]);

                    $interval = $datetime1->diff($datetime2);
                    $minutesDifference =  $interval->i + ($interval->h * 60) + ($interval->days * 1440);
                }


                if ($minutesDifference >=   15 || $ignore15Minutes) { //




                    $branch_name = $value->branch->branch_name ?? '---';

                    $body_content1 = "📊 *{$issue} Notification <br/>";

                    $body_content1 = " Hello, {$value->name} <br/>";
                    $body_content1 .= " Company:  {$value->company->name}<br/>";
                    $body_content1 .= "This is Notifing you about {$issue} status <br/>";

                    if (isset($tempArray["temperature"])) {
                        if ($tempArray["temperature"] != 'nan°C') {
                            $body_content1 .= "Temperature:  {$tempArray["temperature"]}°C<br/>";
                        }
                    }
                    if (isset($tempArray["max_temperature"])) {
                        $body_content1 .= "Threshold:  {$tempArray["max_temperature"]}<br/>";
                    }

                    $body_content1 .= "Date:  $date<br/>";
                    $body_content1 .= "Zone Name: {$value->zone_name}<br/>";
                    // $body_content1 .= "Branch: {$branch_name}<br/><br/><br/><br/>";
                    $body_content1 .= "*Xtreme Guard*<br/>";

                    $data = [
                        'subject' => "{$issue} Notification",
                        'body' => $body_content1,
                    ];


                    $body_content1 = new EmailContentDefault($data);

                    if ($value->email != '') {
                        Mail::to($value->email)
                            ->queue($body_content1);




                        $data = [
                            "company_id" => $value->company_id,
                            "branch_id" => $value->branch_id,
                            "notification_manager_id" => $value->id,
                            "email" => $value->email,
                            "subject" => $issue,
                            "notification_id" => 0,
                            "created_datetime" => date("Y-m-d H:i:s"),
                        ];

                        ReportNotificationLogs::create($data);
                    }
                }
            }
            // } else {
            //     echo "[" . $date . "] Cron: $script_name. No emails are configured";
            // }

            //whatsapp with attachments
            /*
            // if (in_array("Whatsapp", $model->mediums))
            {

                // foreach ($model->managers as $key => $manager)
                {
                    $minutesDifference = 1000; //minutes
                    //wait 5 minutes to send notification
                    $notificationSentLogs = ReportNotificationLogs::where("notification_manager_id", $value->id)
                        ->where("subject", $issue)
                        ->where("whatsapp_number", $value->whatsapp_number)
                        ->orderBy("created_at", "DESC")->first();
                    $minutesDifference = 1000; //minutes
                    if ($notificationSentLogs) {
                        $datetime1 = new DateTime(date("Y-m-d H:i"));
                        $datetime2 = new DateTime($notificationSentLogs["created_at"]);
                        $interval = $datetime1->diff($datetime2);
                        $minutesDifference =  $interval->i + ($interval->h * 60) + ($interval->days * 1440);
                    }



                    if ($minutesDifference >=   15   || $ignore15Minutes) { //

                        if ($value->whatsapp_number != '') {

                            // $branch_name = $value->branch->branch_name ?? '---';

                            $body_content1 = "📊 *{$issue}* Notification  📊\n\n";

                            $body_content1 .= "Hello, *{$value->name}*\n\n";
                            $body_content1 .= "Company:  {$value->company->name}\n\n";
                            $body_content1 .= "This is Notifing you about *{$issue}* status \n\n";
                            if (isset($tempArray["temperature"])) {
                                if ($tempArray["temperature"] != 'nan°C') {
                                    $body_content1 .= "Temperature:  {$tempArray["temperature"]}°C\n\n";
                                }
                            }
                            if (isset($tempArray["max_temperature"])) {
                                $body_content1 .= "*Threshold:  {$tempArray["max_temperature"]}°C*\n\n";
                            }
                            $body_content1 .= "Date:  $date\n";
                            $body_content1 .= "Room Name:  {$room_name}\n";

                            // $body_content1 .= "Branch:  {$branch_name}\n";
                            $body_content1 .= "*Xtreme Guard*\n";




                            if (count($value->company->company_whatsapp_content))
                                $body_content1 .= $value->company->company_whatsapp_content[0]->content;

                            (new WhatsappController())->sendWhatsappNotification($value->company, $body_content1, $value->whatsapp_number, []);

                            $data = [
                                "company_id" => $value->company->id,
                                //"branch_id" => $manager->branch_id,
                                // "notification_id" => $value->notification_id,
                                "notification_manager_id" => $value->id,
                                "whatsapp_number" => $value->whatsapp_number,
                                "subject" => $issue,
                                "notification_id" => 0,
                            ];

                            ReportNotificationLogs::create($data);
                        }
                    }
                }
            }*/
        }
    }
}

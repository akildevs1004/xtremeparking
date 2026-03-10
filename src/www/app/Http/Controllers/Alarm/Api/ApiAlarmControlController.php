<?php

namespace App\Http\Controllers\Alarm\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Models\Device;
use App\Models\Alarm\DeviceSensorLogs;
use App\Models\ReportNotification;
use App\Models\ReportNotificationLogs;
use App\Jobs\MailJob;
use App\Mail\EmailContentDefault;
use App\Http\Controllers\WhatsappController;
use App\Models\DeviceTemperatureLogs;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Cache;

//timestamp
class ApiAlarmControlController extends Controller
{
    public function LogDeviceStatus(Request $request)
    {
        try {

            $serial = $request->serialNumber ?? $request->deviceID ?? '';


            // if ($request->timestamp) {
            //     $alarmTimestamp = Cache::get("device_alarm_timestamp_$serial");
            //     if ($alarmTimestamp && $alarmTimestamp == $request->timestamp) {

            //         return "Already Timestamp is Exist";
            //     }

            //     Cache::put("device_alarm_timestamp_$serial", $request->timestamp, now()->addMinutes(1));
            // }
            Storage::append("logs/alarm/api-requests-device-" . date('Y-m-d') . ".txt", "---------------------------------------------------------------------");
            //if ($request->type == 'alarm')
            Storage::append("logs/alarm/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") . " Request Data : " . json_encode($request->all()));

            $serial = null; //$request->serialNumber ?? $request->deviceID ?? '';

            if ($request->filled("serialNumber")) {
                $serial = $request->serialNumber;
            } else if ($request->filled("deviceID")) {
                $serial =  $request->deviceID;
            }
            if (!$serial) {
                Storage::append("logs/alarm/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") . " : " . 'Serial Number Missing');

                return $this->response('Serial Number Missing', null, false);
            }

            $log_time = now()->format('Y-m-d H:i:s');
            $temperature = $this->sanitizeSensorValue($request->temperature);
            $humidity = $this->sanitizeSensorValue($request->humidity);
            // reuqest alarm query strings Keys
            $alarmData = [
                'fire_alarm'       => $request->fire_alarm ?? $request->fire,
                'smoke_alarm'      => $request->smoke_alarm ?? $request->smokeStatus,
                'water_leakage'    => ($serial === '24000001') ? 0 :   $request->waterLeakage ?? $request->waterLeakage,
                'power_failure'    =>
                $request->acPowerFailure ?? $request->acPowerFailure,
                'door_status'      =>
                $request->doorOpen ?? $request->doorOpen,
                'temperature_alarm'      => $request->temperature_alarm ?? $request->temperature_alarm,
                'humidity_alarm'      => $request->humidity_alarm,




            ];


            Storage::append("logs/alarm/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") . " Alarm Data :  " . json_encode($alarmData));


            if ($request->wifiipaddress) {
                Device::where("serial_number", $serial)
                    ->update([
                        "wifiipaddress" => $request->wifiipaddress,
                        "wifissid" => $request->wifissid
                    ]);
            }

            $deviceModel = Device::where("serial_number", $serial);
            $device = $deviceModel->first();
            if (!$device) {
                Storage::append("logs/alarm/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") . " : " . 'Device not registered');


                return $this->response('Device not found', null, false);
            }


            $deviceModel->update(["status_id" => 1, "last_live_datetime" => now()]);

            $messages = [];

            if ($temperature != 0 && $humidity != 0) {
                //record temeprature logs
                $log = [
                    'company_id' => $device->company_id,
                    'serial_number' => $serial,
                    'device_id' => $device->id,
                    'log_time' => $log_time,
                    'temparature' => $temperature,
                    'humidity' => $humidity,
                    'temperature_serial_address' => $request->sensor_serial_address ?? null,
                    "temperature_alarm" => $request->temperature_alarm ?? null,
                    "humidity_alarm" => $request->humidity_alarm ?? null

                ];

                DeviceTemperatureLogs::create($log);
            }

            $logType = $request->type ?? $request->type;
            //  if ($logType == "alarm")
            {

                //ONLY FOR ALARAM -------------------------------------------
                // Reusable alarm handler

                //device_sensor_logs table columns
                foreach (
                    [
                        'temperature_alarm' => 'Temperature Alarm',
                        'humidity_alarm' => 'Humidity Alarm',

                        'fire_alarm'        => 'Fire Alarm',
                        'smoke_alarm'       => 'Smoke Alarm',
                        'water_leakage'     => 'Water Leakage Alarm',
                        'power_failure'     => 'Power Failure Alarm',
                        'door_status'       => 'Door Open Alarm',
                    ] as $key => $label
                ) {

                    $deviceTakeColumn = "";
                    $value = $alarmData[$key] ?? null;



                    if (!is_null($value)) {

                        $logIdModel = $this->logAlarmEvent(
                            $device,
                            $serial,
                            $key,
                            $value,
                            $log_time,
                            ['temparature' => $temperature, 'humidity' => $humidity, 'temperature_serial_address' => $request->sensor_serial_address],
                            $request
                        );

                        if ($key === 'temperature_alarm') {
                            $deviceTakeColumn = "temparature_alarm_status";
                        }
                        if ($key === 'water_leakage') {
                            $deviceTakeColumn = "water_alarm_status";
                        }
                        if ($key === 'fire_alarm') {
                            $deviceTakeColumn = "fire_alarm_status";
                        }
                        if ($key === 'power_failure') {
                            $deviceTakeColumn = "power_alarm_status";
                        }
                        if ($key === 'door_status') {
                            $deviceTakeColumn = "door_open_status";
                        }
                        if ($key === 'smoke_alarm') {
                            $deviceTakeColumn = "smoke_alarm_status";
                        }


                        $logId = $logIdModel["id"];
                        if ($value)
                            Storage::append("logs/alarm/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") . " : " . $key . "-" . $deviceTakeColumn . " -   Alarm Created #" . $logId);
                        else Storage::append("logs/alarm/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") . " : " . $key . "-" . $deviceTakeColumn . " -  Alarm Closed " . $logId);

                        $messages[] = $logId;


                        // $deviceTakeColumn = $key === 'temperature_alarm' ? 'temparature_alarm_status' : "{$key}_status";




                        $messages[] = $this->deviceAlarmTable(
                            $deviceModel,
                            $device,
                            $deviceTakeColumn,
                            $value,
                            $log_time,
                            $label,
                            $logId,
                            $temperature,
                            $device->temperature_threshold
                        );


                        // Storage::append("logs/alarm/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") . " : " . $key . "-" . $deviceTakeColumn . " -   Alarm Created #" . json_encode($messages));


                    } else {
                        // Storage::append("logs/alarm/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") . $key . " : Value is null");
                    }
                }

                return  $messages;
            } //type alarm

            return $this->response('Successfully Updated', $messages, true);
        } catch (\Exception $e) {

            Cache::forget("device_alarm_timestamp_$serial");

            Storage::append("logs/alarm_error/api-requests-device-" . date('Y-m-d') . ".txt", now() . " : " . json_encode($request->all()) . "\n" . $e);
            return $this->response('Server Error', $e->getMessage(), false);
        }
    }

    private function sanitizeSensorValue($value)
    {
        return (strtolower($value) === 'nan' || $value === 'NaN') ? 0 : floatval($value);
    }

    private function deviceAlarmTable($deviceModel, $device, $field, $value, $log_time, $label, $logId, $temperature = null, $threshold = null)
    {
        $status = $device->$field;
        $startKey = str_replace('_status', '_start_datetime', $field);
        $endKey = str_replace('_status', '_end_datetime', $field);

        $row = [];
        $ignore = false;
        //return $value . "-" . $status;



        if ($value == 1 && $status == 0) {
            $row[$field] = 1;
            $row[$startKey] = $log_time;
            $row[$endKey] = null;
            if ($logId)
                $row["device_sensor_logs_id"] = $logId;
            $deviceModel->update($row);
            $ignore = true;
            return $this->SendWhatsappNotification("{$label} is ON", $device->name, $device, $log_time, $ignore, [
                "temparature" => $temperature,
                "max_temparature" => $threshold
            ]);
        } elseif ($value == 0 && $status == 1) {
            $row[$field] = 0;
            $row[$endKey] = $log_time;
            $deviceModel->update($row);
            $ignore = true;
            return $this->SendWhatsappNotification("{$label} is OFF", $device->name, $device, $log_time, $ignore, [
                "temparature" => $temperature,
                "max_temparature" => $threshold
            ]);
        }

        return [$value . "-" . $status . '-' . $field, "Notificaiton is not sent"];
    }

    private function SendWhatsappNotification($issue, $room_name, $device, $date, $ignore, $tempArray = [])
    {
        $reports = ReportNotification::with(['managers.branch', 'company.company_mail_content', 'company.company_whatsapp_content'])
            ->where('company_id', $device->company_id)
            // ->where('branch_id', $device->branch_id)
            ->get();

        $message = [];

        $message[] = $reports;

        foreach ($reports as $model) {
            foreach ($model->managers as $manager) {
                $lastLog = ReportNotificationLogs::where([
                    ['notification_id', $manager->notification_id],
                    ['notification_manager_id', $manager->id],
                    ['subject', $issue],
                ])->latest()->first();

                $minutes = 1000;
                if ($lastLog) {
                    $interval = (new DateTime(now()))->diff(new DateTime($lastLog->created_at));
                    $minutes = $interval->i + $interval->h * 60 + $interval->days * 1440;
                }

                if ($minutes >= 5 || $ignore) {
                    $body = $this->buildMessage($issue, $manager->name, $model->company->name, $room_name, $manager->branch->branch_name, $date, $tempArray);

                    $whatsappMessage = $body["whatsapp"];
                    $mailMessage = $body["mail"];


                    if (in_array('Email', $model->mediums) && $manager->email) {
                        MailJob::dispatch(["email" => $manager->email, "body_content" => new EmailContentDefault([
                            'subject' => "{$issue} Notification" . " at " . $date,
                            'body' => $mailMessage
                        ])]);

                        $message[] = "Email Notification sent to " . $manager->email;
                    }

                    if (in_array('Whatsapp', $model->mediums) && $manager->whatsapp_number) {
                        (new WhatsappController)->sendWhatsappNotification($model->company, $whatsappMessage, $manager->whatsapp_number, []);

                        $message[] = "Email Notification sent to " . $manager->whatsapp_number;
                    }

                    ReportNotificationLogs::create([
                        "company_id" => $model->company_id,
                        "branch_id" => $manager->branch_id,
                        "notification_id" => $manager->notification_id,
                        "notification_manager_id" => $manager->id,
                        "subject" => $issue,
                        "email" => $manager->email,
                        "whatsapp_number" => $manager->whatsapp_number,
                    ]);
                }
            }
        }

        return $message;
    }
    private function logAlarmEvent($device, $serialNumber, $alarmType, $status,   $logTime = null, $extraData = [], $request)
    {
        $logTime = $logTime ?? now();


        //logs

        $log = [
            'company_id' => $device->company_id,
            'serial_number' => $serialNumber,
            'device_id' => $device->id,
            'alarm_type' => $alarmType,
            $alarmType => 1,
            'alarm_status' => $status,
            'log_time' => $logTime,
            'temperature_min' => $request->temperature_min ?? null,
            'temperature_max' => $request->temperature_max ?? null,
            'humidity_min' => $request->humidity_min ?? null,
            'humidity_max' =>  $request->humidity_max ?? null,

        ];

        if ($status == 1) {

            $lastOpen = DeviceSensorLogs::where('serial_number', $serialNumber)
                ->where('alarm_type', $alarmType)
                ->where('alarm_status', 1)

                ->when(
                    $alarmType === "temperature_alarm",
                    fn($query) => $query->where('temperature_serial_address', $extraData["temperature_serial_address"] ?? null)
                )

                ->whereNull('alarm_end_datetime')
                ->latest('alarm_start_datetime')
                ->first();

            if (!$lastOpen) { //create new only if alarm is closed




                // Alarm ON — Create new log
                $log['alarm_start_datetime'] = $logTime;

                // Add sensor readings or extra alarm data (temperature, humidity, etc.)
                $log += array_filter($extraData, fn($v) => !is_null($v));

                try {
                    $record = DeviceSensorLogs::create($log);

                    return ["id" => $record->id, "message" => ""];
                } catch (\Exception $e) {


                    return ["id" => null, "message" => $e->getMessage()];

                    return $e->getMessage();
                    // \Log::error("Alarm ON log creation failed", [
                    //     'device' => $device->id,
                    //     'type' => $alarmType,
                    //     'error' => $e->getMessage(),
                    //     'data' => $log
                    // ]);
                }
            } else {
                return ["id" => null, "message" => "Already Alarm Is open "];

                return "Already Alarm Is open ";
            }
        } elseif ($status == 0) {






            $now = Carbon::now();

            $alarms = DeviceSensorLogs::where('alarm_type', $alarmType)
                ->where('alarm_status', 1)
                // ->whereNull('alarm_end_datetime')


                ->when(
                    $alarmType === "temperature_alarm",
                    fn($query) => $query->where('temperature_serial_address', $extraData["temperature_serial_address"] ?? null)
                )


                ->get();

            $closedCount = 0;

            foreach ($alarms as $alarm) {
                $responseMinutes = $alarm->alarm_start_datetime
                    ? $now->diffInMinutes(Carbon::parse($alarm->alarm_start_datetime))
                    : null;

                try {
                    $alarm->update([
                        'alarm_status' => 0,
                        'alarm_end_datetime' => $now,
                        'response_minutes' => $responseMinutes,
                    ]);
                    $closedCount++;
                } catch (\Exception $e) {
                    // \Log::error("Failed to close {$alarmType} alarm ID {$alarm->id}", [
                    //     'error' => $e->getMessage(),
                    // ]);
                }
            }

            return [
                "id" => null,
                'status' => true,
                'message' => "Closed $closedCount '$alarmType' alarms where status = 0 and end time is.",
            ];



            // // Alarm OFF — Update the last open record
            // $lastOpen = DeviceSensorLogs::where('serial_number', $serialNumber)
            //     ->where('alarm_type', $alarmType)
            //     ->where('alarm_status', 1)
            //     ->where('temperature_serial_address', $extraData["temperature_serial_address"])

            //     ->whereNull('alarm_end_datetime')
            //     ->latest('alarm_start_datetime')
            //     ->first();

            // if ($lastOpen) {
            //     $responseMinutes = now()->diffInMinutes($lastOpen->alarm_start_datetime);

            //     try {
            //         $lastOpen->update([
            //             'alarm_status' => 0,
            //             'alarm_end_datetime' => $logTime,
            //             'response_minutes' => $responseMinutes,
            //             // Optionally update temp/humidity at close time
            //         ] + array_filter($extraData, fn($v) => !is_null($v)));
            //     } catch (\Exception $e) {
            //         // \Log::error("Alarm OFF update failed", [
            //         //     'device' => $device->id,
            //         //     'type' => $alarmType,
            //         //     'error' => $e->getMessage()
            //         // ]);
            //     }

            //     return $lastOpen->id;
            // }
        }
    }

    private function buildMessage($issue, $managerName, $companyName, $roomName, $branchName, $date, $tempArray = [])
    {
        $msg = "📊 *{$issue}* Notification  📊\n\n";
        $msg .= "Hello, *{$managerName}*\n\n";
        $msg .= "Company: {$companyName}\n";
        $msg .= "Room: {$roomName}\n";
        $msg .= "Branch: {$branchName}\n";
        $msg .= "Date: {$date}\n";
        if (!empty($tempArray["temparature"])) {
            $msg .= "Temperature: {$tempArray["temparature"]}°C\n";
        }
        if (!empty($tempArray["max_temparature"])) {
            $msg .= "Threshold: {$tempArray["max_temparature"]}°C\n";
        }
        $msg .= "*XtremeGuard*\n";


        $Mailmsg = "<strong>{$issue} Notification  </strong><br/>";
        $Mailmsg .= "Hello, *{$managerName}<br/>";
        $Mailmsg .= "Company: {$companyName}<br/>";
        $Mailmsg .= "Room: {$roomName}<br/>";
        $Mailmsg .= "Branch: {$branchName}<br/>";
        $Mailmsg .= "Date: {$date}<br/>";
        if (!empty($tempArray["temparature"])) {
            $Mailmsg .= "Temperature: {$tempArray["temparature"]}°C<br/>";
        }
        if (!empty($tempArray["max_temparature"])) {
            $Mailmsg .= "Threshold: {$tempArray["max_temparature"]}°C<br/>";
        }
        $Mailmsg .= "<br/><br/>From,<br/>XtremeGuard<br/>";
        return ["whatsapp" => $msg, "mail" => $Mailmsg];
    }
}

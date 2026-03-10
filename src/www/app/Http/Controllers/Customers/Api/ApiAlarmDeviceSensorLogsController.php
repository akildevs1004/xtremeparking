<?php

namespace App\Http\Controllers\Customers\Api;


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
use App\Models\Deivices\DeviceZones;
use App\Models\Device;
use App\Models\DeviceArmedLogs;
use App\Models\DeviceNotificationsManagers;
use App\Models\DeviceSensorTypes;
use App\Models\ReportNotification;
use App\Models\ReportNotificationLogs;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Replace;

class ApiAlarmDeviceSensorLogsController extends Controller
{

    // public function verifyHeartbeat()
    // {
    //     $datetime = date("Y-m-d H:i:s", strtotime("-60 minutes"));
    //     $query1 = Device::query()

    //         ->where(function ($query) use ($datetime) {

    //             $query->where('last_live_datetime', '<=', $datetime);
    //             $query->orWhere('last_live_datetime',    null);
    //         })
    //         ->update(["status_id" => 2]);
    // }
    public function getCSVFileLines($date)
    {


        $csvPath = "app/alarm-sensors/sensor-logs-$date.csv"; // The path to the file relative to the "Storage" folder

        $fullPath = storage_path($csvPath);

        if (!file_exists($fullPath)) {
            return ["error" => true, "message" => "File doest not exist on $date."];
        }

        $file = fopen($fullPath, 'r');

        $data = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!count($data)) {
            return ["error" => true, "message" => 'File is empty.'];
        }



        $previoulyAddedLineNumbers =  Storage::get(("alarm-sensors/sensor-logs-count-$date.txt")) ?? 0;

        // return $this->getMeta("Sync Attenance Logs", $previoulyAddedLineNumbers . "\n");

        $totalLines = count($data);

        $currentLength = 0;

        if ($previoulyAddedLineNumbers == $totalLines) {
            return ["error" => true, "message" => 'No new data found.'];
        } else if ($previoulyAddedLineNumbers > 0 && $totalLines > 0) {
            $currentLength = $previoulyAddedLineNumbers;
        }

        fclose($file);

        $result = [
            "date" => $date,
            "totalLines" => $totalLines,
            "data" => array_slice($data, $currentLength, 20)

        ];

        if (array_key_exists("error", $result)) {
            return $this->getMeta("Sync Attenance Logs", $result["message"] . "\n");
        }

        $result["data"] = array_values(array_unique($result["data"]));

        return $result;
    }
    public function readCSVLogFile()
    {
        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step1 readCSVLogFile');

        ///set_time_limit(30); // Timeout after 30 seconds

        $date = date("d-m-Y");
        $results = $this->getCSVFileLines($date);

        $records = [];

        $message = [];
        if (isset($results["data"])) {
            foreach ($results["data"] as $row) {


                $columns = explode(',', $row);


                $serial_number = $columns[0];
                $event = $columns[1];
                $log_time = $columns[2];

                // $unique_code = $columns[3];
                // $unique_code = $columns[3];

                $zone = '';
                $area = '';
                if (isset($columns[5])) {
                    if ($columns[4] != '')
                        $area = trim($columns[4]);
                    if ($columns[5] != '')
                        $zone =  trim($columns[5]);

                    $zone = str_replace('"', '', $zone);
                    $zone = str_replace("'", '', $zone);

                    $area = str_replace('"', '', $area);
                    $area = str_replace("'", '', $area);
                }


                $alarm_type = '';
                $alarm_source = 'Sensor';

                //3401 00 000 / HOME
                Device::where("serial_number", $serial_number)->update(
                    ["status_id" => 1, "last_live_datetime" => $log_time]
                );

                $this->closeOfflineAlarmsBySerialNumber($serial_number);

                $message[] = $this->getMeta("Device HeartBeat", $log_time . "<br/>\n");


                //-----------Alarm Control panel - Wifi Model

                if ($event == 'HEARTBEAT' || $event == '1351') {
                    Device::where("serial_number", $serial_number)->update(
                        ["status_id" => 1, "last_live_datetime" => $log_time]
                    );

                    $this->closeOfflineAlarmsBySerialNumber($serial_number);

                    $message[] = $this->getMeta("Device HeartBeat", $log_time . "<br/>\n");
                }


                if ($event == '1401' || $event == '1406' || $event == '1407'  || $event == '1455' || $event == '1137') //disarm button  // 1401,000=device //1407=remote //1406
                {

                    $data = [
                        "alarm_status" => 0,
                        "alarm_end_datetime" => $log_time,
                        "armed_status" => 0,
                        "armed_datetime" => $log_time
                    ];
                    Device::where("serial_number", $serial_number)->update($data);
                    $this->endAllAlarmsBySerialNumber($serial_number, $log_time);


                    //update armed log
                    $armedRow = ["disarm_datetime" => $log_time];
                    $record = DeviceArmedLogs::where("serial_number", $serial_number)
                        ->where("disarm_datetime", null)
                        ->orderBy("armed_datetime", "desc")
                        ->first();
                    if ($record) {
                        $record->update($armedRow);
                    }
                    $this->updateDisarmTableCompanyLogs();
                    $message[] = $this->getMeta("Device Disarm", $log_time . "<br/>\n");
                } else if ($event == '3407'   || $event == '3401') //armed button   //device=3401,000 //3407,001=remote
                {
                    Device::where("serial_number", $serial_number)->update(["armed_status" => 1, "armed_datetime" => $log_time]);

                    //create log
                    $armedRow = ["serial_number" => $serial_number, "armed_datetime" => $log_time];
                    $record = DeviceArmedLogs::create($armedRow);
                    $message[] = $this->getMeta("Device Armed", $log_time . "<br/>\n");
                } else if ($zone != '' && $event != '3401' && $zone != '141') //zone verification button
                {


                    $devices = Device::where('serial_number', $serial_number)->first();;
                    if ($devices) {
                        $alarm_type = $devices->device_type ?? '';
                        $device_model = $devices->model_number ?? '';

                        if ($device_model == 'H700-TAB') //H700 Tab
                        {
                            if ($event == '1100') {
                                $alarm_type = 'SOS';
                            } else if ($event == '1130') {
                                $alarm_type = 'Tampered';
                            }
                        } else  if ($device_model == 'XG-808') //XTream Box
                        {

                            if ($event == '1120') {
                                $alarm_type = 'SOS';
                            } else  if ($event == '1133') {
                                $alarm_type = '24Hours';
                            } else  if ($event == '1137') {
                                $alarm_type = 'Tampered';
                            } else if ($event == '1301') {
                                $alarm_type = 'AC_off';
                            } else if ($event == '1309') {
                                $alarm_type = 'DC_off';
                            }
                            if ($event == '3301') {
                                $alarm_type = '';
                                //AC_Power Recovery
                                $this->closeACOffAlarmsBySerialNumber($serial_number);
                            }
                            if ($event == '3309') {
                                $alarm_type = '';
                                //AC_Power Recovery
                                $this->closeDCOffAlarmsBySerialNumber($serial_number);
                            }
                            // if ($event == '1132') {
                            //     $alarm_type = 'Regular Alarm';
                            // }
                            //1301 - AC Loss
                            //1309 - Battery Loss
                            //1321 - Restart Started
                            //1351 - Restart End and System is on// Battery Loss
                            //3301 - AC Recovery
                            //3309 - DC Recovery

                            //1406 - disam
                            //1455 - disam

                        }

                        //Identify Alarm Type based on zone and sensor type value

                        $deviceId = $devices->id;
                        $areaTesting = $area == '00' ? null : $area;

                        $sensorType = DeviceZones::where("device_id", $deviceId)
                            ->where("area_code", $areaTesting)
                            ->where("zone_code", $zone)
                            ->pluck("sensor_type");

                        if (isset($sensorType[0])) {
                            // $alarmTypes = [
                            //     'Water Leakage Sensor' => 'Water',
                            //     'Fire Sensor' => 'Fire',
                            //     'Medical Sensor' => 'Medical',
                            //     'SOS Sensor' => 'SOS',
                            //     'Temperature Sensor' => 'Temperature',
                            //     'Gas Sensor' => 'Gas',

                            // ];

                            $alarmTypes = DeviceSensorTypes::pluck("alarm_type", "name");


                            if (!empty($sensorType) && isset($alarmTypes[$sensorType[0]]))
                                $alarm_type = $alarmTypes[$sensorType[0]];
                        }

                        //--------------------------------------

                        Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step2 alarm_type ' . $alarm_type . "-" . $event  . "-" . $zone);


                        //$area =   $devices->area_code ?? '';
                        if ($alarm_type != '' && $event != '' && $zone != '') {

                            $count = AlarmLogs::where("serial_number", $serial_number)->where("log_time", $log_time)->where("zone", $zone)->where("area", $area)->count();


                            Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step2 alarmcount ' . $count);


                            if ($count == 0) {
                                $records  = [
                                    "serial_number" => $serial_number,
                                    "log_time" => $log_time,
                                    "alarm_status" => 1,
                                    "alarm_type" => $alarm_type,
                                    "area" => $area,
                                    "zone" => $zone,
                                    "alarm_source" => $alarm_source,
                                    "event_code" => $event,
                                ];

                                $insertedRecord = AlarmLogs::create($records);
                                Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step2 insertedRecord');


                                $message[] =  $this->getMeta("New Alarm Log Is interted With zone", $log_time . "<br/>\n");
                                $this->updateCompanyIds($insertedRecord, $serial_number, $log_time);
                            } else {

                                Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step2 alarmcount ' . "Alarm Already Exist", $log_time);
                                $message[] =  $this->getMeta("Alarm Already Exist", $log_time . "<br/>\n");
                            }
                        }
                    } //device
                } else if ($zone == ''   && $event != '') {

                    Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step2 Zone Empty');

                    // $devices = Device::where('serial_number', $serial_number)->first();;

                    // $alarm_type = $devices->device_type ?? '';
                    // //$area =   $devices->area_code ?? '';
                    // if ($alarm_type != '') {
                    //     $count = AlarmLogs::where("serial_number", $serial_number)->where("log_time", $log_time)->count();
                    //     if ($count == 0) {
                    //         $records  = [
                    //             "serial_number" => $serial_number,
                    //             "log_time" => $log_time,
                    //             "alarm_status" => 1,
                    //             "alarm_type" => $alarm_type,
                    //             "alarm_source" => $alarm_source,

                    //         ];

                    //         $insertedRecord = AlarmLogs::create($records);
                    //         $message[] =  $this->getMeta("New Alarm Log Is interted without Zone", $log_time . "<br/>\n");
                    //         $this->updateCompanyIds($insertedRecord, $serial_number, $log_time);
                    //     } else {
                    //         $message[] =  $this->getMeta("Alarm Already Exist", $log_time . "<br/>\n");
                    //     }
                    // }
                } else {
                    $message[] =  $this->getMeta("Information Is not availalbe<br/>", $row);


                    Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step3 Information Is not availalbe');
                }

                //----------Update Alarm Duration
                Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step3  Update Alarm Duration');
                (new ApiAlarmDeviceTemperatureLogsController)->updateAlarmResponseTime();

                $device = Device::where("serial_number", $serial_number)->first();
                if ($device) (new ApiAlarmDeviceTemperatureLogsController)->createAlarmEventsJsonFile($device->company_id);
            }

            //update company ids armed logs



            // try {
            Storage::put("alarm-sensors/sensor-logs-count-" . $date . ".txt", $results['totalLines']);
            try {
                $this->updateArmedTableCompanyLogs();
            } catch (\Exception $e) {
            }


            Storage::disk('local')->append("notifications/notification_log_" . date("Y-m-d") . ".txt", now() . '-   step2  Logs  message' . json_encode($message));

            return $this->getMeta("Sync Attenance Logs", count($message) . json_encode($message));
            //    // } catch (\Throwable $th) {

            //         Storage::append("alarm-sensors/error-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") .  " : "  . "\n" . json_encode($th)  . json_encode($th));

            //         return $this->getMeta("Sync Attenance Logs", " Error occured." . "\n");
            //     }

        } else {
            return $this->getMeta("Sync Alarm Sensor Logs", " 0 records found " . "<br/>\n");
        }
    }
    public function endAllAlarmsBySerialNumber($serial_number, $log_end_datetime)
    {

        $alarmActiveEvents = AlarmEvents::where("serial_number", $serial_number)->where("alarm_status", 1)->get();
        //turn off all alarms
        foreach ($alarmActiveEvents  as $key => $event) {
            $datetime1 = new DateTime($log_end_datetime);
            $datetime2 = new DateTime($event["alarm_start_datetime"]);

            $interval = $datetime1->diff($datetime2);

            $minutesDifference = $interval->i + ($interval->h * 60) + ($interval->days * 1440); // i represents the minutes part of the interval

            AlarmEvents::where("id",  $event["id"])
                ->update([
                    "alarm_end_datetime" => $log_end_datetime,
                    "response_minutes" => $minutesDifference,
                    "alarm_status" => 0
                ]);
        }

        AlarmEventsTechnician::where("serial_number",  $serial_number)->where("alarm_status",  1)
            ->update([
                "alarm_end_datetime" => $log_end_datetime,

                "alarm_status" => 0
            ]);

        //turnoff device alarmlogs status
        if ($serial_number != '') {
            $alarm_event_active_count = AlarmEvents::where("serial_number", $serial_number)->where("alarm_status", 1)->count();
            if ($alarm_event_active_count == 0) {
                $device_Data = [];
                $device_Data["alarm_status"] = 0;
                $device_Data["alarm_end_datetime"] = $log_end_datetime;

                Device::where("serial_number", $serial_number)->update($device_Data);
            }
            AlarmLogs::where("serial_number", $serial_number)->where("alarm_status", 1)
                ->update([

                    "alarm_status" => 0
                ]);
        }
    }

    public function updateArmedTableCompanyLogs()
    {

        $logs = DeviceArmedLogs::where("company_id", null)->get();

        foreach ($logs as $key => $log) {



            $device = Device::where("serial_number", $log->serial_number)->first();
            $timeZone = $device?->utc_time_zone ?: 'Asia/Dubai';
            $armed_datetime = new DateTime($log->armed_datetime);
            $armed_datetime->setTimezone(new DateTimeZone($timeZone));

            $data = [
                "company_id" => $device->company_id,
                "armed_datetime" => $armed_datetime->format('Y-m-d H:i:s')
            ];
            DeviceArmedLogs::where("id",  $log->id)->update($data);
        }
    }
    public function updateDisarmTableCompanyLogs()
    {

        $logs = DeviceArmedLogs::where("duration_in_minutes", null)->get();

        foreach ($logs as $key => $log) {

            $device = Device::where("serial_number", $log->serial_number)->first();
            if ($device) {


                $timeZone = $device?->utc_time_zone ?: 'Asia/Dubai';
                $disarm_datetime = new DateTime($log->disarm_datetime);
                $disarm_datetime->setTimezone(new DateTimeZone($timeZone));


                $datetime1 = new DateTime($log->armed_datetime);
                $datetime2 = $disarm_datetime;
                $interval = $datetime1->diff($datetime2);
                $minutesDifference = $interval->i + ($interval->h * 60) + ($interval->days * 1440); // i represents the minutes

                $data = [
                    "duration_in_minutes" => $minutesDifference,
                    "disarm_datetime" => $disarm_datetime->format('Y-m-d H:i:s')
                ];
                DeviceArmedLogs::where("id",  $log->id)->update($data);
            }
        }
    }
    public function updateCompanyIds($insertedRecord, $serial_number, $log_time)
    {
        $deviceModel = Device::where("serial_number", $serial_number)->first();
        if ($deviceModel) {
            $company_id = $deviceModel->company_id;
            $customer_id = $deviceModel->customer_id;

            $timeZone = $deviceModel?->utc_time_zone ?: 'Asia/Dubai';




            $log_time = new DateTime($log_time);
            $log_time->setTimezone(new DateTimeZone($timeZone));

            $data = [
                "company_id" => $company_id,
                "customer_id" => $customer_id,
                "log_time" => $log_time->format('Y-m-d H:i:s')
            ];
            AlarmLogs::where("id", $insertedRecord["id"])->update($data);


            return $this->response('Alarm Logs are created', null, true);
        }
    }
    public function  closeDCOffAlarmsBySerialNumber($serial_number)
    {
        $alarmEvents = AlarmEvents::where('serial_number', $serial_number)->where("alarm_type", "DC_off")->where("alarm_status", 1)->get();

        foreach ($alarmEvents as $alarm) {

            $timeZone = $alarm->device->utc_time_zone ?: 'Asia/Dubai';
            $nowInTimeZone = Carbon::now($timeZone);
            $alarmStartDatetime = Carbon::parse($alarm->alarm_start_datetime);
            $minutesDifference = $alarmStartDatetime->diffInMinutes($nowInTimeZone);

            if ($alarm->alarm_type == "DC_off") {
                $alarm->update([
                    "alarm_end_datetime" => $nowInTimeZone,
                    "response_minutes" => $minutesDifference,
                    "alarm_status" => 0
                ]);
            }
        }

        (new ApiAlarmDeviceTemperatureLogsController())->createAlarmEventsJsonFile();
    }
    public function  closeACOffAlarmsBySerialNumber($serial_number)
    {
        $alarmEvents = AlarmEvents::where('serial_number', $serial_number)->where("alarm_type", "AC_off")->where("alarm_status", 1)->get();

        foreach ($alarmEvents as $alarm) {

            $timeZone = $alarm->device->utc_time_zone ?: 'Asia/Dubai';
            $nowInTimeZone = Carbon::now($timeZone);
            $alarmStartDatetime = Carbon::parse($alarm->alarm_start_datetime);
            $minutesDifference = $alarmStartDatetime->diffInMinutes($nowInTimeZone);

            if ($alarm->alarm_type == "AC_off") {
                $alarm->update([
                    "alarm_end_datetime" => $nowInTimeZone,
                    "response_minutes" => $minutesDifference,
                    "alarm_status" => 0
                ]);
            }
        }

        (new ApiAlarmDeviceTemperatureLogsController())->createAlarmEventsJsonFile();
    }
    public function  closeOfflineAlarmsBySerialNumber($serial_number)
    {
        $alarmEvents = AlarmEvents::where('serial_number', $serial_number)->where("alarm_type", "Offline")->where("alarm_status", 1)->get();

        foreach ($alarmEvents as $alarm) {

            $timeZone = $alarm->device->utc_time_zone ?: 'Asia/Dubai';
            $nowInTimeZone = Carbon::now($timeZone);
            $alarmStartDatetime = Carbon::parse($alarm->alarm_start_datetime);
            $minutesDifference = $alarmStartDatetime->diffInMinutes($nowInTimeZone);

            if ($alarm->alarm_type == "Offline") {
                $alarm->update([
                    "alarm_end_datetime" => $nowInTimeZone,
                    "response_minutes" => $minutesDifference,
                    "alarm_status" => 0
                ]);
            }
        }

        (new ApiAlarmDeviceTemperatureLogsController())->createAlarmEventsJsonFile();
    }
}

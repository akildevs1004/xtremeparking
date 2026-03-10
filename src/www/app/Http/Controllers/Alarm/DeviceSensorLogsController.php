<?php

namespace App\Http\Controllers\Alarm;

use App\Http\Controllers\Controller;
use App\Models\Alarm\DeviceSensorLogs as AlarmDeviceSensorLogs;
use App\Models\Company;
use App\Models\Device;
use App\Models\DeviceSensorLogs;
use App\Models\DeviceTemperatureLogs;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Type\Integer;

class DeviceSensorLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {}
    public function getTemperatureLogs(Request $request)
    {
        $model = DeviceTemperatureLogs::with(["device", "deviceTemperatureSensor"])->where("company_id", $request->company_id)
            ->where("company_id", $request->company_id);
        //->where("temparature", "!=", null);

        if ($request->filled("device_serial_number")) {
            $model->where("serial_number", $request->device_serial_number);
        }

        if ($request->filled("device_temperature_serial_address")) {
            $model->where("temperature_serial_address", $request->device_temperature_serial_address);
        }
        if ($request->filled("from_date")) {
            $model->where("log_time", '>=', $request->from_date . ' 00:00:00');
        }
        if ($request->filled("to_date")) {
            $model->where("log_time", '<=', $request->to_date . ' 23:59:59');
        }
        // if ($request->filled("only_temperature") && $request->only_temperature == 'true') {
        //     $model->where("temparature", "!=", null);
        //     $model->where("temparature", "!=", 0);
        //     $model->where("humidity", "!=", 0);
        // }



        // { name: `Smoke  Only`, value: `1` },
        // { name: `Water  Only`, value: `2` },
        // { name: `Door  Only`, value: `3` },
        // { name: `C Power  Only`, value: `4` },

        $model->when(
            $request->filled('sortBy'),
            function ($q) use ($request) {
                $sortDesc = $request->input('sortDesc'); {
                    $q->orderBy($request->sortBy . "", $sortDesc == 'true' ? 'desc' : 'asc'); {
                    }
                }
            }
        );

        if (!$request->sortBy) {
            $model->orderBy('log_time', 'DESC');
        }



        return    $model->paginate($request->per_page);
    }

    public function getAlarmLogs(Request $request)
    {
        $model = AlarmDeviceSensorLogs::with(["device", "deviceTemperatureSensor"])->where("company_id", $request->company_id)
            ->where("company_id", $request->company_id);
        //->where("temparature", "!=", null);

        if ($request->filled("device_serial_number")) {
            $model->where("serial_number", $request->device_serial_number);
        }

        if ($request->filled("device_temperature_serial_address")) {
            $model->where("temperature_serial_address", $request->device_temperature_serial_address);
        }
        if ($request->filled("from_date")) {
            $model->where("log_time", '>=', $request->from_date . ' 00:00:00');
        }
        if ($request->filled("to_date")) {
            $model->where("log_time", '<=', $request->to_date . ' 23:59:59');
        }
        // if ($request->filled("only_temperature") && $request->only_temperature == 'true') {
        //     $model->where("temparature", "!=", null);
        //     $model->where("temparature", "!=", 0);
        //     $model->where("humidity", "!=", 0);
        // }


        if ($request->filled("filter_alarm_status")) {
            if ($request->filter_alarm_status == 1) {
                $model->where(function ($query) use ($request) {

                    $query->where("water_leakage", 1);
                    $query->orWhere("temperature_alarm", 1);
                    $query->orWhere("power_failure",  1);
                    $query->orWhere("door_status",  1);
                    $query->orWhere("smoke_alarm",  1);
                    $query->orWhere("fire_alarm",  1);
                });
            } else if ($request->filter_alarm_status == 0) {
                $model->where(function ($query) use ($request) {
                    $query->orWhere("temperature_alarm", 0);
                    $query->where("water_leakage", 0);
                    $query->Where("power_failure",  0);
                    $query->Where("door_status",  0);
                    $query->Where("smoke_alarm",  0);
                    $query->Where("fire_alarm",  0);
                });
            } else  if ($request->filter_alarm_status == 2) {
                $model->where("fire_alarm", 1);
            } else  if ($request->filter_alarm_status == 3) {
                $model->where("water_leakage", 1);
            } else  if ($request->filter_alarm_status == 4) {
                $model->where("door_status", 1);
            } else  if ($request->filter_alarm_status == 5) {
                $model->where("power_failure", 1);
            } else  if ($request->filter_alarm_status == 6) {
                $model->where("smoke_alarm", 1);
            }
        }
        // { name: `Smoke  Only`, value: `1` },
        // { name: `Water  Only`, value: `2` },
        // { name: `Door  Only`, value: `3` },
        // { name: `C Power  Only`, value: `4` },

        $model->when(
            $request->filled('sortBy'),
            function ($q) use ($request) {
                $sortDesc = $request->input('sortDesc'); {
                    $q->orderBy($request->sortBy . "", $sortDesc == 'true' ? 'desc' : 'asc'); {
                    }
                }
            }
        );

        if (!$request->sortBy) {
            $model->orderBy('log_time', 'DESC');
        }

        $model->orderBy('log_time', 'DESC');


        // return $model->toSql();


        return    $model->paginate($request->per_page);
    }

    public function getDeliveLogs(Request $request)
    {
        $model = AlarmDeviceSensorLogs::with(["device", "deviceTemperatureSensor"])->where("company_id", $request->company_id)
            ->where("company_id", $request->company_id);
        //->where("temparature", "!=", null);

        if ($request->filled("device_serial_number")) {
            $model->where("serial_number", $request->device_serial_number);
        }

        if ($request->filled("device_temperature_serial_address")) {
            $model->where("temperature_serial_address", $request->device_temperature_serial_address);
        }
        if ($request->filled("from_date")) {
            $model->where("log_time", '>=', $request->from_date . ' 00:00:00');
        }
        if ($request->filled("to_date")) {
            $model->where("log_time", '<=', $request->to_date . ' 23:59:59');
        }
        // if ($request->filled("only_temperature") && $request->only_temperature == 'true') {
        //     $model->where("temparature", "!=", null);
        //     $model->where("temparature", "!=", 0);
        //     $model->where("humidity", "!=", 0);
        // }


        if ($request->filled("filter_alarm_status")) {
            if ($request->filter_alarm_status == 1) {
                $model->where(function ($query) use ($request) {

                    $query->where("water_leakage", 1);
                    $query->orWhere("temperature_alarm", 1);
                    $query->orWhere("power_failure",  1);
                    $query->orWhere("door_status",  1);
                    $query->orWhere("smoke_alarm",  1);
                });
            } else if ($request->filter_alarm_status == 0) {
                $model->where(function ($query) use ($request) {
                    $query->orWhere("temperature_alarm", 0);
                    $query->where("water_leakage", 0);
                    $query->Where("power_failure",  0);
                    $query->Where("door_status",  0);
                    $query->Where("smoke_alarm",  0);
                });
            } else  if ($request->filter_alarm_status == 2) {
                $model->where("smoke_alarm", 1);
            } else  if ($request->filter_alarm_status == 3) {
                $model->where("water_leakage", 1);
            } else  if ($request->filter_alarm_status == 4) {
                $model->where("door_status", 1);
            } else  if ($request->filter_alarm_status == 5) {
                $model->where("power_failure", 1);
            }
        }
        // { name: `Smoke  Only`, value: `1` },
        // { name: `Water  Only`, value: `2` },
        // { name: `Door  Only`, value: `3` },
        // { name: `C Power  Only`, value: `4` },

        $model->when(
            $request->filled('sortBy'),
            function ($q) use ($request) {
                $sortDesc = $request->input('sortDesc'); {
                    $q->orderBy($request->sortBy . "", $sortDesc == 'true' ? 'desc' : 'asc'); {
                    }
                }
            }
        );

        if (!$request->sortBy) {
            $model->orderBy('log_time', 'DESC');
        }



        return    $model->paginate($request->per_page);
    }

    public function getDeviceMonthlyHumidityTemperature(Request $request)
    {





        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // 1. First get your actual data
        $dailyAverages = DeviceTemperatureLogs::selectRaw("DATE(log_time) as date,
    (ROUND(AVG(CAST(temparature AS FLOAT)) * 100) / 100) as avg_temperature,
    (ROUND(AVG(CAST(humidity AS FLOAT)) * 100) / 100) as avg_humidity")
            ->where('company_id', $request->company_id)
            ->where('serial_number', $request->device_serial_number)
            ->where(function ($query) {
                $query->whereNotNull('temparature')
                    ->whereNotNull('humidity')
                    ->where('temparature', '!=', 'NaN')
                    ->where('humidity', '!=', 'NaN');
            })
            ->whereBetween('log_time', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay()
            ])

            ->when($request->filled("device_temperature_serial_address") && $request->device_temperature_serial_address > 0, function ($query) use ($request) {


                $query->where("temperature_serial_address",   $request->device_temperature_serial_address);
            })
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date'); // Key results by date for easier merging

        // 2. Create complete date range
        $period = CarbonPeriod::create($fromDate, $toDate);
        $completeDates = collect($period->toArray())->mapWithKeys(function ($date) {
            return [$date->format('Y-m-d') => [
                'date' => $date->format('Y-m-d'),
                'avg_temperature' => 0,
                'avg_humidity' => 0
            ]];
        });

        // 3. Merge and fill missing dates
        $result = $completeDates->map(function ($defaultData, $date) use ($dailyAverages) {
            return $dailyAverages->has($date)
                ? $dailyAverages->get($date)
                : (object)$defaultData;
        })->values();

        return $result;

        //     $fromDate = '2025-05-01';
        //     $toDate = '2025-05-10';

        //     return  $dailyAverages = AlarmDeviceSensorLogs::selectRaw(
        //         "DATE(log_time) as date,
        // AVG(CAST(temparature AS FLOAT)) as avg_temperature,
        // AVG(CAST(humidity AS FLOAT)) as avg_humidity"
        //     )
        //         ->where('company_id', $request->company_id)
        //         ->where('serial_number', $request->device_serial_number)
        //         ->whereNotNull('temparature')
        //         ->whereNotNull('humidity')
        //         ->where('temparature', '!=', 'NaN')
        //         ->where('humidity', '!=', 'NaN')
        //         ->whereBetween('log_time', [
        //             Carbon::createFromFormat('Y-m-d', $fromDate)->startOfDay(),
        //             Carbon::createFromFormat('Y-m-d', $toDate)->endOfDay()
        //         ])
        //         ->groupBy('date')
        //         ->orderBy('date')
        //         ->get();
    }

    public function getDeviceTodayHourlyHumidityTemperature(Request $request)
    {
        $date = date('Y-m-d');
        if ($request->filled("from_date")) {
            $date = $request->from_date;
        }
        $TemperatureData = $this->getTemparatureHourlyData($request->company_id, $request->device_serial_number, $date, $request);
        $HumidityData = $this->getHumidityHourlyData($request->company_id, $request->device_serial_number, $date, $request);


        return [

            "temperature" => $TemperatureData,
            "humidity" => $HumidityData


        ];
    }
    public function getDeviceTodayHourlyTemperature(Request $request)
    {

        $date = date('Y-m-d');
        if ($request->filled("from_date")) {
            $date = $request->from_date;
        }
        $HouryData = $this->getTemparatureHourlyData($request->company_id, $request->device_serial_number, $date, $request);

        return [

            "houry_data" => $HouryData
        ];
    }
    public function getDeviceTodayHourlyHumidity(Request $request)
    {

        $date = date('Y-m-d');
        if ($request->filled("from_date")) {
            $date = $request->from_date;
        }
        $HouryData = $this->getHumidityHourlyData($request->company_id, $request->device_serial_number, $date, $request);

        return [

            "houry_data" => $HouryData
        ];
    }
    public function getDeviceLatestTemperature(Request $request)
    {
        $temperature_latest = '--';
        $temperature_date_time = '--';
        $temperature_min = '--';
        $temperature_max = '--';
        $temperature_min_date_time = '--';
        $temperature_max_date_time = '--';
        $fire_alarm_start_datetime = '--';
        $smoke_open_start_datetime = '--';
        $water_alarm_start_datetime = '--';
        $power_alarm_start_datetime = '--';
        $door_open_start_datetime = '--';

        $temparature_alarm_status = '--';
        $fire_alarm_status = '--';
        $smoke_alarm_status = '--';
        $water_alarm_status = '--';
        $power_alarm_status = '--';




        $humidity_latest = '--';
        $humidity_date_time = '--';
        $humidity_min = '--';
        $humidity_max = '--';
        $humidity_min_date_time = '--';
        $humidity_max_date_time = '--';



        $date = date('Y-m-d');


        $model = DeviceTemperatureLogs::where("company_id", $request->company_id)
            // ->where("serial_number", $request->device_serial_number)
            ->where("serial_number", $request->device_serial_number)
            ->where("temparature", '!=', null)
            ->where("temparature", '>', 0)
            ->where("temparature",  "!=", "NaN")

            ->when($request->filled("device_temperature_serial_address") && $request->device_temperature_serial_address > 0, function ($query) use ($request) {


                $query->where("temperature_serial_address",   $request->device_temperature_serial_address);
            })



            // ->whereDate("log_time", $date)
            ->orderBy("log_time", "DESC")
            ->first();


        if ($model) {
            $temperature_latest = $model->temparature;
            $temperature_date_time = $model->log_time;

            $humidity_latest = $model->humidity;
            $humidity_date_time = $model->log_time;
        }

        //----
        $model =   DeviceTemperatureLogs::where("company_id", $request->company_id)
            ->where("serial_number", $request->device_serial_number)
            ->when($request->filled("device_temperature_serial_address") && $request->device_temperature_serial_address > 0, function ($query) use ($request) {


                $query->where("temperature_serial_address",   $request->device_temperature_serial_address);
            })
            ->whereDate("log_time", $date);
        $temperature  = $model->clone()->where(
            'temparature',
            '=',
            DeviceTemperatureLogs::where("company_id", $request->company_id)
                ->where("serial_number", $request->device_serial_number)
                ->where("temparature", "!=", "0.0")
                ->where("temparature", "!=", "NaN")
                ->when($request->filled("device_temperature_serial_address") && $request->device_temperature_serial_address > 0, function ($query) use ($request) {


                    $query->where("temperature_serial_address",   $request->device_temperature_serial_address);
                })
                ->whereDate("log_time", $date)->min('temparature')

        )->first();
        if ($temperature) {
            $temperature_min = $temperature->temparature;
            $temperature_min_date_time = $temperature->log_time;
        }
        //----------------
        $temperature =  $temperature  = $model->clone()->where(
            'temparature',
            '=',
            DeviceTemperatureLogs::where("company_id", $request->company_id)
                ->where("serial_number", $request->device_serial_number)
                ->where("temparature", "!=", "0.0")
                ->where("temparature", "!=", "NaN")->when($request->filled("device_temperature_serial_address") && $request->device_temperature_serial_address > 0, function ($query) use ($request) {
                    $query->where("temperature_serial_address",   $request->device_temperature_serial_address);
                })
                ->whereDate("log_time", $date)->max('temparature')

        )->first();
        if ($temperature) {
            $temperature_max = $temperature->temparature;
            $temperature_max_date_time = $temperature->log_time;
        }

        //-------Humidity
        //----
        $model =   DeviceTemperatureLogs::where("company_id", $request->company_id)
            ->where("serial_number", $request->device_serial_number)
            ->where("humidity", '>', 0)
            ->where("humidity", "!=", "NaN")
            ->when($request->filled("device_temperature_serial_address") && $request->device_temperature_serial_address > 0, function ($query) use ($request) {
                $query->where("temperature_serial_address",   $request->device_temperature_serial_address);
            })
            ->whereDate("log_time", $date);

        $humidity  = $model->clone()->where(
            'humidity',
            '=',
            DeviceTemperatureLogs::where("company_id", $request->company_id)
                ->where("serial_number", $request->device_serial_number)
                ->where("humidity", "!=", "0.0")
                ->where("humidity", "!=", "NaN")
                ->when($request->filled("device_temperature_serial_address") && $request->device_temperature_serial_address > 0, function ($query) use ($request) {
                    $query->where("temperature_serial_address",   $request->device_temperature_serial_address);
                })
                ->whereDate("log_time", $date)->min('humidity')
        )->first();
        if ($humidity) {
            $humidity_min = $humidity->humidity;
            $humidity_min_date_time = $humidity->log_time;
        }
        //----------------
        $humidity =  $humidity  = $model->clone()->where(
            'humidity',
            '=',
            DeviceTemperatureLogs::where("company_id", $request->company_id)
                ->where("serial_number", $request->device_serial_number)
                ->where("humidity", "!=", "0.0")
                ->where("humidity", "!=", "NaN")
                ->when($request->filled("device_temperature_serial_address") && $request->device_temperature_serial_address > 0, function ($query) use ($request) {
                    $query->where("temperature_serial_address",   $request->device_temperature_serial_address);
                })
                ->whereDate("log_time", $date)->max('humidity')
        )->first();
        if ($humidity) {
            $humidity_max = $humidity->humidity;
            $humidity_max_date_time = $humidity->log_time;
        }


        //last alarm

        $DeviceArray = Device::where("company_id", $request->company_id)
            ->where("serial_number", $request->device_serial_number)
            ->get();
        $device = [];
        if (isset($DeviceArray[0])) {
            $fire_alarm_start_datetime = $DeviceArray[0]->fire_alarm_start_datetime;
            $device = $DeviceArray[0];
        }
        return [
            "temperature_latest" =>   $temperature_latest,
            "temperature_date_time" => $temperature_date_time,
            "temperature_min" => $temperature_min,
            "temperature_max" => $temperature_max,
            "temperature_min_date_time" => $temperature_min_date_time,
            "temperature_max_date_time" => $temperature_max_date_time,



            "humidity_latest" =>   $humidity_latest,
            "humidity_date_time" => $humidity_date_time,
            "humidity_min" => $humidity_min,
            "humidity_max" => $humidity_max,
            "humidity_min_date_time" => $humidity_min_date_time,
            "humidity_max_date_time" => $humidity_max_date_time,
            "device" => $device,


        ];
    }
    public function getHumidityHourlyData($company_id, $device_serial_number, $date, $request)
    {
        $finalArray = [];

        // Assuming $date is defined somewhere before this loop
        // $date = date('Y-m-d');

        // Fetch hourly averages in a single query
        $hourlyAverages = DeviceTemperatureLogs::selectRaw("EXTRACT(HOUR FROM log_time) AS hour, AVG(humidity) AS avg_humidity")


            ->where('company_id', $company_id)
            ->where('serial_number', $device_serial_number)
            ->where('humidity', '!=', 'NaN')
            ->where('humidity', '!=', null)

            ->when($request->filled("device_temperature_serial_address") && $request->device_temperature_serial_address > 0, function ($query) use ($request) {
                $query->where("temperature_serial_address",   $request->device_temperature_serial_address);
            })
            ->whereDate('log_time', $date)
            ->groupBy('hour')
            ->get();

        // Initialize $finalArray with default values
        for ($i = 0; $i < 24; $i++) {
            $finalArray[] = [
                "date" => $date,
                "hour" => $i,
                "count" => 0,
            ];
        }

        // Update $finalArray with fetched averages
        foreach ($hourlyAverages as $average) {
            $finalArray[$average->hour]["count"] = round((float)$average->avg_humidity, 2);
        }

        return $finalArray;
        // $finalarray = [];

        // for ($i = 0; $i < 24; $i++) {

        //     $j = $i;

        //     $j = $i <= 9 ? "0" . $i : $i;

        //     //$date = date('Y-m-d'); //, strtotime(date('Y-m-d') . '-' . $i . ' days'));
        //     $model = AlarmDeviceSensorLogs::where('company_id', $company_id)
        //         ->where("serial_number", $device_serial_number)
        //         ->where("humidity", "!=", "NaN")
        //         ->where('log_time', '>=', $date . ' ' . $j . ':00:00')
        //         ->where('log_time', '<=', $date  . ' ' . $j . ':59:59')
        //         ->avg("humidity");

        //     $finalarray[] = [
        //         "date" => $date,
        //         "hour" => $i,
        //         "count" =>   $model == null ? 0 : round((float)$model, 2),

        //     ];
        // }


        // return  $finalarray;
    }
    public function getTemparatureHourlyData($company_id, $device_serial_number, $date, $request)
    {
        $finalArray = [];

        // Assuming $date is defined somewhere before this block
        // $date = date('Y-m-d');

        // Fetch hourly averages in a single query
        $hourlyAverages = DeviceTemperatureLogs::selectRaw("EXTRACT(HOUR FROM log_time) AS hour, AVG(temparature) AS avg_temperature")
            ->where('company_id', $company_id)
            ->where('serial_number', $device_serial_number)
            ->where('temparature', '!=', 'NaN')
            ->where('temparature', '!=', null)
            ->when($request->filled("device_temperature_serial_address") && $request->device_temperature_serial_address > 0, function ($query) use ($request) {


                $query->where("temperature_serial_address",   $request->device_temperature_serial_address);
            })
            ->whereDate('log_time', $date)
            ->groupBy('hour')
            ->get();

        // Initialize $finalArray with default values
        for ($i = 0; $i < 24; $i++) {
            $finalArray[$i] = [
                "date" => $date,
                "hour" => $i,
                "count" => 0,
            ];
        }

        // Update $finalArray with fetched averages
        foreach ($hourlyAverages as $average) {
            $hour = (int)$average->hour;
            $finalArray[$hour]["count"] = round((float) $average->avg_temperature, 2);
        }

        return $finalArray;
        // $finalarray = [];

        // for ($i = 0; $i < 24; $i++) {

        //     $j = $i;

        //     $j = $i <= 9 ? "0" . $i : $i;

        //     // $date = date('Y-m-d'); //, strtotime(date('Y-m-d') . '-' . $i . ' days'));
        //     $model = AlarmDeviceSensorLogs::where('company_id', $company_id)
        //         ->where("serial_number", $device_serial_number)
        //         ->where("temparature", "!=", "NaN")
        //         ->where('log_time', '>=', $date . ' ' . $j . ':00:00')
        //         ->where('log_time', '<=', $date  . ' ' . $j . ':59:59')
        //         ->avg("temparature");

        //     $finalarray[] = [
        //         "date" => $date,
        //         "hour" => $i,
        //         "count" =>  $model == null ? 0 : round((float) $model, 2),

        //     ];
        // }


        // return  $finalarray;
    }
    public function UpdateCompanyIds()
    {
        $date = date("Y-m-d H:i:s");

        $model = AlarmDeviceSensorLogs::where("company_id", 0)->distinct('serial_number');


        // $model->whereHas('device', function ($query) {
        //     $query->where('company_id', '!=', 0);
        // });

        $model->take(100);
        $model->with("device:device_id,company_id,location,device_type");
        $rows = $model->get(["serial_number"]);

        if (count($rows) == 0) {
            return "[" . $date . "] Cron: UpdateCompanyIds. No new record found while updating company ids for device.\n";
        }

        $i = 0;

        foreach ($rows as $arr) {

            try {
                $i++;
                AlarmDeviceSensorLogs::where("serial_number", $arr["serial_number"])->update([
                    "company_id" => $arr["device"]["company_id"] ?? 0,

                ]);
            } catch (\Throwable $th) {
                Logger::channel("custom")->error('Cron: UpdateCompanyIds. Error Details: ' . $th);

                $data = [
                    'title' => 'Quick action required',
                    'body' => $th,
                ];

                return "[" . $date . "] Cron: UpdateCompanyIds. Error occured while updating company ids.\n";
            }
        }

        return ""; //"[" . $date . "] Cron: UpdateCompanyIds. $i Logs has been merged with Company IDS.\n"; //."Details: " . json_encode($result) . ".\n";

    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeviceSensorLogs  $deviceSensorLogs
     * @return \Illuminate\Http\Response
     */
    public function show(AlarmDeviceSensorLogs $deviceSensorLogs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeviceSensorLogs  $deviceSensorLogs
     * @return \Illuminate\Http\Response
     */
    public function edit(AlarmDeviceSensorLogs $deviceSensorLogs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DeviceSensorLogs  $deviceSensorLogs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AlarmDeviceSensorLogs $deviceSensorLogs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeviceSensorLogs  $deviceSensorLogs
     * @return \Illuminate\Http\Response
     */
    public function destroy(AlarmDeviceSensorLogs $deviceSensorLogs)
    {
        //
    }

    public function deleteOldLogs()
    {

        $logs = [];
        try {

            //delete duplicate 5 days before old logs
            $logs[] = $this->deleteOld5DaysLogs();
        } catch (\Exception $e) {
        }
        try {
            $logs[] = $this->delete2MonthOldLogs();
        } catch (\Exception $e) {
        }
        try {
            $logs[] = $this->deleteOneMonthOldLogs();
        } catch (\Exception $e) {
        }


        Storage::put("logs/deleted-logs-count-" . date("Y-m-d") . ".logs", json_encode($logs));
        return json_encode($logs);
    }

    public function delete2MonthOldLogs()
    {

        $date =  date("Y-m-d", strtotime('-60 days'));
        $logs = AlarmDeviceSensorLogs::where("log_time", "<=", $date)->delete();;
    }
    public function deleteOneMonthOldLogs()
    {
        // Define the start and end date variables
        // $startDate = '2024-04-02';
        // $endDate = '2024-04-03';

        $date =  date("Y-m-d", strtotime('-30 days'));
        $startDate = new DateTime($date . " 00:00:00"); // Current date and time
        $endDate = new DateTime($date . " 23:59:59");; // Display for the next 24 hours


        // Create Carbon instances for the start and end dates
        $fromDate = Carbon::create($startDate, 0, 0, 0); // Start date: April 2, 2024, 00:00
        $toDate = Carbon::create($endDate, 0, 0, 0);    // End date: April 3, 2024, 00:00

        // Loop through each hour in the date range

        $companies = Company::get();


        $finalDuplicateIds = [];
        foreach ($companies as $company) {

            while ($fromDate->lt($toDate)) {
                // Calculate the next hour
                $nextHour = $fromDate->copy()->addHours(13);

                // Display the from and to dates for the current hour
                echo "From: " . $fromDate->toDateTimeString() . " To: " . $nextHour->toDateTimeString() . "\n";

                $filter_from_date = $fromDate->toDateTimeString();

                $filter_to_datetime = $nextHour->toDateTimeString();


                $logs = AlarmDeviceSensorLogs::where("company_id", $company->id)

                    ->where("log_time", ">=",  $filter_from_date)
                    ->where("log_time", "<",  $filter_to_datetime);

                $deleteIds = $logs->get()->pluck('id')->toArray();

                array_shift($deleteIds);
                // // Fetch the records to delete (including soft deleted ones)
                //$logsToDelete = AlarmDeviceSensorLogs::withTrashed()->whereIn('id', $deleteIds)->get();

                // // Check if there are records to delete
                // if ($logsToDelete->isNotEmpty()) {
                //     // Perform the force delete operation
                //     $deleted = AlarmDeviceSensorLogs::withTrashed()->whereIn('id', $deleteIds)->forceDelete();
                // }


                if (count($deleteIds))
                    AlarmDeviceSensorLogs::whereIn("id", $deleteIds)->delete();


                $finalDuplicateIds = array_merge($finalDuplicateIds, $deleteIds);

                $fromDate->addHours(12);
            }
        }


        // if (count($finalDuplicateIds))
        //     AlarmDeviceSensorLogs::whereIn("id", $finalDuplicateIds)->delete();



        // Add one hour to the current date


        return $finalDuplicateIds;
        // return;
        // //Fetch 30minutes logs and keep one record for every 30 minutes with alarm
        // //Deleting records which has no alarm vaue

        // $date =  date("Y-m-d", strtotime('-15 days'));
        // $startTime = new DateTime($date . " 00:00:00"); // Current date and time
        // $endTime = new DateTime($date . " 23:59:59");; // Display for the next 24 hours

        // //$interval = new DateInterval('PT60M'); // 60 minutes interval
        // $interval = new DateInterval('PT12H'); // 60 minutes interval
        // $period = new DatePeriod($startTime, $interval, $endTime);

        // $companies = Company::get();


        // $finalDuplicateIds = [];
        // foreach ($companies as $company) {

        //     foreach ($period as $dt) {
        //         $filter_from_date = $dt->format('Y-m-d H:i:s');

        //         $filter_to_datetime = $dt;
        //         $filter_to_datetime = $filter_to_datetime->modify('+60 minutes'); // Add 60 minutes to the current date and time
        //         $filter_to_datetime = $filter_to_datetime->format('Y-m-d H:i:s');


        //         $logs = AlarmDeviceSensorLogs::where("company_id", $company->id)
        //             ->where("water_leakage", 0)
        //             ->where("power_failure", 0)
        //             ->where("door_status", 0)
        //             ->where("smoke_alarm", 0)
        //             ->where("fire_alarm", 0)
        //             ->where("log_time", ">=",  $filter_from_date)
        //             ->where("log_time", "<",  $filter_to_datetime);

        //         $deleteIds = $logs->get()->pluck('id')->toArray();

        //         array_shift($deleteIds);


        //         if (count($deleteIds))
        //             AlarmDeviceSensorLogs::whereIn("id", $deleteIds)->delete();


        //         $finalDuplicateIds = array_merge($finalDuplicateIds, $deleteIds);
        //     }
        // }


        // // if (count($finalDuplicateIds))
        // //     AlarmDeviceSensorLogs::whereIn("id", $finalDuplicateIds)->delete();

        // return $finalDuplicateIds;
    }
    public function deleteOld5DaysLogs()
    {

        //delete duplicate 5 days before old logs

        $date = date("Y-m-d", strtotime('-3 days'));
        $return = [];


        $companies = Company::get();

        $finalDuplicateIds = [];
        foreach ($companies as $company) {

            $logs = AlarmDeviceSensorLogs::where("company_id", $company->id)

                ->where("water_leakage", 0)
                ->where("power_failure", 0)
                ->where("door_status", 0)
                ->where("smoke_alarm", 0)
                ->where("fire_alarm", 0)
                ->where("log_time", ">=",  $date . ' 00:00:00')
                ->where("log_time", "<",  $date . ' 23:59:59');

            $logs = $logs->get();


            $uniqueCombinations = [];
            $duplicateCombinations = [];

            foreach ($logs as $log) {

                $key = $log['serial_number'] . '_' . date("Y-m-d H:i", strtotime($log['log_time'])) . '-' . $log['smoke_status'] . '_' . $log['water_leakage'] . '_' . $log['power_failure'] . '_' . $log['door_status'];
                if (isset($uniqueCombinations[$key])) {
                    $duplicateCombinations[] =   $log['id'];
                } else {

                    $uniqueCombinations[$key] =  $log['id'];
                }
            }

            if (count($duplicateCombinations))
                AlarmDeviceSensorLogs::whereIn("id", $duplicateCombinations)->delete();




            $finalDuplicateIds = array_merge($finalDuplicateIds, $duplicateCombinations);

            // }
        }

        // if (count($finalDuplicateIds))
        //     AlarmDeviceSensorLogs::whereIn("id", $finalDuplicateIds)->delete();

        return  $finalDuplicateIds;
    }
}

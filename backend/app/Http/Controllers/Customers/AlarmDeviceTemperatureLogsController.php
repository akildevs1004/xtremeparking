<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\AlarmDeviceTemperatureLogs;
use App\Models\AlarmEvents;
use App\Models\AlarmLogs;
use App\Models\Deivices\DeviceZones;
use App\Models\Device;
use Carbon\Carbon;
use DateTime;
use Hamcrest\Arrays\IsArray;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlarmDeviceTemperatureLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Models\AlarmDeviceTemperatureLogs  $alarmDeviceTemperatureLogs
     * @return \Illuminate\Http\Response
     */
    public function show(AlarmDeviceTemperatureLogs $alarmDeviceTemperatureLogs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AlarmDeviceTemperatureLogs  $alarmDeviceTemperatureLogs
     * @return \Illuminate\Http\Response
     */
    public function edit(AlarmDeviceTemperatureLogs $alarmDeviceTemperatureLogs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AlarmDeviceTemperatureLogs  $alarmDeviceTemperatureLogs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AlarmDeviceTemperatureLogs $alarmDeviceTemperatureLogs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AlarmDeviceTemperatureLogs  $alarmDeviceTemperatureLogs
     * @return \Illuminate\Http\Response
     */
    public function destroy(AlarmDeviceTemperatureLogs $alarmDeviceTemperatureLogs)
    {
        //
    }
    function createDateRangeArray($startDate, $endDate)
    {
        $dateStrings = [];
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            $dateStrings[] = $currentDate->format('Y-m-d'); // Change the format as needed
            $currentDate->modify('+1 day');
        }

        return $dateStrings;
    }

    public function getAlarmLogsMonthwise(Request $request)
    {
        $finalarray = [];
        $dateStrings = [];

        // Validate request inputs
        $request->validate([
            'date_from' => 'required|date|before_or_equal:date_to',
            'date_to' => 'required|date|after_or_equal:date_from',
            'company_id' => 'required|integer',
            'customer_id' => 'nullable|integer'
        ]);

        if ($request->has("date_from") && $request->has("date_to")) {
            $startDate = new Carbon($request->date_from); // Replace with your start date
            $endDate = new Carbon($request->date_to);   // Replace with your end date

            $dateStrings = $this->createDateRangeArray($startDate, $endDate);
        }

        foreach ($dateStrings as $date) {
            $logs = AlarmEvents::where('company_id', $request->company_id)

                ->when($request->filled("customer_id"), fn ($q) => $q->where("customer_id", $request->customer_id))
                ->when($request->filled("serial_number"), fn ($q) => $q->where("serial_number", $request->serial_number))
                ->whereBetween('alarm_start_datetime', [$date . ' 00:00:00', $date . ' 23:59:59'])
                //->where('alarm_status', 1)
                ->get();

            $finalarray[] = [
                "date" => $date,
                "Burglary" => $logs->where('alarm_type', 'Burglary')->count(),
                "Medical" => $logs->where('alarm_type', 'Medical')->count(),
                "Temperature" => $logs->where('alarm_type', 'Temperature')->count(),
                "Water" => $logs->where('alarm_type', 'Water')->count(),
                "Fire" => $logs->where('alarm_type', 'Fire')->count(),
            ];
        }

        return $finalarray;
    }
    public function getDeviceTodayHourlyTemperature(Request $request)
    {
        $date = date('Y-m-d');
        if ($request->filled("from_date")) {
            $date = $request->from_date;
        }

        $temperatureDevices = Device::where('company_id', $request->company_id)
            // ->where('device_type', 'Temperature')
            ->when($request->filled('serial_number'), function ($query) use ($request) {
                $query->where('serial_number', $request->serial_number);
            })
            ->where('customer_id', $request->customer_id)
            ->select(['serial_number', 'location', 'customer_id'])
            ->get()
            ->toArray();

        $temperatureDevices_zones = DB::table('device_sensor_zones')
            ->where('devices.customer_id', $request->customer_id)
            // ->when($request->filled('serial_number'), function ($query) use ($request) {
            //     $query->where('serial_number', $request->serial_number);
            // })
            ->join('devices', 'device_sensor_zones.device_id', '=', 'devices.id')
            ->where('device_sensor_zones.company_id', $request->company_id)
            ->where('device_sensor_zones.sensor_name', 'Temperature')
            ->select(['devices.serial_number', 'device_sensor_zones.location', 'device_sensor_zones.zone_code'])
            ->get()
            ->toArray();

        // $temperatureDevices = array_merge($temperatureDevices, $temperatureDevices_zones);

        $uniqueDevices = [];
        foreach (array_merge($temperatureDevices, $temperatureDevices_zones) as $device) {
            $serialNumber = is_array($device) ? $device['serial_number'] : $device->serial_number;
            if (!isset($uniqueDevices[$serialNumber])) {
                $uniqueDevices[$serialNumber] = $device;
            }
        }

        $logs = [];
        foreach ($uniqueDevices as $device) {
            $serialNumber = is_array($device) ? $device['serial_number'] : $device->serial_number;
            $HouryData  = $this->gettemperatureHourlyData($request->company_id, $serialNumber, $date);
            $logs[] = ["serial_number" => $serialNumber, "data" => $HouryData];
        }

        return
            $logs[0] ?? [];
    }

    public function gettemperatureHourlyData($company_id, $device_serial_number, $date)
    {
        $finalArray = [];

        // Assuming $date is defined somewhere before this block
        // $date = date('Y-m-d');

        // Fetch hourly averages in a single query
        $hourlyAverages = AlarmDeviceTemperatureLogs::selectRaw("EXTRACT(HOUR FROM log_time) AS hour, AVG(temperature) AS avg_temperature")
            ->where('company_id', $company_id)

            ->where('serial_number', $device_serial_number)
            ->where('temperature', '!=', 'NaN')
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
    }
    public function getDeviceLatestTemperature(Request $request)
    {



        $date = date('Y-m-d');

        $temperatureDevices = Device::where('company_id', $request->company_id)

            ->when($request->filled('serial_number'), function ($query) use ($request) {
                $query->where('serial_number', $request->serial_number);
            })
            ->where('customer_id', $request->customer_id)

            ->select(['serial_number', 'location', 'customer_id'])
            ->get()
            ->toArray();

        $temperatureDevices_zones = DB::table('device_sensor_zones')
            ->join('devices', 'device_sensor_zones.device_id', '=', 'devices.id')
            // ->when($request->filled('serial_number'), function ($query) use ($request) {
            //     $query->where('serial_number', $request->serial_number);
            // })
            ->where('device_sensor_zones.company_id', $request->company_id)
            ->where('devices.customer_id', $request->customer_id)

            ->where('device_sensor_zones.sensor_name', 'Temperature')
            ->select(['devices.serial_number', 'device_sensor_zones.location', 'device_sensor_zones.zone_code'])
            ->get()
            ->toArray();

        // $temperatureDevices = array_merge($temperatureDevices, $temperatureDevices_zones);

        $uniqueDevices = [];
        foreach (array_merge($temperatureDevices, $temperatureDevices_zones) as $device) {
            $serialNumber = is_array($device) ? $device['serial_number'] : $device->serial_number;
            if (!isset($uniqueDevices[$serialNumber])) {
                $uniqueDevices[$serialNumber] = $device;
            }
        }

        $logs = [];
        foreach ($uniqueDevices as $device) {

            $temperature_latest = '--';
            $temperature_date_time = '--';
            $temperature_min = '--';
            $temperature_max = '--';
            $temperature_min_date_time = '--';
            $temperature_max_date_time = '--';

            $humidity_latest = '--';
            $humidity_date_time = '--';
            $humidity_min = '--';
            $humidity_max = '--';
            $humidity_min_date_time = '--';
            $humidity_max_date_time = '--';

            $serialNumber = is_array($device) ? $device['serial_number'] : $device->serial_number;

            // Get latest temperature and humidity logs
            $latestLog = AlarmDeviceTemperatureLogs::where('company_id', $request->company_id)
                ->where('serial_number', $serialNumber)
                ->where('temperature', '>', 0)
                ->where('temperature', '!=', 'NaN')
                ->whereDate('log_time', $date)
                ->orderBy('log_time', 'DESC')
                ->first();

            if ($latestLog) {
                $temperature_latest = $latestLog->temperature;
                $temperature_date_time = $latestLog->log_time;
                $humidity_latest = $latestLog->humidity;
                $humidity_date_time = $latestLog->log_time;
            }

            // Get min temperature
            $minTemperatureLog = AlarmDeviceTemperatureLogs::where('company_id', $request->company_id)
                ->where('serial_number', $serialNumber)
                ->whereDate('log_time', $date)
                ->where('temperature', '=', AlarmDeviceTemperatureLogs::where('company_id', $request->company_id)
                    ->where('serial_number', $serialNumber)
                    ->where('temperature', '!=', '0.0')
                    ->where('temperature', '!=', 'NaN')
                    ->whereDate('log_time', $date)
                    ->min('temperature'))
                ->first();

            if ($minTemperatureLog) {
                $temperature_min = $minTemperatureLog->temperature;
                $temperature_min_date_time = $minTemperatureLog->log_time;
            }

            // Get max temperature
            $maxTemperatureLog = AlarmDeviceTemperatureLogs::where('company_id', $request->company_id)
                ->where('serial_number', $serialNumber)
                ->whereDate('log_time', $date)
                ->where('temperature', '=', AlarmDeviceTemperatureLogs::where('company_id', $request->company_id)
                    ->where('serial_number', $serialNumber)
                    ->where('temperature', '!=', '0.0')
                    ->where('temperature', '!=', 'NaN')
                    ->whereDate('log_time', $date)
                    ->max('temperature'))
                ->first();

            if ($maxTemperatureLog) {
                $temperature_max = $maxTemperatureLog->temperature;
                $temperature_max_date_time = $maxTemperatureLog->log_time;
            }

            // Get min humidity
            $minHumidityLog = AlarmDeviceTemperatureLogs::where('company_id', $request->company_id)
                ->where('serial_number', $serialNumber)
                ->where('humidity', '>', 0)
                ->where('humidity', '!=', 'NaN')
                ->whereDate('log_time', $date)
                ->where('humidity', '=', AlarmDeviceTemperatureLogs::where('company_id', $request->company_id)
                    ->where('serial_number', $serialNumber)
                    ->where('humidity', '!=', '0.0')
                    ->where('humidity', '!=', 'NaN')
                    ->whereDate('log_time', $date)
                    ->min('humidity'))
                ->first();

            if ($minHumidityLog) {
                $humidity_min = $minHumidityLog->humidity;
                $humidity_min_date_time = $minHumidityLog->log_time;
            }

            // Get max humidity
            $maxHumidityLog = AlarmDeviceTemperatureLogs::where('company_id', $request->company_id)
                ->where('serial_number', $serialNumber)
                ->where('humidity', '>', 0)
                ->where('humidity', '!=', 'NaN')
                ->whereDate('log_time', $date)
                ->where('humidity', '=', AlarmDeviceTemperatureLogs::where('company_id', $request->company_id)
                    ->where('serial_number', $serialNumber)
                    ->where('humidity', '!=', '0.0')
                    ->where('humidity', '!=', 'NaN')
                    ->whereDate('log_time', $date)
                    ->max('humidity'))
                ->first();

            if ($maxHumidityLog) {
                $humidity_max = $maxHumidityLog->humidity;
                $humidity_max_date_time = $maxHumidityLog->log_time;
            }

            // Get device details
            // $deviceDetails = Device::where('company_id', $request->company_id)
            //     ->where('serial_number', $serialNumber)
            //     ->first();

            $logs[] = [
                'serial_number' => $serialNumber,
                'temperature_latest' => $temperature_latest,
                'temperature_date_time' => $temperature_date_time,
                'temperature_min' => $temperature_min,
                'temperature_max' => $temperature_max,
                'temperature_min_date_time' => $temperature_min_date_time,
                'temperature_max_date_time' => $temperature_max_date_time,
                'humidity_latest' => $humidity_latest,
                'humidity_date_time' => $humidity_date_time,
                'humidity_min' => $humidity_min,
                'humidity_max' => $humidity_max,
                'humidity_min_date_time' => $humidity_min_date_time,
                'humidity_max_date_time' => $humidity_max_date_time,
                // 'device' => $deviceDetails,
            ];
        }

        return $logs[0] ?? [];
    }
}

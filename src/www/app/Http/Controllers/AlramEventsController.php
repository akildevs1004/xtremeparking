<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Customers\Api\ApiAlarmDeviceTemperatureLogsController;
use App\Models\AlarmEvents;
use App\Models\AlramEvents;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AlramEventsController extends Controller
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
     * @param  \App\Models\AlramEvents  $alramEvents
     * @return \Illuminate\Http\Response
     */
    // public function show(AlramEvents $alramEvents)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  \App\Models\AlramEvents  $alramEvents
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit(AlramEvents $alramEvents)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Models\AlramEvents  $alramEvents
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(Request $request, AlramEvents $alramEvents)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  \App\Models\AlramEvents  $alramEvents
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy(AlramEvents $alramEvents)
    // {
    //     //
    // }

    public function verifyOfflineDevices()
    {
        $devices = Device::with(["customer.mappedsecurity"])->where("serial_number", "!=", null)
            ->where("customer_id", "!=", null)
            ->where("status_id", 1)
            ->get();

        $offlineDevices = [];

        foreach ($devices as $device) {
            $timeZone = $device?->utc_time_zone ?: 'Asia/Dubai';
            $nowInTimeZone = Carbon::now($timeZone);


            if ($device->last_live_datetime) {
                $timeDifference = Carbon::parse($device->last_live_datetime)->diffInSeconds($nowInTimeZone);

                if ($timeDifference > 60 * 5) {

                    if ($device->status_id == 1) {


                        Device::where("id", $device->id)->update(["status_id" => 2]);


                        $security_name = null;
                        $security_id = null;
                        if ($device->customer->mappedsecurity) {
                            $security_name = $device->customer->mappedsecurity->securityInfo->first_name . ' ' . $device->customer->mappedsecurity->securityInfo->last_name;
                            $security_id =  $device->customer->mappedsecurity->securityInfo->id;
                        }



                        $data = [
                            "company_id" => $device['company_id'],
                            "serial_number" => $device['serial_number'],
                            "alarm_start_datetime" => $device->last_live_datetime,
                            "customer_id" => $device['customer_id'],
                            "zone" => null,
                            "area" =>  null,
                            "alarm_type" => "Offline",
                            "alarm_category" => 3,
                            "sensor_zone_id" => null,
                            "alarm_source" => null,
                            "security_name" => $security_name,
                            "security_id" => $security_id,
                            "created_event_from" => "verifyOfflineDevices",



                        ];
                        $offlineDevices[] = $data;

                        $latestAlarmEvent =  AlarmEvents::where("serial_number", $device['serial_number'])->where("alarm_status", 0)->orderBy("alarm_start_datetime", "desc")->first();
                        if ($latestAlarmEvent &&  $latestAlarmEvent->alarm_status == 1) {
                            AlarmEvents::create($data);
                        } else if (!$latestAlarmEvent) {
                            AlarmEvents::create($data);
                        }
                    }
                }
            }
        }
        (new ApiAlarmDeviceTemperatureLogsController())->createAlarmEventsJsonFile();

        return $offlineDevices;
    }
}

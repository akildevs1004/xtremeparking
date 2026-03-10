<?php

namespace App\Http\Controllers;

use App\Models\AlarmEvents;
use App\Models\Customers\CustomerBuildingPictures;
use App\Models\Deivices\DeviceZones;
use App\Models\Plotting;
use Exception;
use Illuminate\Http\Request;

class PlottingController extends Controller
{
    public function index(Request $request)
    {
        $customerBuildingPictureId = request("customer_building_picture_id");

        $model = Plotting::with(["photos"])->where("customer_building_picture_id", $customerBuildingPictureId)->first();

        if (!$model || !isset($model->plottings)) {
            return $model;
        }
        $plottings = $model->plottings;

        $plottings =  $this->updatePlottingWithZoneDetails($plottings);
        /////////// $plottings =  $this->updatePlottingWithalarm_event($plottings);

        // // Collect unique sensor IDs to minimize queries
        // // $sensorIds = collect($plottings)->pluck('sensor_id')->unique();


        // // Fetch alarm events for these sensor IDs
        // $alarmEvents = AlarmEvents::where("alarm_status", 1)->get()->keyBy('zone');

        // // Map plottings to include alarm events
        // $plottings = collect($plottings)->map(function ($plotting) use ($alarmEvents) {
        //     $plotting['alarm_event'] = $alarmEvents->get($plotting['sensor_id']);
        //     return $plotting;
        // });

        // Encode the plottings back to JSON


        //  return CustomerBuildingPictures::with("photoPlottings")->where("customer_id", $request->customer_id)->get();

        $model->plottings = $plottings;
        $buildingPhotosPlottings = [];
        //get customer all photos plottings
        if ($request->filled('customer_id'))
            $buildingPhotosPlottings = CustomerBuildingPictures::with("photoPlottings")->where("customer_id", $request->customer_id)->get();
        $model->buildingPhotosPlottings = $buildingPhotosPlottings;
        return $model;
    }
    public function plottingWithCustomerId(Request $request)
    {
        $customer_id = request("customer_id");
        $array = CustomerBuildingPictures::where("customer_id", $request->customer_id)->pluck("id");

        $model = Plotting::with(["photos"])->whereIn("customer_building_picture_id", $array)->get();

        // if (!$model || !isset($model->plottings)) {
        //     return $model;
        // }
        $finalArray = [];
        foreach ($model as $key => $value) {
            $plottings = $value->plottings;

            $plottings =  $this->updatePlottingWithZoneDetails($plottings);
            /////////////////$plottings =  $this->updatePlottingWithalarm_event($plottings);



            //  return CustomerBuildingPictures::with("photoPlottings")->where("customer_id", $request->customer_id)->get();

            $value->plottings = $plottings;
            $buildingPhotosPlottings = [];
            //get customer all photos plottings
            if ($request->filled('customer_id'))
                $buildingPhotosPlottings = CustomerBuildingPictures::with("photoPlottings")->where("customer_id", $request->customer_id)->get();
            $value->buildingPhotosPlottings = $buildingPhotosPlottings;

            $finalArray[] = $value;
        }


        return $model;
    }
    public function updatePlottingWithZoneDetails($plottings)
    {

        // $plottings->buildingPhotosPlottings->photo_plottings = $plottings->buildingPhotosPlottings->photo_plottings->map(function ($plot) {
        //     $plot['zone_data'] = DeviceZones::where('id', $plot['sensor_id'])->first();
        //     return $plot;
        // });

        foreach ($plottings as &$plot) {
            $plot['zone_data'] = DeviceZones::where('id', $plot['sensor_id'])->get(['area_code', 'zone_code'])->first();
        }


        return $plottings;
    }
    // public function updatePlottingWithalarm_event($plottings)
    // {
    //     $sensorIds = array_column($plottings, 'sensor_id');
    //     $deviceZones = DeviceZones::with('device')
    //         ->whereIn('id', array_filter($sensorIds))
    //         ->get()
    //         ->keyBy('id');

    //     foreach ($plottings as &$plot) {
    //         $plot['alarm_event'] = null;

    //         if ($plot['sensor_id'] && $plot['sensor_id'] != $plot['device_id']) {
    //             $deviceZone = $deviceZones->get($plot['sensor_id']);

    //             if ($deviceZone) {
    //                 $plot['alarm_event'] = AlarmEvents::where('alarm_status', 1)
    //                     ->where('serial_number', $deviceZone->device->serial_number)
    //                     ->where('zone', $deviceZone->zone_code)
    //                     ->where('area', $deviceZone->area_code)
    //                     ->get();
    //             }
    //         } else if ($plot['sensor_id'] == $plot['device_id']) {
    //             $plot['alarm_event'] = AlarmEvents::with('device')
    //                 ->where('alarm_status', 1)
    //                 ->whereHas('device', function ($q) use ($plot) {
    //                     $q->where('id', $plot['device_id']);
    //                 })
    //                 ->get();
    //         }
    //     }

    //     return $plottings;
    // }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_building_picture_id' => 'required|integer',
            'plottings' => 'required|array',
            'plottings.*.sensor_id' => 'required',
            'plottings.*.device_id' => 'required',
            'plottings.*.label' => 'required|string',
            'plottings.*.top' => 'required|string',
            'plottings.*.left' => 'required|string',
        ]);

        try {
            $plotting = Plotting::query();

            $plotting->where("customer_building_picture_id", $data['customer_building_picture_id']);

            $plotting->delete();

            $plotting = $plotting->create($data);

            return response()->json($plotting, 201);
        } catch (Exception $e) {
            // Handle general errors
            return response()->json([
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    public function resetPlotting(REquest $request)
    {
        if ($request->filled('customer_building_picture_id')) {

            Plotting::where("customer_building_picture_id", $request->customer_building_picture_id)->delete();
        }

        return $this->response("Deleted Successfully", null, true);
    }
    public function resetPlottingAll(REquest $request)
    {
        if ($request->filled('customer_building_picture_id')) {

            Plotting::whereIn("customer_building_picture_id", $request->customer_building_picture_id)->delete();
        }

        return $this->response("Deleted Successfully", null, true);
    }
}

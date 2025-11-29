<?php

namespace App\Http\Controllers;

use App\Models\Customers\DeviceZoneTypes;
use App\Models\DeviceSensorTypes;
use Illuminate\Http\Request;

class DeviceZoneTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return DeviceZoneTypes::orderBy('id', 'asc')->paginate($this->per_page ?? 10);
    }
    public function zonetypesList()
    {
        return DeviceZoneTypes::orderBy('id', 'asc')->get();
    }
    public function sensorTypesList()
    {

        return DeviceSensorTypes::orderBy("name", "asc")->get();


        // $sensorNames =  [
        //     ["name" => "Fire Sensor", "image" => "fire_sensor.png"],
        //     ["name" => "Beam Sensor", "image" => "beam_sensor.png"],
        //     ["name" => "Door Sensor", "image" => "door_sensor.png"],
        //     ["name" => "Gas Sensor", "image" => "gas_sensor.png"],
        //     ["name" => "Glass Break Sensor", "image" => "glass_breake_sensor.png"],
        //     ["name" => "Medical Sensor", "image" => "medical_sensor.png"],
        //     ["name" => "Motion Sensor", "image" => "motion_sensor.png"],
        //     ["name" => "Smoke Sensor", "image" => "smoke_sensor.png"],
        //     ["name" => "SOS Sensor", "image" => "sos_sensor.png"],
        //     ["name" => "Temperature Sensor", "image" => "temperature_sensor.png"],
        //     ["name" => "Vibration Sensor", "image" => "vibration_sensor.png"],
        //     ["name" => "Water Leakage Sensor", "image" => "water_leakage_sensor.png"],
        //     ["name" => "Other Sensor", "image" => "other_sensor.png"],




        //     // ["name" => "Heat Sensor", "image" => "motion_sensor.png"],
        //     // ["name" => "Microwave Sensor", "image" => "beam_sensor.png"],
        //     // ["name" => "Shutter Door", "image" => "beam_sensor.png"],
        //     // ["name" => "Curtain Sensor", "image" => "beam_sensor.png"],

        // ];

        // ksort($sensorNames);

        // return $sensorNames;
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data = $request->validate(["name" => "required"]);

        // if ($request->company_id) {
        //     $data["company_id"] = $request->company_id;
        // }

        $verifyDuplicate = DeviceZoneTypes::where("name", $request->name);
        if ($verifyDuplicate->count() == 0) {
            $record = DeviceZoneTypes::create($data);

            if ($record) {
                return $this->response('New Zone Type is  successfully added.', $record, true);
            } else {
                return $this->response('New Zone Type cannot add.', null, false);
            }
        } else {
            return $this->response('  Zone Type is already Exist', null, false);
        }
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
     * @param  \App\Models\Customers\DeviceZoneTypes  $DeviceZoneTypes
     * @return \Illuminate\Http\Response
     */
    public function show(DeviceZoneTypes $DeviceZoneTypes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customers\DeviceZoneTypes  $DeviceZoneTypes
     * @return \Illuminate\Http\Response
     */
    public function edit(DeviceZoneTypes $DeviceZoneTypes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customers\DeviceZoneTypes  $DeviceZoneTypes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {


        $request->validate(["name" => "required", "id" => "required"]);



        $verifyDuplicate = DeviceZoneTypes::where("name", "=", $request->name)->where("id", "!=", $request->id);

        if ($verifyDuplicate->count() == 0) {
            $record = DeviceZoneTypes::where("id", $request->id)->update(["name" => $request->name]);

            if ($record) {
                return $this->response('Zone Type successfully updated.', null, true);
            } else {
                return $this->response('Zone Type cannot updated.', null, false);
            }
        } else {
            return $this->response('Zone Type Already Exist', null, false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customers\DeviceZoneTypes  $DeviceZoneTypes
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {


        if ($request->filled('id')) {
            DeviceZoneTypes::where("id", $request->id)->delete();
        }

        $this->response("Zone Type name Deleted Successfully", null, true);

        //
    }
}

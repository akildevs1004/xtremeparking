<?php

namespace App\Http\Controllers;

use App\Models\Customers\AlarmSensorTypes;
use Illuminate\Http\Request;

class AlarmSensorTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return AlarmSensorTypes::orderBy('id', 'asc')->paginate($this->per_page ?? 10);
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

        $verifyDuplicate = AlarmSensorTypes::where("name", $request->name);
        if ($verifyDuplicate->count() == 0) {
            $record = AlarmSensorTypes::create($data);

            if ($record) {
                return $this->response('New Sensor Type is  successfully added.', $record, true);
            } else {
                return $this->response('New Sensor cannot add.', null, false);
            }
        } else {
            return $this->response('New Sensor is already Exist', null, false);
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
     * @param  \App\Models\Customers\AlarmSensorTypes  $alarmSensorTypes
     * @return \Illuminate\Http\Response
     */
    public function show(AlarmSensorTypes $alarmSensorTypes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customers\AlarmSensorTypes  $alarmSensorTypes
     * @return \Illuminate\Http\Response
     */
    public function edit(AlarmSensorTypes $alarmSensorTypes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customers\AlarmSensorTypes  $alarmSensorTypes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {


        $request->validate(["name" => "required", "id" => "required"]);



        $verifyDuplicate = AlarmSensorTypes::where("name", "=", $request->name)->where("id", "!=", $request->id);

        if ($verifyDuplicate->count() == 0) {
            $record = AlarmSensorTypes::where("id", $request->id)->update(["name" => $request->name]);

            if ($record) {
                return $this->response('Sensor Type successfully updated.', null, true);
            } else {
                return $this->response('Sensor Type cannot updated.', null, false);
            }
        } else {
            return $this->response('Sensor Type Already Exist', null, false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customers\AlarmSensorTypes  $alarmSensorTypes
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {


        if ($request->filled('id')) {
            AlarmSensorTypes::where("id", $request->id)->delete();
        }

        $this->response("Sensor name Deleted Successfully", null, true);

        //
    }
}

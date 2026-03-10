<?php

namespace App\Http\Controllers;

use App\Models\DeviceTemperatureSensors;
use Illuminate\Http\Request;

class DeviceTemperatureSensorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = DeviceTemperatureSensors::query();
        if ($request->filled('status') && $request->status != "-1") {
            $model->where('status', $request->status);
        }

        $model = $model->where('company_id', $request->company_id);


        //  else {
        //     $model->where('status', 0);
        // }

        //datatable sorty by
        if ($request->filled('sortBy')) {

            $sortDesc = $request->sortDesc === 'true' ? 'DESC' : 'ASC';
            if (strpos($request->sortBy, '.')) {
            } else {
                $model->orderBy($request->sortBy, $sortDesc);
            }
        } else {
            $model->orderBy('id', 'ASC');
        }

        return $model->paginate($request->per_page);
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
     * @param  \App\Http\Requests\StoreDeviceTemperatureSensorsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([

                'company_id' => 'required',
                'name' => 'required',
                'device_id' => 'required',
                'temperature_serial_address' => 'required',


            ]);

            if ($data) {

                $verifyIsDeviceTemperatureSensors = DeviceTemperatureSensors::where('company_id', $request->company_id)
                    ->where('device_id', $request->device_id)
                    ->where('temperature_serial_address', $request->temperature_serial_address)
                    ->count();
                if ($verifyIsDeviceTemperatureSensors == 0) {

                    $record = DeviceTemperatureSensors::create($data);

                    if ($record) {

                        return $this->response('Sensor details are successfully created', $record, true);
                    } else {
                        return $this->response('Sensor details not created', $record, false);
                    }
                } else {
                    return $this->response($request->temperature_serial_address  . ' : Sensor number   is  already exist. ', $data, false);
                }
            } else {
                return $this->response('Data is not validated', $data, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeviceTemperatureSensors  $DeviceTemperatureSensors
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return DeviceTemperatureSensors::where('id', $id)->first();
    }
    public function destroy(Request $request, $id)
    {
        if (DeviceTemperatureSensors::find($id)->delete()) {

            return $this->response('Sensor    successfully deleted.', null, true);
        } else {
            return $this->response('Sensor   cannot delete.', null, false);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeviceTemperatureSensors  $DeviceTemperatureSensors
     * @return \Illuminate\Http\Response
     */
    public function edit(DeviceTemperatureSensors $DeviceTemperatureSensors)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDeviceTemperatureSensorsRequest  $request
     * @param  \App\Models\DeviceTemperatureSensors  $DeviceTemperatureSensors
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeviceTemperatureSensors $DeviceTemperatureSensors, $id)
    {
        // try {

        $data = $request->validate([

            'company_id' => 'required',
            'name' => 'required',
            'device_id' => 'required',
            'temperature_serial_address' => 'required',


        ]);

        if ($data) {

            $isDeviceTemperatureSensorsExist = DeviceTemperatureSensors::where('company_id', $request->company_id)
                ->where('device_id', $request->device_id)
                ->where('temperature_serial_address', $request->temperature_serial_address)
                ->first();

            if ($isDeviceTemperatureSensorsExist) {
                if ($isDeviceTemperatureSensorsExist->id != $id) {
                    return $this->response($request->start_price . '-' . $request->end_price . ' DeviceTemperatureSensors Details are Can not save', null, false);
                }
            }
            $status = DeviceTemperatureSensors::whereId($id)->update($data);
            if ($status) {

                return $this->response('DeviceTemperatureSensors Details are updated succesfully', $status, true);
            } else {
                return $this->response('DeviceTemperatureSensors Details are not Updated', $status, false);
            }
        } else {
            return $this->response('Error Occured', $data, false);
        }
        // } catch (\Throwable $th) {
        //     return $this->response('Something wrong.', $th, false);
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeviceTemperatureSensors  $DeviceTemperatureSensors
     * @return \Illuminate\Http\Response
     */
}

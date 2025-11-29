<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Device;
use App\Models\MasterDeviceSerialNumbers;
use Illuminate\Http\Request;

class MasterDeviceSerialNumbersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = Device::with(["company"]);

        $model->where("serial_number", "!=", "Manual");
        $model->where("serial_number", "!=", "Mobile");

        $model->when($request->filled("common_search"), function ($q) use ($request) {
            $q->where(function ($qq) use ($request) {

                $qq->where("serial_number", "ILIKE", "%$request->common_search%")
                    ->orWhere("model_number", "ILIKE", "%$request->common_search%")
                    ->orWhere("device_type", "ILIKE", "%$request->common_search%")
                    ->orWhereHas("company", function ($qqq) use ($request) {
                        $qqq->where("name", "ILIKE", "%$request->common_search%");
                    });
            });
        });

        $model->orderBy("serial_number", "asc");

        return  $model->paginate($request->perpage ?? 10);
    }
    // public function index_old(Request $request)
    // {
    //     $model = MasterDeviceSerialNumbers::with(["assignedcompany", "liveDevice"]);


    //     $model->when($request->filled("common_search"), function ($q) use ($request) {
    //         $q->where(function ($qq) use ($request) {

    //             $qq->where("master_serial_number", "ILIKE", "%$request->common_search%")
    //                 ->orWhere("device_model", "ILIKE", "%$request->common_search%")
    //                 ->orWhere("device_type", "ILIKE", "%$request->common_search%")
    //                 ->orWhereHas("assignedcompany", function ($qqq) use ($request) {
    //                     $qqq->where("name", "ILIKE", "%$request->common_search%");
    //                 });
    //         });
    //     });

    //     $model->orderBy("master_serial_number", "asc");

    //     return  $model->paginate($request->perpage ?? 10);
    // }

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
        $request->validate([
            'company_id' => 'required',
            'device_type' => 'required',

            'new_serial_number' => 'required',

            'model_number' => 'required',
            'device_description' => 'nullable',
            'picture' => 'nullable',
            'name' => 'required',
            'location' => 'required',
            'utc_time_zone' => 'required',
            'customer_id' => 'required',

        ]);


        $data = $request->all();

        unset($data['login_user_id']);
        unset($data['login_user_type']);



        $data["serial_number"] = $data["new_serial_number"];
        unset($data["new_serial_number"]);
        $data["assigned_datetime"] = date("Y-m-d H:i:s");


        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/master_devices'), $fileName);
            $data['picture'] = $fileName;
        }
        $data["device_id"] = $request->new_serial_number;
        $data["status_id"] = 2;
        unset($data["attachment"]);
        unset($data["editId"]);
        unset($data["login_user_id"]);
        unset($data["login_user_type"]);


        $isExist = Device::where('serial_number', $request->new_serial_number)->first();


        if ($request->filled("editId")) {


            if ($isExist === null || $request->editId == $isExist->id) {
                $record = Device::where("id", $request->editId)->update($data);

                (new ActivityController())->recordNewActivity(
                    $request,
                    "update",
                    "Device '{$data['serial_number']}' is Updated. ",
                    $request->editId,
                    "devices",
                    null,
                    $request->customer_id
                );
                return $this->response(' Serial Number is Updated Successfully', $data, true);
            } else {
                return ["status" => false, "errors" => ["serial_number" => ["Serial Number is already in use."]]];
            }
        } else {
            if ($isExist === null) {


                $company = Company::find($request->company_id);
                $maxDevices = $company->max_devices;

                $totalAvailable = Device::where("company_id", $request->company_id)

                    ->where("model_number", "!=", "Manual")
                    ->where("model_number",  'not like', "%Mobile%")
                    ->where("name",  'not like', "%Manual%")
                    ->where("name",  'not like', "%manual%")
                    ->count();

                if ($maxDevices - $totalAvailable <= 0 && $company->account_type == "company") {
                    return $this->response('Device limit reached. Max Devices :' . $maxDevices, null, false);
                }







                $record = Device::create($data);

                (new ActivityController())->recordNewActivity(
                    $request,
                    "create",
                    "New Device '{$data['serial_number']}' is Created. ",
                    $record->id,
                    "devices",
                    null,
                    $request->customer_id
                );
                return $this->response('New Serial Number is created Successfully.', $record, true);
            } else {
                return ["status" => false, "errors" => ["serial_number" => ["Serial Number is already in use."]]];
            }
        }

        return ["status" => false, "errors" => ["serial_number" => ["No Updates"]]];
        //
    }
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'company_id' => 'nullable',
    //         'device_type' => 'nullable',
    //         'master_serial_number' => 'required',
    //         'device_model' => 'required',
    //         'device_description' => 'nullable',
    //         'picture' => 'nullable',
    //     ]);


    //     $data = $request->all();
    //     $data["assigned_datetime"] = date("Y-m-d H:i:s");


    //     if ($request->hasFile('attachment')) {
    //         $file = $request->file('attachment');
    //         $fileName = time() . '.' . $file->getClientOriginalExtension();
    //         $file->move(public_path('/master_devices'), $fileName);
    //         $data['picture'] = $fileName;
    //     }
    //     unset($data["attachment"]);
    //     unset($data["editId"]);

    //     $isExist = MasterDeviceSerialNumbers::where('master_serial_number', $request->master_serial_number)->first();


    //     if ($request->filled("editId")) {


    //         if ($isExist === null || $request->editId == $isExist->id) {
    //             $record = MasterDeviceSerialNumbers::where("id", $request->editId)->update($data);
    //             return $this->response(' Serial Number is Updated Successfully', $data, true);
    //         } else {
    //             return ["status" => false, "errors" => ["master_serial_number" => ["Serial Number is already in use."]]];
    //         }
    //     } else {
    //         if ($isExist === null) {
    //             $record = MasterDeviceSerialNumbers::create($data);
    //             return $this->response('New Serial Number is created Successfully.', $record, true);
    //         } else {
    //             return ["status" => false, "errors" => ["master_serial_number" => ["Serial Number is already in use."]]];
    //         }
    //     }

    //     return ["status" => false, "errors" => ["master_serial_number" => ["No Updates"]]];
    //     //
    // }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterDeviceSerialNumbers  $masterDeviceSerialNumbers
     * @return \Illuminate\Http\Response
     */
    public function show(MasterDeviceSerialNumbers $masterDeviceSerialNumbers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterDeviceSerialNumbers  $masterDeviceSerialNumbers
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterDeviceSerialNumbers $masterDeviceSerialNumbers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MasterDeviceSerialNumbers  $masterDeviceSerialNumbers
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MasterDeviceSerialNumbers $masterDeviceSerialNumbers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterDeviceSerialNumbers  $masterDeviceSerialNumbers
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {

        $record = Device::where('id', $request->id)->delete();
        if ($record) {


            (new ActivityController())->recordNewActivity(
                $request,
                "delete",
                "Device '{$record['serial_number']}' is Deleted. ",
                $record->id,
                "devices",
                null,
                $record->customer_id
            );

            return $this->response('Serial Number is  Successfully deleted.', $record, true);
        } else {
            return $this->response('Serial Number can not delete.', null, false);
        }
    }
}

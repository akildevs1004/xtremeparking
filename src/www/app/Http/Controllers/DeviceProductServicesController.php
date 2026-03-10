<?php

namespace App\Http\Controllers;

use App\Models\DeviceProductServices;
use Illuminate\Http\Request;

class DeviceProductServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = DeviceProductServices::where("company_id", $request->company_id);

        $model->when($request->filled("common_search"), function ($q) use ($request) {

            $q->where("name", "ILIKE", "%" . $request->common_search . "%");
        });


        if ($request->filled("sortBy"))
            $model->orderby($request->sortBy, $request->sortDesc == 'true' ? 'desc' : 'asc');

        else
            $model->orderby("name", "asc");

        return $model->paginate($request->perPage ?? 10);
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
        if ($request->editId) {
            $request->validate([

                'company_id' => 'required|integer',
                'branch_id' => 'nullable',
                'editId' => 'nullable',
                'name' => 'required',
                'year_amount' => 'required|numeric',
                'quarter_amount' => 'required|numeric',
                'month_amount' => 'required|numeric',
                // 'sensor_count' => 'required|numeric',





            ]);
        } else {
            $request->validate([

                'company_id' => 'required|integer',
                'branch_id' => 'nullable',

                'name' => 'required',
                'year_amount' => 'required|numeric',
                'quarter_amount' => 'required|numeric',
                'month_amount' => 'required|numeric',
                // 'sensor_count' => 'required|numeric',
            ]);
        }


        $data =  $request->all();
        unset($data['login_user_id']);
        unset($data['login_user_type']);
        unset($data['editId']);
        if ($request->filled("editId")) {

            $isExist = DeviceProductServices::where("company_id", $request->company_id)->where("name", $request->name)->where("id", "!=", $request->editId)->count();
            if ($isExist == 0)

                $record = DeviceProductServices::where("id", $request->editId)->update($data);
            else
                return ["status" => false, "errors" => ["name" => [$request->name . " is already Exist"]]];

            return $this->response('Product Service details are updated', $record, true);
        } else {


            $isExist = DeviceProductServices::where("company_id", $request->company_id)->where("name", $request->name)->count();
            if ($isExist == 0)
                $record = DeviceProductServices::create($data);
            else

                return ["status" => false, "errors" => ["name" => [$request->name . " is already Exist"]]];
        }


        if ($record) {
            return $this->response('Product Service   is created.', $record, true);
        } else {
            return $this->response('Product Service   can not create ', null, false);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeviceProductServices  $deviceProductServices
     * @return \Illuminate\Http\Response
     */
    public function show(DeviceProductServices $deviceProductServices)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeviceProductServices  $deviceProductServices
     * @return \Illuminate\Http\Response
     */
    public function edit(DeviceProductServices $deviceProductServices)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DeviceProductServices  $deviceProductServices
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeviceProductServices $deviceProductServices)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeviceProductServices  $deviceProductServices
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request  $request, $id)
    {
        if ($id > 0) {
            $return = DeviceProductServices::where("id", $id)->delete();
            return $this->response('Product Service is deleted Successfully', null, true);
        }
    }

    public function DeviceProductServicesGroup(Request $request)
    {
        return  $services = DeviceProductServices::where("company_id", $request->company_id)->orderBy("year_amount", "desc")->get();
    }
}

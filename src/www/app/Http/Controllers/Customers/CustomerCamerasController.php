<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Controller;

use App\Models\CustomerCameras;

use Illuminate\Http\Request;

class CustomerCamerasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = CustomerCameras::where("company_id", $request->company_id)->where("customer_id", $request->customer_id)->orderBy("display_order", "ASC");



        return $model->paginate($request->perPage ?? 100);;
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
        try {
            $data = $request->validate([

                'company_id' => 'required',
                'customer_id' => 'required',
                'display_order' => 'nullable',
                'title' => 'required',
                'camera_url' => 'nullable',




            ]);



            if ($request->filled("editId")) {
                $record = CustomerCameras::where("id", $request->editId)->update($data);

                (new ActivityController())->recordNewActivity(
                    $request,
                    "update",
                    "Customer Camera '{$request->title}' URL  is Updated",
                    $request->editId,
                    "customer_cameras",
                    null,
                    $request->customer_id
                );
            } else {

                $record = CustomerCameras::create($data);

                (new ActivityController())->recordNewActivity(
                    $request,
                    "create",
                    "Customer Camera '{$request->title}' URL  is Created",
                    $record->id,
                    "customer_cameras",
                    null,
                    $request->customer_id
                );
            }



            if ($record) {
                return $this->response('Camera URL is created.', $record, true);
            } else {
                return $this->response('Camera URL can not create ', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customers\CustomerBuildingPhotos  $CustomerBuildingPhotos
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerCameras $CustomerBuildingPhotos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customers\CustomerBuildingPhotos  $CustomerBuildingPhotos
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerCameras $CustomerBuildingPhotos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customers\CustomerBuildingPhotos  $CustomerBuildingPhotos
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerCameras $CustomerBuildingPhotos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customers\CustomerBuildingPhotos  $CustomerBuildingPhotos
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if ($id > 0) {
            $contact = CustomerCameras::find($id);

            if ($contact->delete()) {

                (new ActivityController())->recordNewActivity(
                    $request,
                    "delete",
                    "Customer Camera '{$contact->title}' URL  is Deleted",
                    $id,
                    "customer_cameras",
                    null,
                    $contact->customer_id
                );

                return $this->response('Camera Details are Deleted', null, true);
            } else
                return $this->response('Camera Details are not Deleted', null, false);
            //
        }
    }
}

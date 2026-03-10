<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerBuildingPhotos\StoreRequest;
use App\Models\Customers\CustomerBuildingPhotos;
use Illuminate\Http\Request;

class CustomerBuildingPhotosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = CustomerBuildingPhotos::where("company_id", $request->company_id)->where("customer_id", $request->customer_id)->orderBy("display_order", "ASC");



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
    public function store(StoreRequest $request)
    {
        try {
            $data = $request->validated();

            if (isset($request->attachment) && $request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;

                $request->file('attachment')->move(public_path('/customers/building_photos'), $fileName);
                $data['picture'] = $fileName;
            } else if (!$request->filled("editId")) {
                return $this->response('Photo can not create ', null, false);
            }

            if ($request->filled("editId")) {
                CustomerBuildingPhotos::where("id", $request->editId)->update($data);

                (new ActivityController())->recordNewActivity(
                    $request,
                    "update",
                    "Customer    Photo  '{$data['title']}' is Updated.",
                    $request->editId,
                    "customer_building_photos",
                    null,
                    $request->customer_id
                );

                return $this->response('Photo is Updated.', null, true);
            } else {

                $record = CustomerBuildingPhotos::create($data);


                (new ActivityController())->recordNewActivity(
                    $request,
                    "create",
                    "Customer  New  Photo  '{$data['title']}' is created.",
                    $record->id,
                    "customer_building_photos",
                    null,
                    $request->customer_id
                );
            }



            if ($record) {
                return $this->response('Photo is created.', $record, true);
            } else {
                return $this->response('Photo can not create ', null, false);
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
    public function show(CustomerBuildingPhotos $CustomerBuildingPhotos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customers\CustomerBuildingPhotos  $CustomerBuildingPhotos
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerBuildingPhotos $CustomerBuildingPhotos)
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
    public function update(Request $request, CustomerBuildingPhotos $CustomerBuildingPhotos)
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
            $contact = CustomerBuildingPhotos::find($id);
            if (file_exists(public_path('/customers/building_photos') . '/' . $contact->picture_raw))
                unlink(public_path('/customers/building_photos') . '/' . $contact->picture_raw);
            if ($contact->delete()) {

                (new ActivityController())->recordNewActivity(
                    $request,
                    "delete",
                    "Customer     Photo  '{$contact['title']}' is Deleted.",
                    $id,
                    "customer_building_photos",
                    null,
                    $contact['customer_id']
                );
                return $this->response('Contact Details are Deleted', null, true);
            } else
                return $this->response('Contact Details are not Deleted', null, false);
            //
        }
    }
}

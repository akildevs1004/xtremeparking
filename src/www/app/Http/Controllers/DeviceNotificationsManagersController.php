<?php

namespace App\Http\Controllers;

use App\Models\device_notifications_managers;
use App\Models\DeviceNotificationsManagers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DeviceNotificationsManagersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = DeviceNotificationsManagers::with(["device.zone"])
            ->where("company_id", $request->company_id)
            ->where("customer_id", $request->customer_id);


        $model->when($request->filled("common_search"), function ($q) use ($request) {


            $q->where(function ($qwhere) use ($request) {
                $qwhere->where("name", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("email", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("whatsapp_number", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("serial_number", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("zone_name", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("mobile_number", "ILIKE", "%$request->common_search%");



                $qwhere->orWhereHas("device",  fn (Builder $query) => $query->where("name", "ILIKE", "%$request->common_search%"));
                $qwhere->orWhereHas("device.sensorzones",  fn (Builder $query) => $query->where("name", "ILIKE", "%$request->common_search%"));
            });
        });


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
        // Validate request inputs
        $data = $request->validate([
            'serial_number' => 'required',
            'company_id' => 'required',
            'customer_id' => 'required',

            'zone_name' => 'nullable',
            'name' => 'required',
            'mobile_number' => 'required',
            'email' => 'required',
            'whatsapp_number' => 'required',


        ]);


        try {



            if ($request->filled("editId")) {
                $record = DeviceNotificationsManagers::where("id", $request->editId)->update($data);
                return $this->response('Automation Is  Updated.', $record, true);
            } else {

                $record = DeviceNotificationsManagers::create($data);
            }

            if ($record) {
                return $this->response('Automation Is  created.', $record, true);
            } else {
                return $this->response('Automation can not  created ', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\device_notifications_managers  $device_notifications_managers
     * @return \Illuminate\Http\Response
     */
    public function show(DeviceNotificationsManagersController $device_notifications_managers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\device_notifications_managers  $device_notifications_managers
     * @return \Illuminate\Http\Response
     */
    public function edit(DeviceNotificationsManagersController $device_notifications_managers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\device_notifications_managers  $device_notifications_managers
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeviceNotificationsManagersController $device_notifications_managers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\device_notifications_managers  $device_notifications_managers
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $modelEvent = DeviceNotificationsManagers::where("id", $request->id);

        if ($modelEvent)
            $modelEvent->delete();

        return $this->response('Notification Deleted successfully', null, true);
    }
}

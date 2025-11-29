<?php

namespace App\Http\Controllers;

use App\Models\Community\Parking;
use App\Models\ParkingMembers;
use App\Models\ParkingMembersTransactions;
use App\Models\ParkingMembersVehiclesList;
use Illuminate\Http\Request;

class ParkingMembersVehiclesListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {


        $model = ParkingMembersVehiclesList::where("company_id", $request->company_id);

        $model->when($request->filled("common_search"), function ($q) use ($request) {


            $q->where(function ($qwhere) use ($request) {
                $qwhere->where("guest_first_name", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("guest_last_name", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("vehicle_number", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("guest_address", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("guest_location", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("guest_company_details", "ILIKE", "%$request->common_search%");
            });
        });

        $model->when($request->filled("member_id"), function ($q) use ($request) {



            $q->where("member_id", $request->member_id);
        });


        return $model->orderBy('created_at', 'DESC')->paginate($request->perPage);;
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

        $data =   $request->validate([

            'company_id' => 'required',
            'member_id' => 'required',
            'vehicle_number' => 'required',
            'guest_first_name' => 'nullable',
            'guest_last_name' => 'nullable',
            'guest_address' => 'nullable',
            'guest_location' => 'nullable',
            'guest_company_details' => 'nullable',
            'member_id' => 'nullable',

        ]);





        $isExit = ParkingMembersVehiclesList::where("vehicle_number", $request->vehicle_number)->exists();
        if ($isExit == 0) {
            $record = ParkingMembersVehiclesList::create($data);

            return $this->response('Parking Member Guest Details are created.', $record, true);
        } else {
            return $this->response($request->vehicle_number . ' - Parking Member Guest Vehicle is  already Exist', null, false);
        }


        return $this->response('Parking Member can not create ', null, false);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ParkingMembersVehiclesList  $parkingMembersVehiclesList
     * @return \Illuminate\Http\Response
     */
    public function show(ParkingMembersVehiclesList $parkingMembersVehiclesList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ParkingMembersVehiclesList  $parkingMembersVehiclesList
     * @return \Illuminate\Http\Response
     */
    public function edit(ParkingMembersVehiclesList $parkingMembersVehiclesList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ParkingMembersVehiclesList  $parkingMembersVehiclesList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data =   $request->validate([

            'company_id' => 'required',
            'vehicle_number' => 'required',
            'guest_first_name' => 'nullable',
            'guest_last_name' => 'nullable',
            'guest_address' => 'nullable',
            'guest_location' => 'nullable',
            'guest_company_details' => 'nullable',

        ]);





        $isExit = ParkingMembersVehiclesList::where("id", "!=", $id)->where("company_id",  $request->company_id)->where("vehicle_number", $request->vehicle_number)->exists();
        if ($isExit == 0) {
            $record = ParkingMembersVehiclesList::where("id",   $id)->where("company_id",  $request->company_id)->update($data);

            return $this->response('Parking Member Guest Details are Updated.', $record, true);
        } else {
            return $this->response($request->vehicle_number . ' - Parking Member Guest Vehicle is  already Exist', null, false);
        }


        return $this->response('Parking Member can not Update ', null, false);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ParkingMembersVehiclesList  $parkingMembersVehiclesList
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {

        if ($id > 0) {


            $return = ParkingMembersVehiclesList::where("id", $id)->where("company_id", $request->company_id)->delete();
            return $this->response('Parking Member Guest Vehicle is deleted Successfully', null, true);
        }
    }

    public function ParkingMembersAddBalance(Request $request)
    {

        $data = $request->validate([
            'company_id' => 'required',
            'member_id' => 'required',
            'guest_parking_hours_count' => 'required',
        ]);

        $member = ParkingMembers::where("id", $request->member_id)->where("company_id", $request->company_id)->first();
        if ($member) {

            $previousCount = $member->guest_parking_hours_count ?? 0;

            $transactionData = [
                'company_id' => $request->company_id,
                'member_id' => $request->member_id,
                'credit' => $request->guest_parking_hours_count,
                'notes' => "Admin Added",
                'created_datetime' => date("Y-m-d H:i:s"),


            ];
            $record = ParkingMembersTransactions::create($transactionData);

            ParkingMembers::where("id", $request->member_id)->where("company_id", $request->company_id)
                ->update(["guest_parking_hours_count" => $previousCount + $request->guest_parking_hours_count]);


            return $this->response('Parking Member Credits are Updated ',  $record, true);
        }

        return $this->response('Parking Member Credits are Not Updated ',  null, false);
    }
}

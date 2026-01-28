<?php

namespace App\Http\Controllers;

use App\Models\ParkingBlockedLogs;
use Illuminate\Http\Request;

class ParkingBlockedLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $perPage = $request->get('per_page', 10);

        // $logs = ParkingBlockedLogs::query()
        //     ->where('company_id', $request->company_id)
        //     ->orderByDesc('id')
        //     ->paginate($perPage);

        // return response()->json($logs);



        $perPage = $request->get('per_page', 10);

        $model = ParkingBlockedLogs::with(["ParkingMembers"])
            ->where('company_id', $request->company_id);

        $model->when($request->filled('common_search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('plate_number', 'ILIKE', "%$request->common_search%");
            });
        });


        $model->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('created_datetime', '>=', $request->date_from);
            $q->whereDate('created_datetime', '<=', $request->date_to);
        });
        $logs = $model->orderByDesc('id')
            ->paginate($perPage);


        return response()->json($logs);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ParkingBlockedLogs  $parkingBlockedLogs
     * @return \Illuminate\Http\Response
     */
    public function show(ParkingBlockedLogs $parkingBlockedLogs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ParkingBlockedLogs  $parkingBlockedLogs
     * @return \Illuminate\Http\Response
     */
    public function edit(ParkingBlockedLogs $parkingBlockedLogs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ParkingBlockedLogs  $parkingBlockedLogs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ParkingBlockedLogs $parkingBlockedLogs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ParkingBlockedLogs  $parkingBlockedLogs
     * @return \Illuminate\Http\Response
     */
    public function destroy(ParkingBlockedLogs $parkingBlockedLogs)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\report_notification_logs;
use App\Models\ReportNotificationLogs;
use Illuminate\Http\Request;

class ReportNotificationLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = ReportNotificationLogs::with(
            [
                "customer" =>
                function ($q) {
                    $q->withOut(["user", "devices", "primary_contact", "contacts", "secondary_contact", "profile_pictures"]);
                }
            ]
        )->where("company_id", $request->company_id)
            ->whereBetween("created_datetime", [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);


        $model->when($request->filled("filter_customer_id"), function ($q) use ($request) {
            $q->where("customer_id", $request->filter_customer_id);
        });

        $model->when($request->filled("fileter_customers_assigned"), function ($q) use ($request) {
            $q->whereIn("customer_id", $request->fileter_customers_assigned);
        });



        return   $model->orderBy("created_at", "desc")
            ->paginate($request->perPage);
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
     * @param  \App\Models\report_notification_logs  $report_notification_logs
     * @return \Illuminate\Http\Response
     */
    public function show(ReportNotificationLogs $report_notification_logs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\report_notification_logs  $report_notification_logs
     * @return \Illuminate\Http\Response
     */
    public function edit(ReportNotificationLogs $report_notification_logs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\report_notification_logs  $report_notification_logs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReportNotificationLogs $report_notification_logs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\report_notification_logs  $report_notification_logs
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReportNotificationLogs $report_notification_logs)
    {
        //
    }
}

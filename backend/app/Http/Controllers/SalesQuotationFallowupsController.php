<?php

namespace App\Http\Controllers;

use App\Models\SalesQuotationFallowups;
use App\Models\SalesQuotations;
use Illuminate\Http\Request;

class SalesQuotationFallowupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return SalesQuotationFallowups::where("company_id", $request->company_id)->where("quotation_id", $request->quotation_id)->orderBy("created_datetime", "DESC")->get();
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
        $data = $request->validate([
            "company_id" => "required",
            "quotation_id" => "required",
            "message" => "required",
            "status" => "required",
            "user_id" => "required",

        ]);


        $data["created_datetime"] = date("Y-m-d H:i:s");
        SalesQuotationFallowups::create($data);

        SalesQuotations::where("id", $request->quotation_id)->update(["fallowup_status" => $request->status]);

        return $this->response("Quotation status Updated", null, true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesQuotationFallowups  $salesQuotationFallowups
     * @return \Illuminate\Http\Response
     */
    public function show(SalesQuotationFallowups $salesQuotationFallowups)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesQuotationFallowups  $salesQuotationFallowups
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesQuotationFallowups $salesQuotationFallowups)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SalesQuotationFallowups  $salesQuotationFallowups
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SalesQuotationFallowups $salesQuotationFallowups)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesQuotationFallowups  $salesQuotationFallowups
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesQuotationFallowups $salesQuotationFallowups)
    {
        //
    }
}

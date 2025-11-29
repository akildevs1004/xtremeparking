<?php

namespace App\Http\Controllers;

use App\Models\CustomerProductServices;
use App\Models\Customers\CustomerPayments;
use App\Models\ParkingCameraLogs;
use App\Models\ParkingMembers;
use App\Models\TaxSlabs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParkingMembersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = ParkingMembers::where("company_id", $request->company_id);

        $model->when($request->filled("common_search"), function ($q) use ($request) {


            $q->where(function ($qwhere) use ($request) {
                $qwhere->where("first_name", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("last_name", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("phone", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("email", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("member_type", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("plate_number", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("plate_size", "ILIKE", "%$request->common_search%");
            });
        });


        $model->when($request->filled("filterMemberStatus"), function ($q) use ($request) {
            $q->where(function ($qwhere) use ($request) {
                $qwhere->where("is_active", $request->filterMemberStatus);
            });
        });
        $model->when($request->filled("filterMemberType"), function ($q) use ($request) {
            $q->where(function ($qwhere) use ($request) {
                $qwhere->where("member_type", $request->filterMemberType);
            });
        });

        return $model->orderBy('created_at', 'ASC')->paginate($request->perPage);;
    }

    public function membersAll(Request $request)
    {
        $model = ParkingMembers::where("company_id", $request->company_id)->where("member_type", "Membership");

        return $model->orderBy('first_name', 'ASC')->get();
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
                'editId' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'plate_size' => 'required',
                'plate_number' => 'required',
                'member_type' => 'required',
                'membership_start' => 'nullable',
                'membership_end' => 'nullable',
                'parking_slot' => 'nullable',







                'vehicle_country_region' => 'required',
                'vehicle_plate_type' => 'required',
                'vehicle_plate_color' => 'required',
                'plate_size' => 'nullable',
                'vehicle_type' => 'nullable',
                'vehicle_color' => 'nullable',
                'blocked_reason' => 'nullable',

                'password' => 'nullable',
                'confirm_password' => 'nullable',


            ]);
        } else {
            $request->validate([

                'company_id' => 'required|integer',
                'editId' => 'nullable',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'plate_size' => 'required',
                'plate_number' => 'required',

                'plate_number' => 'required',

                'member_type' => 'required',
                'membership_start' => 'nullable',
                'membership_end' => 'nullable',
                'parking_slot' => 'nullable',

                'vehicle_country_region' => 'required',
                'vehicle_plate_type' => 'required',
                'vehicle_plate_color' => 'required',
                'plate_size' => 'nullable',
                'vehicle_type' => 'nullable',
                'vehicle_color' => 'nullable',
                'blocked_reason' => 'nullable',

                'password' => 'nullable',
                'confirm_password' => 'nullable',

            ]);
        }


        if ($request->filled('password') || $request->filled('confirm_password')) {
            if ($request->password != $request->confirm_password) {

                return [
                    "status" => false,
                    "errors" => ['confirm_password' => ['Confirm password does not match']],
                ];
            }
        }


        $data =  $request->all();

        unset($data['attachment']);
        unset($data['editId']);

        unset($data['login_user_id']);
        unset($data['login_user_type']);

        if ($request->filled('membership_start'))
            if ($request->membership_start != '')
                $data['membership_start'] = date("Y-m-d", strtotime($request->membership_start));
            else   $data['membership_start'] = null;
        if ($request->filled('membership_end'))
            if ($request->membership_end != '')
                $data['membership_end'] = date("Y-m-d", strtotime($request->membership_end));
            else   $data['membership_end'] = null;


        if ($request->filled("editId")) {

            $isExit = ParkingMembers::where("plate_number", $request->plate_number)->where("id", "!=", $request->editId)->exists();
            //duplicate checking
            if (!$isExit) {

                if (isset($request->attachment) && $request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $ext = $file->getClientOriginalExtension();
                    $fileName = $request->editId . '.' . $ext;

                    $request->file('attachment')->move(public_path('/parking_members'), $fileName);
                    $data['picture'] = $fileName;
                }
                unset($data['password']);
                unset($data['confirm_password']);
                $record = ParkingMembers::where("id", $request->editId)->update($data);
                //Update user password

                if ($request->filled('password') && $request->filled('confirm_password')) {

                    $isUserExist = User::where('email', '=', $request->email)->exists();
                    if ($isUserExist) {

                        $user = User::where('email', '=', $request->email)->update([
                            'web_login_access' => true,
                            'can_login' => true,
                            'password' => Hash::make($request->password),
                        ]);
                    }
                }



                return $this->response('Parking Member   details are updated', $record, true);
            } else {
                return $this->response($request->plate_number . ' - Plate Number is already Exist', null, false);
            }
        } else {

            $isExit = ParkingMembers::where("plate_number", $request->plate_number)->exists();
            if ($isExit == 0) {

                //create user account
                if ($request->filled('password') && $request->filled('confirm_password')) {
                    unset($data['password']);
                    unset($data['confirm_password']);
                    $isUserExist = User::where('email', '=', $request->email)->first();
                    if ($isUserExist == null) {

                        $user = User::create([
                            "user_type" => "member",
                            'name' => 'null',
                            'email' => $request->email,
                            'password' => Hash::make($request->password),
                            'company_id' => $request->company_id,
                            'web_login_access' => true,
                            'can_login' => true,

                        ]);


                        $data['user_id'] = $user->id;
                    } else {
                        return [
                            "status" => false,
                            "errors" => ['email' => ['Email is already Exist']],
                        ];
                    }
                }











                $record = ParkingMembers::create($data);





                if (isset($request->attachment) && $request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $ext = $file->getClientOriginalExtension();
                    $fileName = $record->id . '.' . $ext;

                    $request->file('attachment')->move(public_path('/parking_members'), $fileName);
                    $data['picture'] = $fileName;

                    ParkingMembers::where("id", $record->id)->update(["picture" => $fileName]);
                }

                return $this->response('Parking Member   is created.', $record, true);
            } else {
                return $this->response($request->plate_number . ' - Plate number is already Exist', null, false);
            }
        }

        return $this->response('Parking Member can not create ', null, false);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ParkingMembers  $parkingMembers
     * @return \Illuminate\Http\Response
     */
    public function show(ParkingMembers $parkingMembers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ParkingMembers  $parkingMembers
     * @return \Illuminate\Http\Response
     */
    public function edit(ParkingMembers $parkingMembers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ParkingMembers  $parkingMembers
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ParkingMembers $parkingMembers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ParkingMembers  $parkingMembers
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request  $request, $id)
    {
        if ($id > 0) {

            $model = ParkingMembers::where("id", $id)->first();
            if (file_exists(public_path('/parking_members') . '/' . $model->picture_raw))
                unlink(public_path('/parking_members') . '/' . $model->picture_raw);
            $return = ParkingMembers::where("id", $id)->delete();
            return $this->response('Parking Member is deleted Successfully', null, true);
        }
    }

    public function MemberProductInvoiceSubmition(Request $request)
    {


        $request->validate(
            [
                'company_id' => 'required|integer',
                'member_id' => 'required|integer', //memberid
                'device_service_id' => 'required|integer',
                'end_date' => 'required',
                'start_date' => 'required',
                'payment_type' => 'required',
                'product_discount_price' => 'required|numeric',
                'product_final_price' => 'required|numeric',
                'product_price' => 'required|numeric',
                'mode_of_payment' => 'nullable',
                'status' =>  'nullable',
                'received_date' => 'nullable',



            ]
        );
        $totalInvoiceCount = $request->total_invoice_count;
        $data = [
            "company_id" => $request->company_id,
            "member_id" => $request->member_id,
            "device_product_service_id" => $request->device_service_id,
            "payment_type" => $request->payment_type,
            "discount" =>  $request->product_discount_price,
            "amount" =>  $request->product_final_price,
            "created_datetime" =>  date("Y-m-d H:i:s"),
            "start_date" =>  $request->start_date,
            "end_date" => $request->end_date,
            "invoices_count" => $totalInvoiceCount,

        ];
        // Calculate the bill amount before discount
        $billAmountBeforeDiscount = $request->product_final_price + $request->discount;
        $taxSlab = TaxSlabs::where('company_id', $request->company_id)
            ->where('start_price', '<=', $request->product_final_price)
            ->where('end_price', '>=', $request->product_final_price)
            ->orderByDesc('start_price')
            ->first();

        $taxPercentage = 0;
        $taxAmount = 0;

        if ($taxSlab) {
            $taxPercentage = $taxSlab->tax;
            $taxAmount = $request->product_final_price * $taxPercentage / 100;
        }


        $data = [
            ...$data,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'bill_amount_before_discount' => $billAmountBeforeDiscount,
        ];



        $record = CustomerProductServices::create($data);


        $maxId = CustomerPayments::where("company_id", $request->company_id)
            ->orderBy("invoice_count", "desc")
            ->value("invoice_count") ?? 0; // Default to 0 if no records exist


        $daysToAdd = ($request->payment_type == 'Yearly') ? 364 : (($request->payment_type == 'Quarter') ? 89 : 29);

        $date = Carbon::parse($request->start_date);

        $invoices = [];
        $invoice_id = null;
        for ($i = 1; $i <= $totalInvoiceCount; $i++) {

            $invoiceFormat = sprintf("INV%d%06d", $request->company_id, $maxId + $i);
            $invoice_date = $date->copy()->addDays($daysToAdd * ($i - 1))->format('Y-m-d');

            //eturn  $invoice_end_date = $date->copy()->addDays($daysToAdd * $i)->format('Y-m-d');

            $invoices  = [
                "company_id" => $request->company_id,
                "member_id" => $request->member_id,
                "invoice_number" => $invoiceFormat,
                "amount" => $request->product_final_price,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,


                "status" => "Pending",
                "invoice_date" => $invoice_date,
                "invoice_count" => $maxId + $i,
                "created_at" =>  date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),

                "created_datetime" =>  date("Y-m-d H:i:s"),
                "updated_datetime" => date("Y-m-d H:i:s"),
                "customer_product_service_id" => $record->id,

                "mode_of_payment" => $request->filled('mode_of_payment') ? $request->mode_of_payment : null,
                "status" => $request->filled("status") ? $request->status : "Pending",

                "received_date" => $request->filled("received_date") ? $request->received_date : null,

            ];

            $insertedId =  CustomerPayments::create($invoices);
            if ($i == 1)  $invoice_id = $insertedId->id;
        }


        //updae quotationtable invoiceId
        // if ($request->quotation_id && $request->quotation_id != 'null' && $invoice_id)
        //     SalesQuotations::where("id", $request->quotation_id)->update(["invoice_id" => $invoice_id]);


        ///////CustomerPayments::insert($invoices);
        $member = ParkingMembers::find($request->member_id);

        if ($request->status != "Pending") {
            if ($member) {
                $updateData = [
                    "membership_end"             => $request->end_date,
                    "is_active"                  => true,
                    "membership_start"                  => $request->start_date,
                    "customer_product_service_id" => $record->id
                ];

                $member->update($updateData);
            }
        }




        // $this->sendMail($invoice_id, $request->company_id);


        return $this->response("Invoices created successfully.", null, true);
    }

    public function MemberPayments(Request $request)
    {
        $rules = [
            'company_id' => 'required|integer',
            'member_id' => 'nullable',
            'invoice_number' => 'required',
            'amount' => 'required',
            'status' => 'required',
            'payment_id' => 'nullable', // Fixed typo from 'paymant_id'
            'received_date' => 'nullable',
            'mode_of_payment' => 'nullable',
            'invoice_date' => 'nullable',
            'cancel_notes' => 'nullable',





        ];

        // If received_date is present, make it required instead of nullable
        if ($request->status == 'Received') {
            $rules['received_date'] = 'required';
            $rules['mode_of_payment'] = 'required';
        }

        $request->validate($rules);
        try {


            $data = $request->all();

            unset($data['login_user_id']);
            unset($data['login_user_type']);


            unset($data['editId']);
            $data["updated_datetime"] = date("Y-m-d H:i:s");



            if ($request->filled("editId")) {

                unset($data['member_id']);

                $record = CustomerPayments::where("id", $request->editId)->update($data);

                $this->updateSubscriptionDates($request->member_id, $request->editId);

                return $this->response('Payment Details are Updated.', $record, true);
            } else {
                $data["created_datetime"] = date("Y-m-d H:i:s");
                $record = CustomerPayments::create($data);

                $this->updateSubscriptionDates($request->member_id, $request->editId);
            }

            if ($record) {
                return $this->response('Payment Details are Created.', $record, true);
            } else {
                return $this->response('Payment Details Not Created', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateSubscriptionDates($member_id, $customer_payment_id)
    {
        $member = ParkingMembers::where("id", $member_id)->first();
        if ($member) {
            $latestPayment = CustomerPayments::whereId($customer_payment_id)

                ->first();

            if ($latestPayment) {
                $latestProductService = CustomerProductServices::where("id", $latestPayment->customer_product_service_id)->first();

                $start_date = Carbon::parse($latestPayment->invoice_date);

                $end_date = (clone $start_date)->addDays(
                    $latestProductService->payment_type == 'Yearly' ? 364 : ($latestProductService->payment_type == 'Quarter' ? 89 : 29)
                );


                if ($latestProductService) {
                    ParkingMembers::where("id", $member_id)->update([
                        "is_active" => true,
                        "membership_start" => $start_date,
                        "membership_end" => $end_date,
                    ]);
                }
            }
        }
    }

    public function OpenGate1111111111111(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer',
            'event_id' => 'required|integer', //memberid
            'device_id' => 'required|integer', //memberid
            'trigger' => 'required|string', //memberid

        ]);

        if ($request->trigger == 'manual') {
            ParkingCameraLogs::where("id", $request->event_id)
                ->where("company_id", $request->company_id)

                ->update([
                    "manual_gate_opened_at" => date("Y-m-d H:i:s")
                ]);
        } else if ($request->trigger == 'automatic') {
            ParkingCameraLogs::where("id", $request->event_id)
                ->where("company_id", $request->company_id)

                ->update([
                    "automatic_gate_opened_at" => date("Y-m-d H:i:s")
                ]);
        }


        return $this->response('Gate Opened', null, true);
    }

    public function DeviceAcknowledged(Request $request)
    {
        $request->validate([

            'event_id' => 'required|integer', //memberid

        ]);
    }
}

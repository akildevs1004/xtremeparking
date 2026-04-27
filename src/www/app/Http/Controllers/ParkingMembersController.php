<?php

namespace App\Http\Controllers;

use App\Models\CustomerProductServices;
use App\Models\Customers\CustomerPayments;
use App\Models\ParkingCameraLogs;
use App\Models\ParkingMembers;
use App\Models\ParkingMembersVehiclesList;
use App\Models\TaxSlabs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;   // ✅ ADD THIS
use App\Models\Company;




use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
                $qwhere->orWhere("plate_number", "ILIKE", "%$request->common_search%");
                $qwhere->orWhere("plate_size", "ILIKE", "%$request->common_search%");
                $qwhere->orWhereHas("ParkingFamilyMembers", function ($qFamily) use ($request) {
                    $qFamily->where("plate_number", "ILIKE", "%$request->common_search%")
                        ->orWhere("plate_size", "ILIKE", "%$request->common_search%");
                });
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

        $model->when($request->filled("floor_no"), function ($q) use ($request) {
            $q->where("floor_no", $request->floor_no);
        });

        $model->when($request->filled("slot_number"), function ($q) use ($request) {
            $q->where("slot_number", $request->slot_number);
        });

        $model->when($request->filled("unit_number"), function ($q) use ($request) {
            $q->where("unit_number", $request->unit_number);
        });

        return $model->orderBy('created_at', 'desc')->paginate($request->per_page);;
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

        $messages = [
            'phone.required' => 'The phone number is mandatory.',
            'phone.regex' => 'Please enter a valid UAE phone number starting with 971.',
            'phone.min' => 'The phone number must be exactly 12 digits.',
            'phone.unique' => 'This phone number is already registered to another member.',
            'floor_no.unique' => 'This floor number is already assigned.',
            'slot_number.unique' => 'This parking number is already taken.',
            'unit_number.unique' => 'This unit number is already taken.',
        ];

        if ($request->editId) {
            $request->validate([
                'company_id' => 'required|integer',
                'editId' => 'required',
                'first_name' => 'required',
                'last_name' => 'nullable',
                'email' => 'required',
                'phone' => [
                    'required',
                    'string',
                    'regex:/^971(50|52|54|55|56|58|5|2|3|4|6|7|9)\d{7}$/',
                    // 'unique:parking_members,phone,' . $request->editId,
                    'min:12',
                    'max:12',
                ],
                'plate_size' => 'nullable',
                'plate_number' => 'required',
                'member_type' => 'required',
                'membership_start' => 'nullable',
                'membership_end' => 'nullable',
                'parking_slot' => 'nullable',
                'address' => 'nullable',
                'remarks' => 'nullable',
                'vehicle_country_region' => 'nullable',
                'vehicle_plate_type' => 'nullable',
                'vehicle_plate_color' => 'nullable',
                'plate_size' => 'nullable',
                'vehicle_type' => 'nullable',
                'vehicle_color' => 'nullable',
                'blocked_reason' => 'nullable',
                'password' => 'nullable',
                'confirm_password' => 'nullable',
                'floor_no' => 'required',
                'slot_number' => 'required|unique:parking_members,slot_number,' . $request->editId,
                // 'unit_number' => 'required|unique:parking_members,unit_number,' . $request->editId,
                'prefix' => 'required',
            ], $messages);
        } else {
            $request->validate([

                'company_id' => 'required|integer',
                'editId' => 'nullable',
                'first_name' => 'required',
                'last_name' => 'nullable',
                'email' => 'required',
                'phone' => [
                    'required',
                    'string',
                    'regex:/^971(50|52|54|55|56|58|5|2|3|4|6|7|9)\d{7}$/',
                    // 'unique:parking_members,phone',
                    'min:12',
                    'max:12',
                ],
                'plate_size' => 'nullable',
                'plate_number' => 'nullable',

                'address' => 'nullable',
                'remarks' => 'nullable',
                'member_type' => 'required',
                'membership_start' => 'nullable',
                'membership_end' => 'nullable',
                'parking_slot' => 'nullable',

                'vehicle_country_region' => 'nullable',
                'vehicle_plate_type' => 'nullable',
                'vehicle_plate_color' => 'nullable',
                'plate_size' => 'nullable',
                'vehicle_type' => 'nullable',
                'vehicle_color' => 'nullable',
                'blocked_reason' => 'nullable',

                'password' => 'nullable',
                'confirm_password' => 'nullable',

                'floor_no' => 'required',
                'slot_number' => [
                    'required',
                    'unique:parking_members,slot_number',
                ],
                // 'unit_number' => [
                //     'required',
                //     'unique:parking_members,unit_number',
                // ],
                'prefix' => 'required',
            ], $messages);
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

        unset($data['is_import_from_csv']);
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

            //create new

            $isExit = ParkingMembers::where("plate_number", $request->plate_number)->exists();
            if ($isExit == 0) {

                $isExitGuest = ParkingMembersVehiclesList::where("vehicle_number", $request->plate_number)->exists();
                if ($isExitGuest == 0) { //guest not exist

                    //create user account
                    if (($request->filled('password') && $request->filled('confirm_password')) || $request->filled("is_import_from_csv")) {
                        unset($data['password']);
                        unset($data['confirm_password']);
                        $isUserExist = User::where('email', '=', $request->email)->first();
                        if ($isUserExist == null) {

                            $user = User::create([
                                "user_type" => "member",
                                'name' => 'null',
                                'email' => $request->email,
                                'password' =>  Hash::make($request->password),
                                'company_id' => $request->company_id,
                                'web_login_access' => true,
                                'can_login' => true,

                            ]);


                            $data['user_id'] = $user->id;
                        }
                        //email id exist means, this vehicle have to create as guest or sub member list
                        else {

                            if ($request->filled("is_import_from_csv")) {


                                //create Vehicle details in Guest List


                                $ParkingMember = Parkingmembers::where('email', '=', $request->email)->first();

                                if ($ParkingMember) {

                                    $Guestdata =    [

                                        'company_id' => $request->company_id,
                                        'member_id' => $ParkingMember->id,
                                        'vehicle_number' =>   $request->plate_number,
                                        'guest_first_name' => $request->first_name,
                                        'guest_last_name' => $request->last_name,
                                        'guest_address' => $request->address,
                                        'guest_location' => null,
                                        'guest_company_details' => null,

                                        'parking_slot' => $request->parking_slot,
                                    ];

                                    $record = ParkingMembersVehiclesList::create($Guestdata);

                                    $this->exportParkingMembersJson();



                                    return $this->response('Parking Member   is created as ' . $ParkingMember->first_name . '  ' . $ParkingMember->last_name . ' Guest/Member List', $record, true);
                                }
                            } else {
                                return [
                                    "status" => false,
                                    "errors" => ['email' => ['Email is already Exist']],
                                ];
                            }
                        }
                    }
                    if (!isset($data['last_name'])) $data['last_name'] = "";

                    if ($request->filled("is_import_from_csv") || !$request->filled('membership_start')) {
                        $start_date = Carbon::parse(date("Y-m-d"));

                        $end_date = (clone $start_date)->addDays(
                            364
                        );

                        $data['membership_start'] = $start_date;
                        $data['membership_end'] = $end_date;
                    }

                    if ($request->filled('is_active')) {
                        $data['is_active'] = $request->filled('is_active');
                    } else {
                        $data['is_active'] = true;
                    }


                    $record = ParkingMembers::create($data);
                    $this->exportParkingMembersJson();

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
                    return $this->response($request->plate_number . ' -  Plate number is already Exist in Guest/Members List', null, false);
                }
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
            try {
                if (file_exists(public_path('/parking_members') . '/' . $model->picture_raw))
                    unlink(public_path('/parking_members') . '/' . $model->picture_raw);
            } catch (\Exception $e) {
            }

            $return = User::where("id", $model->user_id)->delete();
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
                $this->exportParkingMembersJson();
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

                $this->exportParkingMembersJson();

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
                    $this->exportParkingMembersJson();
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

    // public function preview(Request $request)
    // {
    //     $v = Validator::make($request->all(), [
    //         'company_id' => 'required',
    //         'file' => 'required|file|mimes:csv,txt',
    //     ]);

    //     if ($v->fails()) {
    //         return response()->json(['message' => $v->errors()->first()], 422);
    //     }

    //     $rows = $this->parseCsv($request->file('file')->getRealPath());
    //     $headerMap = [
    //         'first name' => 'first_name',
    //         'last name' => 'last_name',
    //         'flat number' => 'flat_number',
    //         'parking floor number' => 'parking_floor_number',
    //         'parking number' => 'parking_number',
    //         'email id' => 'email_id',
    //         'phone' => 'phone',
    //         'prefix' => 'prefix',
    //         'plate number' => 'plate_number',
    //         'vehicle country region' => 'vehicle_country_region',
    //         'vehicle plate color' => 'vehicle_plate_color',
    //     ];
    //     // validate headers/shape (optional strict)
    //     $required = ['first_name', 'last_name', 'phone', 'flat_number', 'parking_floor_number', 'parking_number', 'email_id', 'prefix', 'plate_number', 'vehicle_country_region', 'vehicle_plate_color'];
    //     $missing = [];
    //     if (!empty($rows)) {
    //         $keys = array_keys($rows[0]);
    //         foreach ($required as $r) {
    //             if (!in_array($r, $keys)) $missing[] = $r;
    //         }
    //     }

    //     if (!empty($missing)) {
    //         return response()->json([
    //             'message' => 'Missing required columns: ' . implode(', ', $missing),
    //         ], 422);
    //     }

    //     // Return normalized rows
    //     return response()->json([
    //         'rows' => $rows,
    //     ]);
    // }
    public function preview(Request $request)
    {
        $v = Validator::make($request->all(), [
            'company_id' => 'required',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        if ($v->fails()) {
            return response()->json([
                'message' => $v->errors()->first()
            ], 422);
        }

        // Parse CSV
        $rows = $this->parseCsv($request->file('file')->getRealPath());



        if (empty($rows)) {
            return response()->json([
                'rows' => [],
                'message' => 'Empty file'
            ]);
        }

        /*
     |--------------------------------------------------------------------------
     | Normalize headers (keep human Excel headers working)
     |--------------------------------------------------------------------------
     */
        $normalized = [];

        foreach ($rows as $row) {
            $clean = [];
            foreach ($row as $key => $value) {
                $cleanKey = trim(strtolower($key));
                $clean[$cleanKey] = is_string($value) ? trim($value) : $value;
            }
            $normalized[] = $clean;
        }

        return response()->json([
            'rows' => $normalized
        ]);
    }

    public function createFromCSV(Request $request)
    {
        $v = Validator::make($request->all(), [
            'company_id' => 'required',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $companyId = $request->company_id;

        $rows = $this->parseCsv($request->file('file')->getRealPath());

        $results = [];
        $success = 0;
        $fail = 0;

        // IMPORTANT:
        // Here we call your existing member creation logic.
        // If you already have a controller method/service, call it here.
        // Replace createMemberInternal(...) to match your system.

        foreach ($rows as $idx => $row) {
            $rowNo = $idx + 1;

            try {
                // Basic row validation
                $err = $this->validateRow($row);
                if ($err) {
                    $fail++;
                    $results[] = array_merge($row, [
                        'row_no' => $rowNo,
                        'status' => 'error',
                        'message' => $err,
                    ]);
                    continue;
                }

                // Map CSV -> payload
                $payload = [
                    'company_id' => $companyId,
                    'first_name' => trim($row['first_name'] ?? ''),
                    'last_name' => trim($row['last_name'] ?? ''),
                    'phone' => trim($row['phone'] ?? ''),
                    'email' => trim($row['email'] ?? ''),
                    'plate_number' => trim($row['plate_number'] ?? ''),
                    'member_type' => trim($row['member_type'] ?? ''),
                ];

                // Call your existing create member logic
                // You MUST replace this line to your actual creation function/service
                $created = $this->createMemberInternal($payload);

                $success++;
                $results[] = array_merge($row, [
                    'row_no' => $rowNo,
                    'status' => 'success',
                    'message' => 'Created',
                    'id' => $created['id'] ?? null,
                ]);
            } catch (Throwable $e) {
                $fail++;
                $results[] = array_merge($row, [
                    'row_no' => $rowNo,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success_count' => $success,
            'fail_count' => $fail,
            'results' => $results,
        ]);
    }

    private function validateRow(array $row): ?string
    {
        if (empty(trim($row['first_name'] ?? ''))) return 'first_name is required';
        if (empty(trim($row['phone'] ?? ''))) return 'phone is required';
        if (empty(trim($row['plate_number'] ?? ''))) return 'plate_number is required';
        // if (empty(trim($row['member_type'] ?? ''))) return 'member_type is required';
        return null;
    }
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) return [];

        $rows = [];
        $headers = null;
        $rowCount = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $rowCount++;

            // Trim + UTF-8 encode each cell
            $data = array_map(function ($v) {
                return mb_convert_encoding(trim((string)$v), 'UTF-8', 'UTF-8');
            }, $data);

            // Skip empty rows
            $nonEmptyCount = count(array_filter($data, fn($v) => $v !== ''));
            if ($nonEmptyCount === 0) continue;

            // Skip title row
            if ($rowCount === 1) continue;

            // Header row
            if ($rowCount === 2) {
                // Remove BOM from first header
                $data[0] = preg_replace('/^\x{FEFF}/u', '', $data[0]);

                // Normalize headers
                $headers = array_map(fn($h) => strtolower(preg_replace('/\s+/', ' ', $h)), $data);
                continue;
            }

            // Data rows
            $row = [];
            foreach ($headers as $i => $key) {
                if ($key === null) continue;
                $row[$key] = $data[$i] ?? '';
            }

            // Skip all-empty rows
            if (count(array_filter($row, fn($v) => $v !== '')) === 0) continue;

            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }





    // private function parseCsv(string $path): array
    // {
    //     $handle = fopen($path, 'r');
    //     if (!$handle) return [];

    //     $rows = [];
    //     $headers = null;

    //     // Map your exact CSV header labels -> snake_case keys
    //     $headerMap = [
    //         'first name' => 'first_name',
    //         'last name' => 'last_name',
    //         'flat number' => 'flat_number',
    //         'parking floor number' => 'parking_floor_number',
    //         'parking number' => 'parking_number',
    //         'email id' => 'email_id',
    //         'phone' => 'phone',
    //         'prefix' => 'prefix',
    //         'plate number' => 'plate_number',
    //         'vehicle country region' => 'vehicle_country_region',
    //         'vehicle plate color' => 'vehicle_plate_color',
    //     ];

    //     while (($data = fgetcsv($handle)) !== false) {
    //         // Header row
    //         if ($headers === null) {
    //             $headers = array_map(function ($h) use ($headerMap) {
    //                 $h = trim((string)$h);
    //                 $key = mb_strtolower($h);

    //                 // normalize multiple spaces
    //                 $key = preg_replace('/\s+/', ' ', $key);

    //                 // map to expected snake_case if known
    //                 if (isset($headerMap[$key])) {
    //                     return $headerMap[$key];
    //                 }

    //                 // fallback: basic snake_case normalization
    //                 $key = str_replace(['-', '/'], ' ', $key);
    //                 $key = preg_replace('/\s+/', '_', $key);
    //                 return $key;
    //             }, $data);

    //             continue;
    //         }

    //         if (count($data) === 1 && trim((string)$data[0]) === '') continue;

    //         $row = [];
    //         foreach ($headers as $i => $key) {
    //             $row[$key] = isset($data[$i]) ? trim((string)$data[$i]) : '';
    //         }
    //         $rows[] = $row;
    //     }

    //     fclose($handle);
    //     return $rows;
    // }

    /**
     * Replace this with your actual member create logic.
     * Example:
     *   return app(\App\Services\ParkingMemberService::class)->create($payload);
     */
    private function createMemberInternal(array $payload): array
    {
        // ---- PLACEHOLDER ----
        // Implement using your existing API/service/model.
        // For now, throw to remind you to connect it.
        // Remove this once connected.

        // Example if you have model ParkingMember:
        // $m = \App\Models\ParkingMember::create($payload);
        // return ['id' => $m->id];

        throw new \Exception("createMemberInternal() not connected to your existing member creation logic.");
    }

    public function exportParkingMembersJson()
    {
        $today = Carbon::today();

        $result = collect();

        $members = ParkingMembers::with('ParkingFamilyMembers')
            ->select('id', 'plate_number', 'is_active', 'membership_end', 'first_name', 'last_name')
            //   ->where("plate_number", "DXBZ19425")
            ->get();

        foreach ($members as $member) {

            $blocked = !$member->is_active ||
                ($member->member_end_date &&
                    Carbon::parse($member->member_end_date)->lt($today));

            // Add main plate number
            $result->push([
                'plate_number' => $member->plate_number,
                'blocked' => $blocked,
                'id' => $member->id,
                'name' => $member->first_name . ' ' . $member->last_name,
                'family_memebr' => false

            ]);
            if ($member->ParkingFamilyMembers)
                // Add family vehicles
                foreach ($member->ParkingFamilyMembers as $vehicle) {
                    $result->push([
                        'plate_number' => $vehicle->vehicle_number,
                        'blocked' => $blocked,
                        'id' => $vehicle->id,

                        'name' => $member->first_name . ' ' . $member->last_name,
                        'family_memebr' => true
                    ]);
                }
        }



        Storage::put(
            'parking_members.json',
            $result->toJson(JSON_PRETTY_PRINT)
        );

        return response()->json([
            'message' => 'File created successfully'
        ]);
    }

    public function exportAllCompaniesJson()
    {
        // Load all companies with their devices
        $companies = Company::with('devices')->get();

        foreach ($companies as $company) {
            $data = [
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'guest_vehicles' => $company->guset_vehicles,
                    'devices' => $company->devices->map(function ($device) {
                        return [
                            'id' => $device->id,
                            'name' => $device->name,
                            'camera_in_name' => $device->camera_in_name,
                            'camera_out_name' => $device->camera_out_name,
                        ];
                    }),
                ]
            ];

            // Save each company to a separate JSON file
            $filename = 'company_' . $company->id . '.json';
            Storage::put($filename, json_encode($data, JSON_PRETTY_PRINT));
        }

        return response()->json([
            'message' => 'All companies exported successfully.'
        ]);
    }
}

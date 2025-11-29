<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\WhatsappController;
use App\Http\Requests\Customer\UpdateRequest;
use App\Http\Requests\Customer\StoreRequest;
use App\Mail\EmailContentDefault;
use App\Models\AlarmEvents;
use App\Models\Company;
use App\Models\Customers\AlarmSensorTypes;
use App\Models\Customers\CustomerBuildingPictures;
use App\Models\Customers\CustomerContacts;
use App\Models\Customers\CustomerContactTypes;
use App\Models\Customers\CustomerPayments;
use App\Models\Customers\Customers;
use App\Models\Customers\Tickets;
use App\Models\CustomersBuildingTypes;
use App\Models\Deivices\DeviceZones;
use App\Models\Device;
use App\Models\MasterDeviceSerialNumbers;
use App\Models\ReportNotificationLogs;
use App\Models\SalesQuotations;
use App\Models\SecurityCustomers;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Hamcrest\Arrays\IsArray;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToArray;

use function PHPSTORM_META\map;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = Customers::with(["photos", "profilePictures", "buildingtype", "mappedsecurity", "devices.sensorzones", "contacts", "primary_contact", "secondary_contact", "cameras"])
            ->where("company_id", $request->company_id);
        //$model->where("deleted", 0);
        $model->when($request->filled("customer_id"), function ($q) use ($request) {
            $q->where("id", $request->customer_id);
        });
        $model->when($request->filled("eventFilter"), function ($q) use ($request) {
            $q->where("end_date", "<", date("Y-m-d"));
        });
        $model->when($request->filled("fileter_customers_assigned"), function ($q) use ($request) {
            $q->whereIn("id", $request->fileter_customers_assigned);
        });





        $model->when($request->filled("filterSecuritymapped"), function ($q) use ($request) {
            $q->whereHas("mappedsecurity", function ($qq) use ($request) {

                $qq->where("security_id", $request->filterSecuritymapped);
            });
        });


        $model->when($request->filled("common_search"), function ($q) use ($request) {
            $q->where(function ($qwhere) use ($request) {
                $search = $request->common_search;
                $qwhere->where("building_name", "ILIKE", "%$search%");
                $qwhere->orWhere("house_number", "ILIKE", "%$search%");
                $qwhere->orWhere("street_number", "ILIKE", "%$search%");
                $qwhere->orWhere("area", "ILIKE", "%$search%");
                $qwhere->orWhere("city", "ILIKE", "%$search%");
                $qwhere->orWhere("state", "ILIKE", "%$search%");
                $qwhere->orWhere("country", "ILIKE", "%$search%");
                $qwhere->orWhere("landmark", "ILIKE", "%$search%");

                $qwhere->orWhereHas("buildingtype",  fn(Builder $query) => $query->where("name", "ILIKE", "%$request->common_search%"));
            });
        });


        return $model->orderBy('building_name', "asc")->paginate($request->perPage);;
    }

    public function customerinfo(Request $request)
    {
        $model = Customers::with(["user", "photos", "buildingtype", "devices.sensorzones", "contacts", "primary_contact", "secondary_contact"])
            ->where("company_id", $request->company_id);

        $model->where("id", $request->customer_id);



        return $model->first();;
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
    public function store(Request  $request)
    {

        try {
            $data = $request->validate([
                'company_id' => "required",
                "building_type_id" => "required",
                'building_name' => "required",
                'house_number' => 'nullable',
                'street_number' => "nullable",
                'city' => "required",
                'state' => "required",
                'country' => "required",
                'landmark' => "nullable",
                'latitude' => "nullable",
                'longitude' => "nullable",
                'start_date' => "nullable",
                'end_date' => "nullable",
                'email' => "required",
                'password' => "required",
                'area' => "nullable",

            ]);

            $isExist = Customers::where('email', '=', $request->email)->first();
            if ($isExist == null) {

                $data["created_date"] = date("Y-m-d");
                //$data["password"] = Hash::make($data["password"]);



                if (isset($request->attachment) && $request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $ext = $file->getClientOriginalExtension();
                    $fileName = time() . '.' . $ext;

                    $request->file('attachment')->move(public_path('/customers/building'), $fileName);
                    $data['profile_picture'] = $fileName;
                }

                if ($request->filled("customer_id")) {
                    $record = Customers::where("id", $request->customer_id)->update($data);



                    (new ActivityController())->recordNewActivity(
                        $request,
                        "update",
                        "Customer details are updated",
                        $request->customer_id,
                        "customers",
                        null,
                        $request->customer_id
                    );
                } else {
                    $isExist = User::where('email', '=', $request->email)->first();
                    if ($isExist == null) {

                        $user = User::create([
                            "user_type" => "customer",
                            'name' => 'null',
                            'email' => $data['email'],
                            'password' => Hash::make($data["password"]),
                            'company_id' => $request->company_id,
                            'web_login_access' => 1,
                        ]);


                        $data['user_id'] = $user->id;
                    } else {
                        return $this->response('User Email is already Exist', null, false);
                    }
                    unset($data["password"]);
                    $record = Customers::create($data);

                    (new ActivityController())->recordNewActivity(
                        $request,
                        "create",
                        "New Customer '{$data['email']}' is Created. ",
                        $record->id,
                        "customers",
                        null,
                        $record->id
                    );
                }



                if ($record) {

                    //create primary contact
                    if ($request->filled("first_name") && $request->first_name != ''  && $request->first_name != 'null') {
                        $contact = [
                            "company_id" => $request->company_id,
                            "customer_id" => $record->id,
                            "address_type" => "primary",
                            "email" => $request->email,
                            "first_name" => $request->first_name,
                            "last_name" => $request->last_name,
                            "phone1" => $request->contact_number,
                            "whatsapp" => $request->whatsapp,
                        ];

                        $contact = CustomerContacts::create($contact);

                        (new ActivityController())->recordNewActivity(
                            $request,
                            "create",
                            "New Customer '{$data['building_name']}'  Primary contact is Created",
                            $contact->id,
                            "customer_contacts",
                            null,
                            $record->id
                        );
                    }
                    if ($request->filled("quotation_id") && $request->quotation_id != ''  && $request->quotation_id != 'null') {
                        $data = [
                            "customer_id" =>  $record->id,
                            "updated_datetime" => date("Y-m-d H:i:s")
                        ];

                        SalesQuotations::where("id", $request->quotation_id)->update($data);

                        (new ActivityController())->recordNewActivity(
                            $request,
                            "create",
                            "Sales Quotation is updated with Customer Id {$record->id} ",
                            $request->quotation_id,
                            "sales_quotations",
                            null,
                            $record->id
                        );
                    }








                    return $this->response('Customer New Account is created.', $record, true);
                } else {
                    return $this->response('Customer New Account can not create ', null, false);
                }
            } else {

                return [
                    "message" => 'Customer Email is already Exist',
                    "errors" => [
                        "email" => [
                            'Customer Email is already Exist'
                        ]
                    ]
                ];
                return $this->response('Customer Email is already Exist', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function show(Customers $customers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function edit(Customers $customers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest  $request) {}
    public function updateCustomer(UpdateRequest  $request)
    {
        try {
            $data = $request->validated();
            unset($data["customer_id"]);

            $isExist = Customers::where('id', '=', $request->customer_id)->first();
            if ($isExist) {



                if (isset($request->attachment) && $request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $ext = $file->getClientOriginalExtension();
                    $fileName = time() . '.' . $ext;

                    $request->file('attachment')->move(public_path('/customers/building'), $fileName);
                    $data['profile_picture'] = $fileName;
                }

                if ($request->filled("customer_id")) {
                    $record = Customers::where("id", $request->customer_id)->update($data);


                    (new ActivityController())->recordNewActivity(
                        $request,
                        "update",
                        "Customer  Address Details are Updated",
                        $request->customer_id,
                        "customers",
                        null,
                        $request->customer_id
                    );
                }

                if ($record) {
                    return $this->response('Customer   Account is Updated.', $record, true);
                } else {
                    return $this->response('Customer   Account can not Updated ', null, false);
                }
            } else {

                return [
                    "message" => 'Customer Email is already Exist',
                    "errors" => [
                        "email" => [
                            'Customer Email is already Exist'
                        ]
                    ]
                ];
                return $this->response('Customer Email is already Exist', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customers $customers)
    {
        //
    }
    public function updatebuildingContacts(Request $request)
    {
        $data1 = $request->all();
        unset($data1['login_user_id']);
        unset($data1['login_user_type']);

        $data["first_name"] = $data1["first_name"];
        $data["last_name"] = $data1["last_name"];
        $data["company_id"] = $data1["company_id"];
        $data["customer_id"] = $data1["customer_id"];
        $data["address_type"] = $data1["address_type"] ?? "building";
        $data["phone1"] = $data1["phone1"];
        $data["phone2"] = $data1["phone2"];
        $data["office_phone"] = $data1["office_phone"];
        $data["email"] = $data1["email"];
        $data["whatsapp"] = $data1["whatsapp"];



        return   $this->updateContact($data, $request, "building");
    }
    public function updateSecondaryContacts(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'company_id' => 'required',
            'customer_id' => 'required',
            'address_type' => 'nullable',
            'phone2' => 'nullable',
            'office_phone' => 'nullable',
            'whatsapp' => 'nullable',

            'phone1' => 'required',
            'mobile_number' => 'nullable',
            'email' => 'nullable',
            'alarm_stop_pin' => 'required',


        ]);
        $data1 = $request->all();
        unset($data1['login_user_id']);
        unset($data1['login_user_type']);
        $data["first_name"] = $data1["first_name"];
        $data["last_name"] = $data1["last_name"];
        $data["company_id"] = $data1["company_id"];
        $data["customer_id"] = $data1["customer_id"];
        $data["address_type"] = $data1["address_type"] ?? "secondary";
        $data["phone1"] = $data1["phone1"];
        $data["phone2"] = $data1["phone2"];
        $data["office_phone"] = $data1["office_phone"];
        $data["email"] = $data1["email"];
        $data["whatsapp"] = $data1["whatsapp"];
        $data["display_order"] = 2;
        return   $this->updateContactPrimary($data, $request, "secondary");
    }
    public function deleteDeviceZoneIndividual(Request $request)
    {


        if ($request->filled('id')) {
            DeviceZones::where("id", $request->id)->delete();
        }

        $this->response("Sensor Deleted Successfully", null, true);
    }
    public function deleteCustomer(Request $request)
    {


        if ($request->filled('customer_id')) {
            Device::where("customer_id", $request->customer_id)->delete();
            AlarmEvents::where("customer_id", $request->customer_id)->delete();

            Customers::where("id", $request->customer_id)->delete();

            (new ActivityController())->recordNewActivity(
                $request,
                "delete",
                "Customer  Account '{$request->customer_name}' is Deleted.  ",
                $request->customer_id,
                "customers",
                null,
                $request->customer_id
            );

            ///////Customers::where("id", $request->customer_id)->update(["deleted" => true]);
        }

        $this->response("Customer Deleted Successfully", null, true);
    }



    public function createDeviceZoneIndividual(Request $request)
    {



        $data = $request->validate([
            'zone_code' => 'required',
            'location' => 'required',

            'sensor_name' => 'required',
            'area_code' => 'nullable',
            'hours24' => 'nullable',


            'device_id' => 'required',

            'sensor_type' => 'nullable',
            'serial_number' => 'nullable',


        ]);

        $data = $request->all();
        unset($data['login_user_id']);
        unset($data['login_user_type']);

        unset($data['editId']);
        unset($data['sensor_name_other']);
        unset($data['sensor_type_other']);

        unset($data['login_user_id']);
        unset($data['login_user_type']);




        //verify same zone name exist in Table
        $deviceZoneDuplicate = DeviceZones::where("device_id", $request->device_id)
            ->where("zone_code", $request->zone_code);

        if ($deviceZoneDuplicate->count() == 0) {
            $deviceZone = DeviceZones::create($data);

            (new ActivityController())->recordNewActivity(
                $request,
                "create",
                "New Device Sensor Zone '{$request->location}' is created",
                $deviceZone->id,
                "device_zones",
                null,
                $request->customer_id
            );
        } else {
            return $this->response("Zone Number " . $request->zone_code . " is already Exist", null, false);
        }


        return $this->response("Zone Details are Created successfully", null, true);
    }
    public function updateDeviceZoneIndividual(Request $request)
    {



        $data = $request->validate([
            'zone_code' => 'required',
            'location' => 'required',

            'sensor_name' => 'required',
            'area_code' => 'nullable',
            'hours24' => 'nullable',

            'device_zone_id' => 'required',
            'device_id' => 'required',
            'sensor_type' => 'nullable',
            'serial_number' => 'nullable',


        ]);

        $data = $request->all();
        unset($data['login_user_id']);
        unset($data['login_user_type']);
        if ($request->filled('device_zone_id')) {

            unset($data['editId']);
            unset($data['device_zone_id']);
            $deviceZone = DeviceZones::where("id", $request->device_zone_id);






            if ($deviceZone) {


                //verify same zone name exist in Table
                $deviceZoneDuplicate = DeviceZones::where("device_id", $request->device_id)
                    ->where("zone_code", $request->zone_code)
                    ->where("id", "!=", $request->device_zone_id);

                if ($deviceZoneDuplicate->count() == 0) {
                    $deviceZone->update($data);

                    (new ActivityController())->recordNewActivity(
                        $request,
                        "create",
                        "New Device Sensor Zone '{$request->location}' is created",
                        $deviceZone->id,
                        "device_zones",
                        null,
                        $request->customer_id ?? null
                    );
                } else {
                    return $this->response("Zone Number " . $request->zone_code . " is already Exist", null, false);
                }


                return $this->response("Zone Details are updated successfully", null, true);
            } else {
                return $this->response("Zone Details are Not Available", null, false);
            }
        } else {
            return $this->response("Zone ID is missing ", null, false);
        }
    }
    public function updateDeviceZones(Request $request)
    {
        $zones = $request->sensorzones;
        $device_id = $request->device_id;
        $company_id = $request->company_id;

        if ($device_id > 0) {
            DeviceZones::where("company_id", $company_id)->where("device_id", $device_id)->delete();

            foreach ($zones as $key => $value) {
                if ($value["sensor_name"] != '') {

                    $data = [
                        "sensor_name" => $value["sensor_name"],
                        "wired" =>  $value["wired"],
                        "location" =>  $value["location"],
                        "area_code" =>  $value["area_code"],
                        "zone_code" => $value["zone_code"],
                        "device_id" => $device_id,
                        "company_id" => $company_id,
                        "delay" =>  $value["delay"],
                        "hours24" => $value["hours24"] ?? '',
                    ];
                    $deviceZone = DeviceZones::create($data);

                    (new ActivityController())->recordNewActivity(
                        $request,
                        "create",
                        "New Device Sensor Zone {$value['location']} is created",
                        $deviceZone->id,
                        "device_zones",
                        null,
                        $request->customer_id ?? null
                    );
                }
            }
            return $this->response('Zone Details Updated Successfully', null, true);
        } else {
            return $this->response('Zone Details Not updated', null, false);
        }

        return $this->response('Zone Details Updated Successfully', null, true);
    }
    public function customerDeviceTypes(Request $request)
    {
        // Fetch distinct device types
        $deviceTypes = Device::where('company_id', $request->company_id)
            ->where('customer_id', $request->customer_id)
            ->distinct()
            ->pluck('device_type');

        // Fetch sensor names
        $sensorNames = DeviceZones::whereHas('device', function ($query) use ($request) {
            $query->where('company_id', $request->company_id)
                ->where('customer_id', $request->customer_id);
        })->pluck('sensor_name');

        // Merge the two collections and get distinct values
        $mergedCollection = $deviceTypes->merge($sensorNames)->unique();

        // Convert to array if needed
        return  $distinctValues = $mergedCollection->toArray();
    }
    public function customerDeviceSensorNames(Request $request)
    {


        $sensorNames = DeviceZones::whereHas('device', function ($query) use ($request) {
            $query->where('company_id', $request->company_id)
                ->where('customer_id', $request->customer_id);
        })
            ->with('sensor_types:id,name,image')
            ->get();


        $sensors = $sensorNames->map(function ($value) {
            if ($value->sensor_types) {
                return [
                    "name" => $value->sensor_types->name,
                    "image" => $value->sensor_types->image,
                ];
            }
            return null;
        })->filter()->values();

        return $sensors;
    }
    public function updateCustomerSettings(Request $request)
    {



        if ($request->filled("customer_id") && $request->filled("company_id")) {

            $user_data = [];
            $customer_data = [];
            $customer_data["close_time"] = $request->close_time;
            $customer_data["open_time"] = $request->open_time;

            $user_data["web_login_access"] = $request->web_login_access;
            if ($request->filled("password") && $request->password != '') {
                if ($request->password == $request->password_confirmation) {
                    $user_data["password"] = Hash::make($request->password);
                } else {

                    return [
                        "message" => 'Confirm Passsword is not matching password',
                        "errors" => [
                            "password_confirmation" => [
                                'Confirm Passsword is not matching password'
                            ]
                        ]
                    ];
                }
            }


            User::where("id", $request->user_id)->update(
                $user_data
            );




            Customers::where("id", $request->customer_id)->update($customer_data);

            (new ActivityController())->recordNewActivity(
                $request,
                "update",
                "Customer Settings are updated",
                $request->customer_id,
                "device_zones",
                null,
                $request->customer_id ?? null
            );
        }

        return $this->response('Customer Setting Updated Successfully', null, true);
    }
    public function deleteCustomerContact(Request $request)
    {



        if ($request->filled('contact_id')) {

            $contact = CustomerContacts::where("id", $request->contact_id)->first();;
            if ($contact) {



                if ($contact->picture_raw) {
                    if (file_exists(public_path('/customers/contacts') . '/' . $contact->picture_raw))
                        unlink(public_path('/customers/contacts') . '/' . $contact->picture_raw);
                }
                if ($contact->delete()) {

                    (new ActivityController())->recordNewActivity(
                        $request,
                        "delete",
                        "Customer Contact is deleted",
                        $request->contact_id,
                        "customer_contacts",
                        null,
                        $request->customer_id ?? null
                    );

                    return $this->response('Contact Details are Deleted', null, true);
                } else
                    return $this->response('Contact Details are not Deleted', null, false);
                //
            }
        }
        return $this->response("Contact Delated Successfully", null, true);
    }
    public function updateComponentContactsUpdate(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'company_id' => 'required',
            'customer_id' => 'required',
            'address_type' => 'required',
            'phone2' => 'nullable',
            'office_phone' => 'nullable',
            'whatsapp' => 'nullable',

            'phone1' => 'required',
            'mobile_number' => 'nullable',
            'email' => 'nullable',
            'alarm_stop_pin' => 'nullable',
            'distance' => 'nullable',


        ]);
        $data1 = $request->all();

        unset($data1['login_user_id']);
        unset($data1['login_user_type']);
        $data = [];
        $data["distance"] = $data1["distance"] ?? null;
        $data["first_name"] = $data1["first_name"];
        $data["last_name"] = $data1["last_name"];
        $data["company_id"] = $data1["company_id"];
        $data["customer_id"] = $data1["customer_id"];
        $data["address_type"] = $data1["address_type"];
        if (isset($data1["phone1"]))
            $data["phone1"] = $data1["phone1"];

        if (isset($data1["phone2"]))
            $data["phone2"] = $data1["phone2"];
        if (isset($data1["office_phone"]))
            $data["office_phone"] = $data1["office_phone"];

        if (isset($data1["email"]))
            $data["email"] = $data1["email"];

        if (isset($data1["whatsapp"]))
            $data["whatsapp"] = $data1["whatsapp"];


        // $data["phone2"] = $data1["phone2"] ?? null;
        // $data["office_phone"] = $data1["office_phone"] ?? null;
        // $data["email"] = $data1["email"] ?? null;
        // $data["whatsapp"] = $data1["whatsapp"] ?? null;
        if (isset($data1["alarm_stop_pin"]))
            if ($data1["alarm_stop_pin"] == 'null') {
                $data["alarm_stop_pin"] = null;
            } else {
                $data["alarm_stop_pin"] = $data1["alarm_stop_pin"];
            }



        //$data["display_order"] = 1;
        $data["address"] = $data1["address"] ?? null;

        $data["latitude"] = $data1["latitude"] ?? null;
        $data["longitude"] =  $data1["longitude"] ?? null;
        $data["working_hours"] =  $data1["working_hours"] ?? null;
        $data["distance"] =  $data1["distance"] ?? null;
        $data["notes"] =  $data1["notes"] ?? null;


        if (strtolower($data["address_type"]) == 'primary') {
            $data["address_type"] = 'primary';
            $data["display_order"] = 1;
        }
        if (strtolower($data["address_type"]) == 'secondary') {
            $data["address_type"] = 'secondary';
            $data["display_order"] = 2;
        }


        $addressTypes = $this->addressTypes($request);


        if (!$request->filled("editId") && !isset($data["display_order"])) {
            if (!is_array($addressTypes)) {
                $addressTypes = $addressTypes->toArray();
            }
            $displayOrders = array_column($addressTypes, 'display_order');
            $data["display_order"] = max($displayOrders) + 1;
        }

        return   $this->updateContact($data, $request, $data["address_type"]);
    }
    public function updatePrimaryContacts(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'company_id' => 'required',
            'customer_id' => 'required',
            'address_type' => 'nullable',
            'phone2' => 'nullable',
            'office_phone' => 'nullable',
            'whatsapp' => 'nullable',

            'phone1' => 'required',
            'mobile_number' => 'nullable',
            'email' => 'nullable',
            'alarm_stop_pin' => 'required',


        ]);
        $data1 = $request->all();
        unset($data1['login_user_id']);
        unset($data1['login_user_type']);

        $data["first_name"] = $data1["first_name"];
        $data["last_name"] = $data1["last_name"];
        $data["company_id"] = $data1["company_id"];
        $data["customer_id"] = $data1["customer_id"];
        $data["address_type"] = $data1["address_type"] ?? "primary";
        $data["phone1"] = $data1["phone1"];
        $data["phone2"] = $data1["phone2"] ?? '---';;
        $data["office_phone"] = $data1["office_phone"] ?? '---';;
        $data["email"] = $data1["email"] ?? '---';;
        $data["whatsapp"] = $data1["whatsapp"] ?? '---';;
        $data["alarm_stop_pin"] = $data1["alarm_stop_pin"];
        $data["display_order"] = 1;

        return   $this->updateContactPrimary($data, $request, "primary");
    }
    public function updateCustomerContact(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'company_id' => 'required',
            'customer_id' => 'required',
            'address_type' => 'nullable',
            'phone2' => 'nullable',
            'office_phone' => 'nullable',
            'whatsapp' => 'nullable',

            'phone1' => 'required',
            'mobile_number' => 'nullable',
            'email' => 'nullable',



        ]);

        if (!$data) return false;

        $data1 = $request->all();
        unset($data1['login_user_id']);
        unset($data1['login_user_type']);
        //$data["display_order"] = 1;
        $data["address"] = isset($data1["address"]) ? $data1["address"] : '---';
        $data["first_name"] = isset($data1["first_name"]) ? $data1["first_name"] : '---';
        $data["last_name"] = isset($data1["last_name"]) ? $data1["last_name"] : '---';
        $data["company_id"] = isset($data1["company_id"]) ? $data1["company_id"] : '---';
        $data["customer_id"] = isset($data1["customer_id"]) ? $data1["customer_id"] : '---';
        $data["address_type"] = isset($data1["address_type"]) ? $data1["address_type"] : '---';
        $data["phone1"] = isset($data1["phone1"]) ? $data1["phone1"] : '---';
        $data["phone2"] = isset($data1["phone2"]) ? $data1["phone2"] : '---';
        $data["office_phone"] = isset($data1["office_phone"]) ? $data1["office_phone"] : '---';
        $data["email"] = isset($data1["email"]) ? $data1["email"] : '---';
        $data["whatsapp"] = isset($data1["whatsapp"]) ? $data1["whatsapp"] : '---';
        $data["latitude"] = isset($data1["latitude"]) ? $data1["latitude"] : '---';
        $data["longitude"] = isset($data1["longitude"]) ? $data1["longitude"] : '---';
        $data["working_hours"] = isset($data1["working_hours"]) ? $data1["working_hours"] : '---';
        $data["distance"] = isset($data1["distance"]) ? $data1["distance"] : '---';
        $data["notes"] = isset($data1["notes"]) ? $data1["notes"] : '---';




        $addressTypes = $this->addressTypes($request);


        if (!$request->filled("editId")) {
            if (!is_array($addressTypes)) {
                $addressTypes = $addressTypes->toArray();
            }
            $displayOrders = array_column($addressTypes, 'display_order');
            $data["display_order"] = max($displayOrders) + 1;

            // $filtered_data = array_filter($addressTypes, function ($item) use ($request) {
            //     return strtolower($item["name"]) == strtolower($request->address_type);
            // });
            // $singleArray = array();

            // foreach ($filtered_data as $key => $value) {
            //     $singleArray[] = $value;
            // }

            // if ($singleArray && $singleArray[0]) {
            //     $data["display_order"] = $filtered_data[0]["display_order"];
            // } else {

            //     $displayOrders = array_column($addressTypes, 'display_order');
            //     $data["display_order"] = max($displayOrders) + 1;
            // }
        }


        return   $this->updateContact($data, $request, $request->address_type);
    }

    public function updateContactPrimary($data, $request, $type)
    {

        if ((int)$request->customer_id <= 0 || (int)$request->company_id <= 0) {
            return $this->response('Customer Id and Company Ids are not exist', null, false);
        }
        try {
            //$data = $request->all();


            if ($request->filled("editId")) {
                $record = CustomerContacts::where('company_id',   $request->company_id)
                    ->where('customer_id',   $request->customer_id)
                    ->where('id',   $request->editId)->update($data);


                (new ActivityController())->recordNewActivity(
                    $request,
                    "update",
                    "Customer Contact is updated",
                    $request->editId,
                    "customer_contacts",
                    null,
                    $request->customer_id
                );


                return $this->response('Customer ' . $type . ' Contacts Updated.', $record, true);
            } else {

                $customerPrimaryContact = CustomerContacts::where('company_id',   $request->company_id)
                    ->where('customer_id',   $request->customer_id)
                    ->where('address_type',  $type)
                    ->first();

                if (isset($request->profile_picture) && $request->hasFile('profile_picture')) {
                    $file = $request->file('profile_picture');
                    $ext = $file->getClientOriginalExtension();
                    $fileName = time() . '.' . $ext;

                    $request->file('profile_picture')->move(public_path('/customers/contacts'), $fileName);
                    $data['profile_picture'] = $fileName;
                }

                if ($customerPrimaryContact != null) {

                    $record = CustomerContacts::where('company_id',   $request->company_id)
                        ->where('customer_id',   $request->customer_id)
                        ->where('address_type',   $type)->update($data);

                    (new ActivityController())->recordNewActivity(
                        $request,
                        "update",
                        "Customer Contact '{$type}' is updated",
                        $record->id,
                        "customer_contacts",
                        null,
                        $request->customer_id
                    );

                    if ($record) {
                        return $this->response('Customer ' . $type . ' Contacts Updated ', $record, true);
                    } else {
                        return $this->response('Customer  ' . $type . '  Contacts Not Updated', null, false);
                    }
                } else {


                    $data['company_id'] =  $request->company_id;
                    $data['customer_id'] = $request->customer_id;
                    $data['address_type'] = $type;
                    $record = CustomerContacts::create($data);

                    (new ActivityController())->recordNewActivity(
                        $request,
                        "create",
                        "New Customer Contact is Created. Type:" . $type,
                        $record->id,
                        "customers",
                        null,
                        $request->customer_id
                    );

                    if ($record) {
                        return $this->response('Customer ' . $type . ' Contacts Created.', $record, true);
                    } else {
                        return $this->response('Customer ' . $type . ' Contacts Not Created', null, false);
                    }
                }
            }
        } catch (\Throwable $th) {
            return $this->response('Customer ' . $type . ' Error', null, false);
            throw $th;
        }
    }

    public function updateContact($data, $request, $type)
    {



        if ((int)$request->customer_id <= 0 || (int)$request->company_id <= 0) {
            return $this->response('Customer Id and Company Ids are not exist', null, false);
        }
        try {
            //$data = $request->all();
            if (isset($request->profile_picture) && $request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;

                $request->file('profile_picture')->move(public_path('/customers/contacts'), $fileName);
                $data['profile_picture'] = $fileName;
            }


            if ($request->filled("editId")) {
                $record = CustomerContacts::where('id',   $request->editId)->update($data);


                (new ActivityController())->recordNewActivity(
                    $request,
                    "update",
                    "Customer Contact '{$data['address_type']}' is updated.",
                    $request->editId,
                    "customer_contacts",
                    null,
                    $request->customer_id
                );




                return $this->response('Customer ' . $type . ' Contacts Updated', $record, true);
            } else {

                // $customerPrimaryContact = CustomerContacts::where('company_id',   $request->company_id)
                //     ->where('customer_id',   $request->customer_id)
                //     ->where('address_type',  $type)->first();




                // if (!$customerPrimaryContact) {

                $data['company_id'] =  $request->company_id;
                $data['customer_id'] = $request->customer_id;
                $data['address_type'] = $type;
                $record = CustomerContacts::create($data);


                (new ActivityController())->recordNewActivity(
                    $request,
                    "create",
                    "Customer Contact '{$type}' is Created",
                    $record->id,
                    "customer_contacts",
                    null,
                    $request->customer_id ?? null
                );



                if ($record) {
                    return $this->response('  ' . $type . ' Contacts Created .', $record, true);
                } else {
                    return $this->response('  ' . $type . ' Contacts Not Created', null, false);
                }
                // } else {


                //     $record = CustomerContacts::where("id", $customerPrimaryContact->id)->update($data);
                //     return $this->response('  ' . $type . ' Contacts is Updated', null, true);
                // }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function buildingTypes()
    {

        return CustomersBuildingTypes::orderBy("name", "asc")->get();
        // $data = [
        //     ["name" => "Commercial", "id" => 1],
        //     ["name" => "Residencial", "id" => 2],
        //     ["name" => "Semi Commercial", "id" => 3],
        //     ["name" => "Inventory", "id" => 4],
        // ];
        // return $data;
    }
    public function getCustomerTemperatureDevices(Request $request)
    {
        $data = Device::with(["sensorzones"])
            ->where('company_id', $request->company_id)
            ->where('customer_id', $request->customer_id)
            ->where(function ($query) {
                $query->where("device_type", "Temperature")
                    ->orWhere(function ($query) {
                        $query->where("device_type", "Intruder")
                            ->whereHas('sensorzones', function ($query) {
                                $query->where('sensor_name', 'Temperature');
                            });
                    });
            })
            ->get();
        return $data;
    }
    public function deviceModels()
    {
        $data = [
            // "OX-866",
            // "OX-886",
            // "OX-966",
            // "OX-900",
            "XT-CPANEL",
            "H700-TAB",
            // "XG-808",
            // "XT-FIRE",
            // "XT-WATER"
        ];
        return $data;
    }
    public function SecurityCustomersListUpdate(Request $request)
    {

        if ($request->filled("security_id")) {
            SecurityCustomers::where("security_id", $request->security_id)->delete();

            foreach ($request->customers as $customer_id) {

                if ((int)$customer_id) {
                    $data = ["company_id" => $request->company_id, "security_id" => $request->security_id, "customer_id" => $customer_id];

                    $record = SecurityCustomers::create($data);

                    (new ActivityController())->recordNewActivity(
                        $request,
                        "create",
                        "Customer is Mapped with Security",
                        $record->id,
                        "security_customers",
                        null,
                        $customer_id
                    );
                }
            }
        }
        return $this->response('Customer Details are updated', null, true);
    }
    public function SecurityCustomersSingleUpdate(Request $request)
    {
        if ($request->security_id == null) {
            SecurityCustomers::where("customer_id", $request->customer_id)->delete();
        } else   if ($request->filled("security_id") && $request->filled("customer_id")) {
            SecurityCustomers::where("customer_id", $request->customer_id)->delete();


            $data = [
                "company_id" => $request->company_id,
                "security_id" => $request->security_id,
                "customer_id" => $request->customer_id
            ];

            $record = SecurityCustomers::create($data);

            (new ActivityController())->recordNewActivity(
                $request,
                "create",
                "Customer is Mapped with Security",
                $record->id,
                "security_customers",
                null,
                $request->customer_id
            );
        }
        return $this->response('Customer Details are updated', null, true);
    }
    public function resetCustomerPin(Request $request)
    {

        $pin = rand(100000, 999999);

        $model = CustomerContacts::where("company_id", $request->company_id)
            ->where("customer_id", $request->customer_id)
            ->where("address_type", $request->contact_type);

        $contact = $model->first();

        $model->update(["alarm_stop_pin" => $pin]);


        (new ActivityController())->recordNewActivity(
            $request,
            "update",
            "Customer Alarm Stop PIN is updated",
            $contact->id,
            "customer_contacts",
            null,
            $request->customer_id
        );




        if ($contact->email != '') {
            $date = date("Y-m-d H:i:s");

            $body_content1 = " Hello, {$contact->first_name} <br/>";

            $body_content1 .= "This is Notifing you about Alarm Pin Reset status <br/><br/>";
            $body_content1 .= "New PIN :  {$pin}<br/><br/>";

            $body_content1 .= "Date:  $date<br/><br/><br/>";

            $body_content1 .= "*Xtreme Guard*<br/>";


            $data = [
                'subject' => "Alarm Pin Reset Notification",
                'body' => $body_content1,
            ];


            $body_content1 = new EmailContentDefault($data);


            Mail::to($contact->email)
                ->queue($body_content1);

            //tanjore mail settings
            $data["to"] = $contact->email;
            (new ClientController())->sendExternalMail($data);

            //whatsapp
            $company = Company::where("id", $request->company_id)->first();

            $body_content = " Hello, *{$contact->first_name}* \n";
            $body_content .= "This is Notifing you about Alarm Pin Reset status \n\n";
            $body_content .= "New PIN :  *{$pin}*\n\n";
            $body_content .= "Date:  $date<br/>\n\n";
            $body_content .=
                "Regards,\n" . env("MAIL_FROM_NAME");

            $response = (new WhatsappController())->sendWhatsappNotification(
                $company,
                $body_content,
                $contact->whatsapp,
                []
            );
        }

        return $this->response('Customer  PIN updated', null, true);
    }
    public function getCustomersList(Request $request)
    {
        $model = Customers::where("company_id", $request->company_id);

        $model->when($request->filled("filter_customers_list"), function ($model) use ($request) {
            $model->whereIn('id', $request->filter_customers_list);
        });

        return $model->orderBy('building_name', "ASC")->get();;
    }
    public function alarmTypes()
    {


        $data = [
            ["name" => "Intruder", "id" => "Intruder"],
            ["name" => "SOS", "id" => "SOS"],
            ["name" => "Offline", "id" => "Offline"],
            ["name" => "Tampered", "id" => "Tampered"],
            ["name" => "AC Off", "id" => "AC_off"],
            ["name" => "DC Off", "id" => "DC_off"],

            //["name" => "Burglary", "id" => "Burglary"],
            ["name" => "Medical", "id" => "Medical"],
            ["name" => "Temperature", "id" => "Temperature"],
            ["name" => "Water", "id" => "Water"],
            //["name" => "Humidity", "id" => "Humidity"],
            ["name" => "Fire", "id" => "Fire"],

            // ["name" => "All(Attendance and Access)", "id" => "all"],
            // ["name" => "Attendance", "id" => "Attendance"],
            // ["name" => "Access Control", "id" => "Access Control"],

        ];
        return $data;
    }
    public function deviceTypes()
    {
        $data = [
            ["name" => "Intruder", "id" => "Intruder"],
            //["name" => "Burglary", "id" => "Burglary"],
            ["name" => "Medical", "id" => "Medical"],
            ["name" => "Temperature", "id" => "Temperature"],
            ["name" => "Water", "id" => "Water"],
            //["name" => "Humidity", "id" => "Humidity"],
            ["name" => "Fire", "id" => "Fire"],

            // ["name" => "All(Attendance and Access)", "id" => "all"],
            // ["name" => "Attendance", "id" => "Attendance"],
            // ["name" => "Access Control", "id" => "Access Control"],

        ];
        return $data;
    }

    public function getDeviceZonesList(Request $request)
    {
        $model = DeviceZones::where("company_id", $request->company_id)->where("device_id", $request->deviceId);


        return $model->orderBy("zone_code", "ASC")->paginate($request->per_page ?? 10);
    }

    public function getCustomerDevicesSensorZones(Request $request)
    {
        $model = DeviceZones::with("device")->where("company_id", $request->company_id)->whereHas("device", function ($query) use ($request) {
            $query->where("customer_id", $request->customer_id);
        });


        return $model->orderBy("zone_code", "ASC")->get();
    }

    public function getSensorsList()
    {

        return AlarmSensorTypes::orderBy("name", "asc")->pluck("name");
        // return  [

        //     "Burglary",
        //     "Medical",
        //     "Fire",
        //     "Water",
        //     "Temperature",
        // ];
    }
    // public function deviceTypes()
    // {
    //     $data = [

    //         ["name" => "Burglary", "id" => "Burglary"],
    //         ["name" => "Medical", "id" => "Medical"],
    //         ["name" => "Temperature", "id" => "Temperature"],
    //         ["name" => "Water", "id" => "Water"],
    //         ["name" => "Humidity", "id" => "Humidity"],

    //         ["name" => "All(Attendance and Access)", "id" => "all"],
    //         ["name" => "Attendance", "id" => "Attendance"],
    //         ["name" => "Access Control", "id" => "Access Control"],

    //     ];
    //     return $data;
    // }
    public function addressTypes(Request $request)
    {
        // $data = [
        //     ["name" => "Police Station", "display_order" => 1],
        //     ["name" => "Fire/Civil Department", "display_order" => 2],
        //     ["name" => "Hopsital", "display_order" => 3],
        //     ["name" => "Building Security", "display_order" => 4],
        //     ["name" => "Community Security", "display_order" => 5],
        // ];
        return   $data = CustomerContactTypes::orderBy("name", "asc")->get();

        // Fetch addressTypes from the database
        $addressTypes = CustomerContacts::where("company_id", $request->company_id)->where("display_order", ">", 0)
            ->get(["address_type AS name", "display_order"])
            ->toArray();

        // Merge the predefined data with the addressTypes from the database
        $mergedData = array_merge($data, $addressTypes);

        // Remove duplicates based on the 'name' key
        $mergedData = array_map("unserialize", array_unique(array_map("serialize", $mergedData)));

        // Sort by 'display_order'
        usort($mergedData, function ($a, $b) {
            return $a['display_order'] <=> $b['display_order'];
        });

        return $mergedData;
    }

    public function alarmCategories()
    {
        return  $data = [
            ["name" => "Critical", "id" => 2],
            ["name" => "Normal", "id" => 1],


        ];
    }
    public function alarmCustomersForMap(Request $request)
    {
        $model = Customers::with([
            "alarm_events",
            "latest_alarm_event.device",
            "devices.sensorzones",
            "contacts",
            "primary_contact",
            "secondary_contact"
        ])
            ->whereHas("alarm_events")
            ->where("company_id", $request->company_id);

        $model->when($request->filled("commonSearch"), function ($query) use ($request) {

            return $query->where("building_name", "ILIKE", "$request->commonSearch%")
                ->orWhere("house_number", "ILIKE", "$request->commonSearch%")
                ->orWhere("area", "ILIKE", "$request->commonSearch%");
        });

        $model->when($request->filled("filter_customers_list"), function ($model) use ($request) {
            $model->whereIn('id', $request->filter_customers_list);
        });
        $model->when($request->filled("customer_id"), fn($q) => $q->where("id", $request->customer_id));

        return $model->orderByDesc('id')->paginate($request->perPage);
    }
    public function customersForMapOperator(Request $request)
    {
        $model = Customers::with(
            [
                "alarm_events",
                "all_alarm_events",
                "devicesOffline",
                "devicesOnline",
                "latest_alarm_event",
                "device.customer.photos",
                "devices.sensorzones",
                "contacts",
                "primary_contact",
                "secondary_contact"
            ]
        )
            //->whereHas("alarm_events")
            ->where("company_id", $request->company_id);
        $model->withCount("all_alarm_events");
        // $model->has("alarm_events", '>', 0);

        $model->withCount("devicesOffline");
        $model->withCount("devicesOnline");
        $model->when($request->filled("common_search"), function ($query) use ($request) {

            return $query->where("building_name", "ILIKE", "$request->common_search%")
                ->orWhere("house_number", "ILIKE", "$request->common_search%")
                ->orWhere("area", "ILIKE", "$request->common_search%");
        });

        $model->when($request->filled("filter_customers_list"), function ($model) use ($request) {
            $model->whereIn('id', $request->filter_customers_list);
        });
        $model->when($request->filled("customer_id"), fn($q) => $q->where("id", $request->customer_id));
        $model->when(
            $request->filled("filter_text"),
            function ($q) use ($request) {
                if (strtolower($request->filter_text) == 'alarm') {
                    $q->has("all_alarm_events", '>', 0);
                }
                if (strtolower($request->filter_text) == 'online') {
                    $q->wherehas("devices", function ($q) use ($request) {
                        $q->where("status_id", 1);
                    });
                }
                if (strtolower($request->filter_text) == 'offline') {
                    $q->wherehas("devices", function ($qq) use ($request) {
                        $qq->where("status_id", 2);
                    });
                }
                if (strtolower($request->filter_text) == 'armed') {
                    $q->wherehas("devices", function ($qq) use ($request) {
                        $qq->where("armed_status", 1);
                    });
                }
                if (strtolower($request->filter_text) == 'disarm') {
                    $q->wherehas("devices", function ($qq) use ($request) {
                        $qq->where("armed_status", 0);
                    });
                }
            }



        );


        $model->when(
            $request->filled("filterBuildingType"),
            function ($q) use ($request) {
                $q->where("building_type_id", $request->filterBuildingType);
            }
        );

        $model->when(
            $request->filled('filterEventType'), // Check if filterEventType is present in the request
            function ($q) use ($request) {
                $q->whereHas('all_alarm_events', function ($query) use ($request) {
                    $query->where('alarm_status', $request->filterEventType);
                });
            }
        );

        $model->when(
            $request->filled("filterAlarmType"),
            function ($q) use ($request) {
                $q->whereHas("all_alarm_events",  function ($event) use ($request) {
                    $event->where("alarm_type", $request->filterAlarmType);
                });
            }
        );



        $model->orderBy('all_alarm_events.alarm_start_datetime', 'desc')->orderBy('devices_offline_count', 'desc');

        return $model->get();
        // return $model->paginate($request->perPage ?? 10);
    }
    public function customersForMap(Request $request)
    {
        $model = Customers::with(["alarm_events", "devicesOffline", "devicesOnline", "latest_alarm_event.device.customer", "devices.sensorzones", "contacts", "primary_contact", "secondary_contact"])
            //->whereHas("alarm_events")
            ->where("company_id", $request->company_id);
        //->where("id", 6);

        $model->withCount("alarm_events");
        // $model->has("alarm_events", '>', 0);

        $model->withCount("devicesOffline");
        $model->withCount("devicesOnline");
        $model->when($request->filled("common_search"), function ($query) use ($request) {

            return $query->where("building_name", "ILIKE", "$request->common_search%")
                ->orWhere("house_number", "ILIKE", "$request->common_search%")
                ->orWhere("area", "ILIKE", "$request->common_search%");
        });

        $model->when($request->filled("filter_customers_list"), function ($model) use ($request) {
            $model->whereIn('id', $request->filter_customers_list);
        });
        $model->when($request->filled("customer_id"), fn($q) => $q->where("id", $request->customer_id));
        $model->when(
            $request->filled("filter_text"),
            function ($q) use ($request) {
                if (strtolower($request->filter_text) == 'alarm') {
                    $q->has("alarm_events", '>', 0);
                }
                if (strtolower($request->filter_text) == 'online') {
                    $q->wherehas("devices", function ($q) use ($request) {
                        $q->where("status_id", 1);
                    });
                }
                if (strtolower($request->filter_text) == 'offline') {
                    $q->wherehas("devices", function ($qq) use ($request) {
                        $qq->where("status_id", 2);
                    });
                }
                if (strtolower($request->filter_text) == 'armed') {
                    $q->wherehas("devices", function ($qq) use ($request) {
                        $qq->where("armed_status", 1);
                    });
                }
                if (strtolower($request->filter_text) == 'disarm') {
                    $q->wherehas("devices", function ($qq) use ($request) {
                        $qq->where("armed_status", 0);
                    });
                }
            }



        );


        $model->when(
            $request->filled("filterBuildingType"),
            function ($q) use ($request) {
                $q->where("building_type_id", $request->filterBuildingType);
            }
        );

        $model->when(
            $request->filled("filterEventType"),
            function ($q) use ($request) {
                $q->whereHas("alarm_events",  function ($event) use ($request) {
                    $event->where("alarm_status", $request->filterEventType);
                });
            }
        );

        $model->when(
            $request->filled("filterAlarmType"),
            function ($q) use ($request) {
                $q->whereHas("alarm_events",  function ($event) use ($request) {
                    $event->where("alarm_type", $request->filterAlarmType);
                });
            }
        );



        $model->orderBy('alarm_events_count', 'desc')->orderBy('devices_offline_count', 'desc');

        return $model->get();
        // return $model->paginate($request->perPage ?? 10);
    }
    public function customersAll(Request $request)
    {
        $model = Customers::with(["devices.sensorzones", "contacts", "primary_contact", "secondary_contact"])->where('company_id', $request->company_id);

        return $model->orderBy('building_name', "asc")->get();
    }
    public function customerProfileCompletionPercentage(Request $request)
    {
        $customer = Customers::with(['devices'])->where('id', $request->customer_id)->first();
        $customerContactsPrimary = CustomerContacts::where('customer_id', $request->customer_id)->where('address_type', 'primary')->first();
        $customerContactsSecondary = CustomerContacts::where('customer_id', $request->customer_id)->where('address_type', 'secondary')->first();
        $customerContactsOthers = CustomerContacts::where('customer_id', $request->customer_id)->whereNotIn('address_type', ['primary', 'secondary'])->count();
        $CustomerBuildingPictures = CustomerBuildingPictures::where('customer_id', $request->customer_id)->count();

        $profilePercentage = 0;
        $message = [];

        $fields = [
            'profile_picture' => 10,
            'building_type_id' => 2,
            'building_name' => 2,
            'house_number' => 2,
            'area' => 2,
            'city' => 2,
            'landmark' => 10,
            'latitude' => 5,
            'longitude' => 5,
        ];
        foreach ($fields as $field => $weight) {
            if (!empty($customer->$field)) {
                $profilePercentage += $weight;
            }
        }
        if ($customer)
            if ($customer->latitude  == '' || $customer->longitude == '') {
                $message[] = "Google Map Latitude and Longitude are Empty";
            }


        if ($profilePercentage < 40) {
            $message[] = "Address Information Is in-complete";
        }

        if (isset($customer) && count($customer->devices) > 0) {
            $profilePercentage += 5;
        } else
            $message[] = "0 Devices";


        if ($customerContactsPrimary) {
            $profilePercentage += 10;
        } else
            $message[] = "No Primary Contact information";

        if ($customerContactsSecondary) {
            $profilePercentage += 10;
        } else
            $message[] = "No Secondary Contact information";


        if ($CustomerBuildingPictures > 0) {
            $profilePercentage += min($CustomerBuildingPictures, 5) * 5;
        } else
            $message[] = "No Building Pictures";


        if ($customerContactsOthers > 0) {
            $profilePercentage += min($customerContactsOthers, 5) * 5;
        } else
            $message[] = "No Emergency Contacts";

        $profilePercentage = min($profilePercentage, 100);
        if (count($message) == 0 && $profilePercentage != 100) {
            $message[] = "Minimum 2 Building Photos are required";
            $message[] = "Minimum 5 Emergency Contacts required";
        }

        return ["percentage" => $profilePercentage, "message" => $message];
    }

    public function getMasterDeviceSerialNumbers(Request $request)
    {
        return  Device::where("company_id", $request->company_id)
            ->where("customer_id", null)
            ->where("device_type", "!=", "Manual")
            ->where("serial_number", "!=", "Manual")
            ->where("serial_number", "!=", "Mobile")
            ->where("serial_number", "!=", "mobile")
            ->where("serial_number", "!=", null)

            ->get();
    }
    public function getCustomerNewSerialNumbers(Request $request)
    {
        return  Device::where("company_id", $request->company_id)
            ->where("customer_id", null)
            ->where("device_type", "!=", "Manual")
            ->where("serial_number", "!=", "Manual")
            ->where("serial_number", "!=", "Mobile")
            ->where("serial_number", "!=", "mobile")
            ->where("serial_number", "!=", null)

            ->get();
    }
    public function customerDevicesStats(Request $request)
    {
        $AlarmSensorTypes = AlarmSensorTypes::orderBy('id', 'asc')->get();
        //$AlarmSensorTypes = AlarmSensorTypes::limit(3)->get();

        $groupCount = Device::where('company_id', $request->company_id)
            ->select('device_type', DB::raw('COUNT(id) as total_count'))

            ->groupBy('device_type')
            ->pluck('total_count', 'device_type'); // Pluck total_count with building_type_id as the key

        $groupResults = [];

        $total = 0;

        foreach ($AlarmSensorTypes as $buildingType) {
            $groupResults[] = ["name" => $buildingType->name, "count" => $groupCount->get($buildingType->name, 0)];
            $total = $total + $groupCount->get($buildingType->name, 0);
        }

        $groupResults[] = ["name" => "total", "count" => $total];

        return $groupResults;

        // return  $groupCount = Device::where('company_id', $request->company_id)


        //     ->select('device_type', DB::raw('COUNT(id) as total_count'))
        //     ->groupBy('device_type')
        //     ->whereIn('device_type', ['Burglary', 'Medical', 'Temperature', 'Fire', 'Water'])
        //     ->pluck('total_count', 'device_type');
    }
    public function customerContractExpin30daysStats(Request $request)
    {

        $today = now();
        $thirtyDaysFromNow = now()->addDays(30);





        $businessTypes = CustomersBuildingTypes::all();

        $groupCount = Customers::where('company_id', $request->company_id)

            ->whereBetween("end_date", [$today, $thirtyDaysFromNow])
            ->select('building_type_id', DB::raw('COUNT(id) as total_count'))
            ->groupBy('building_type_id')
            ->pluck('total_count', 'building_type_id'); // Pluck total_count with building_type_id as the key

        $groupResults = [];

        foreach ($businessTypes as $buildingType) {
            $groupResults[] = ["name" => $buildingType->name, "count" => $groupCount->get($buildingType->id, 0)];
        }

        return $groupResults;
    }
    public function clearDeviceNotification()
    {
        Device::where("armed_notification1", "!=", null)
            ->update(["armed_notification1" => null, "armed_notification2" => null]);
    }
    public function verifyArmedDeviceWithShopTime()
    {
        $message = [];
        $devices = Device::with(["customer.mappedsecurity" => function ($w) {
            $w->withOut(["devices", "contacts"]);
        }])->whereHas("customer", function ($q) {
            $q->whereDate("end_date", ">=", date("Y-m-d"));
        })->where("armed_status", "!=", 1)
            ->where(function ($q) {
                $q->where("armed_notification1",   null);
                $q->Orwhere("armed_notification2",   null);
            })
            ->get();

        foreach ($devices as $key => $device) {

            $date  = new DateTime("now", new DateTimeZone($device['utc_time_zone'] != '' ? $device['utc_time_zone'] : 'Asia/Dubai'));


            if ($device->customer['close_time'] != '') {

                $sendNotification = false;

                $currentDateTime = $date->format('Y-m-d H:i:s');
                $armedShceduleDateTime = $date->format('Y-m-d ' . $device->customer['close_time'] . ':00');

                if (strtotime($currentDateTime) <= strtotime($armedShceduleDateTime)) {
                    $message[] = 'Notificaiton   not sent -  ' . $currentDateTime . ' - Close Time ' . $armedShceduleDateTime;

                    return $message;
                }


                $currentDateTimeObj = new DateTime($currentDateTime);
                $armedScheduleDateTimeObj = new DateTime($armedShceduleDateTime);

                $currentDateTimeFormatted = $currentDateTimeObj->format('Y-m-d H:i:s');

                $difference = $currentDateTimeObj->diff($armedScheduleDateTimeObj);
                // $sign = $difference->invert === 1 ? -1 : 1;

                $totalMinutes =  ($difference->d * 24 * 60 + $difference->h * 60) + $difference->i;
                $cc_emails = $device['customer']['user']['email'];


                $armed_notification1 = new DateTime($device->armed_notification1 != '' ? $device->armed_notification1 : "1970-01-01 00:00:00");
                $armed_notification2 = new DateTime($device->armed_notification2 != '' ? $device->armed_notification2 : "1970-01-01 00:00:00");

                $difference1 = $currentDateTimeObj->diff($armed_notification1, true);
                $difference2 = $currentDateTimeObj->diff($armed_notification2, true);

                $message[] = ($difference1->d * 24 * 60 + $difference1->h * 60) + $difference1->i;
                $message[] = ($difference1->d * 24 * 60 + $difference2->h * 60) + $difference2->i;

                if (($difference1->d * 24 * 60 + $difference1->h * 60) + $difference1->i > (24 * 60) ||
                    ($difference2->d * 24 * 60 + $difference2->h * 60) + $difference2->i > (24 * 60)
                ) {
                    $sendNotification = true;
                }


                if ($sendNotification) {

                    if ($totalMinutes >= 15 && $totalMinutes < 30 && $device->armed_notification1 == '') {
                        Device::where("serial_number", $device->serial_number)->update([
                            "armed_notification1" => $currentDateTime
                        ]);
                        $message[] = 'Notificaiton   sent -  ' . $totalMinutes;
                    } else if ($totalMinutes >= 30 && $totalMinutes < 60 && $device->armed_notification2 == '') {
                        Device::where("serial_number", $device->serial_number)->update([
                            "armed_notification2" => $currentDateTime
                        ]);


                        Device::where("serial_number", $device->serial_number)
                            ->where("armed_notification1", null)
                            ->update([
                                "armed_notification2" => $currentDateTime,
                                "armed_notification1" => $currentDateTime
                            ]);

                        $message[] = 'Notificaiton   sent -  ' . $totalMinutes;
                    } else {
                        $message[] = 'Notificaiton Not sent - Duration is out of Time ' . $totalMinutes;

                        $sendNotification = false;
                    }


                    if ($sendNotification) {
                        //send Warning Notification

                        if ($device->customer->primary_contact) {
                            $this->sendArmedWarningMail($device->customer->primary_contact, $device, $currentDateTimeFormatted, $cc_emails);
                            $this->sendArmedWarningWhatsapp($device->customer->primary_contact, $device, $currentDateTimeFormatted, $cc_emails);
                        }

                        if ($device->customer->secondary_contact) {
                            $this->sendArmedWarningMail($device->customer->secondary_contact, $device, $currentDateTimeFormatted, $cc_emails);
                            $this->sendArmedWarningWhatsapp($device->customer->secondary_contact, $device, $currentDateTimeFormatted, $cc_emails);
                        }


                        if ($device->customer->mappedsecurity) {
                            $this->sendArmedWarningMail($device->customer->mappedsecurity, $device, $currentDateTimeFormatted, $cc_emails);
                            $this->sendArmedWarningWhatsapp($device->customer->mappedsecurity, $device, $currentDateTimeFormatted, $cc_emails);
                        }
                    } else {
                        $message[] = 'Notificaiton Not sent - Duration is out of Time ' . $totalMinutes;
                    }
                } else {
                    $message[] = 'Notificaiton is Already Sent at ' . $device->armed_notification1 . ' - ' . $device->armed_notification2;
                }
            }
        }






        return $message;
    }

    public function sendArmedWarningMail($contact, $device, $currentDateTime, $cc_emails)
    {
        $body_content1 = '';

        $email = $contact->email;
        if ($email == '') return '';
        $whatsapp_number = $contact['contact_number'];
        $location = "{$device['customer']['latitude']},{$device['customer']['longitude']}";


        $body_content1 = "Hello, <strong>{$contact->first_name} {$contact->last_name}</strong><br/>";
        $body_content1 .= "Building: {$device->customer->building_name}<br/><br/>";
        $body_content1 .= "<strong>This is notifying you that the device armed is not active</strong><br/>";
        $body_content1 .= "Scheduled Store Close/Armed Time: {$device->customer->close_time}<br/>";
        $body_content1 .= "Event Time: {$currentDateTime}<br/>";
        $body_content1 .= "Priority: Low<br/><br/>";
        $body_content1 .= "Property: {$device->customer->buildingtype->name}<br/>";
        $body_content1 .= "Address: {$device->customer->street_number}, {$device->customer->area}, {$device->customer->city}, {$device->customer->state}<br/>";
        if ($contact)
            $body_content1 .= "Contact Number: {$contact->phone1}<br/><br/>";
        $body_content1 .= "Google Map Link: <a href='https://maps.google.com/?q={$location}'>View on Google Maps</a><br/><br/><br/>";
        $body_content1 .= "Thanks,<br/>Xtreme Guard<br/>";


        $logData = [
            "company_id" => $device->company_id,
            'subject' => "Device Armed is not Active. Scheduled Time: " . $device->customer->close_time,
            "email" => $email,
            "created_datetime" => $currentDateTime,
            "customer_id" => $device->customer_id,
        ];
        ReportNotificationLogs::create($logData);
        $emailData = [
            'subject' => "Device Armed is not Active. Scheduled Time: " . $device->customer->close_time,
            'body' => $body_content1,
        ];
        $body_content1 = new EmailContentDefault($emailData);
        try {
            if ($email != '')
                Mail::to($email)
                    ->cc($cc_emails)
                    ->queue($body_content1);

            //tanjore mail settings
            $emailData["to"] = $email;
            (new ClientController())->sendExternalMail($emailData);


            //whatsapp
            $company = Company::where("id", $device->company_id)->first();
            $body_content = "Hello, *{$contact->first_name} {$contact->last_name}*\n";
            $body_content .= "Building: {$device->customer->building_name}\n";
            $body_content .= "*This is notifying you that the device armed is not active*\n";
            $body_content .= "Scheduled Store Close/Armed Time: {$device->customer->close_time}\n";
            $body_content .= "Event Time: {$currentDateTime}\n";
            $body_content .= "Priority: Low\n";
            $body_content .= "Property: {$device->customer->buildingtype->name}\n";
            $body_content .= "Address: {$device->customer->street_number}, {$device->customer->area}, {$device->customer->city}, {$device->customer->state}\n";
            if ($contact)
                $body_content .= "Contact Number: {$contact->phone1}\n";
            $body_content .= "Google Map Link: <a href='https://maps.google.com/?q={$location}'>View on Google Maps</a>\n\n";

            $body_content .=
                "Regards,\n" . env("MAIL_FROM_NAME");

            $response = (new WhatsappController())->sendWhatsappNotification(
                $company,
                $body_content,
                $contact->whatsapp,
                []
            );
        } catch (\Exception $e) {

            Log::error("Email sending failed: " . $e->getMessage());
        }



        return $body_content1;
    }
    public function sendArmedWarningWhatsapp($contact, $device, $currentDateTime, $cc_emails)
    {


        $body_content1 = '';


        $email = $contact->email;
        $whatsapp_number = $contact['contact_number'];
        if ($whatsapp_number == '') return '';

        $location = "{$device['customer']['latitude']},{$device['customer']['longitude']}";


        $body_content1 = "Hello,  {$contact->first_name} {$contact->last_name}\n\n";
        $body_content1 .= "Building: {$device->customer->building_name}\n";
        $body_content1 .= "*This is notifying you that the device armed is not active*\n";
        $body_content1 .= "Scheduled Store Close/Armed Time: {$device->customer->close_time}\n";
        $body_content1 .= "Event Time: {$currentDateTime}\n";
        $body_content1 .= "Priority: Low\n\n";
        $body_content1 .= "Property: {$device->customer->buildingtype->name}\n";
        $body_content1 .= "Address: {$device->customer->street_number}, {$device->customer->area}, {$device->customer->city}, {$device->customer->state}\n";
        if ($contact->phone1)
            $body_content1 .= "Contact Number: {$contact->phone1}\n\n";
        $body_content1 .= "Google Map Link:  https://maps.google.com/?q={$location} \n\n\n";
        $body_content1 .= "Thanks,\nXtreme Guard\n";


        $logData = [
            "company_id" => $device->company_id,
            'subject' => "Device Armed is not Active. Scheduled Time: " . $device->customer->close_time,
            "whatsapp_number" => $whatsapp_number,
            "created_datetime" => $currentDateTime,
            "customer_id" => $device->customer_id,
        ];
        ReportNotificationLogs::create($logData);
        // $emailData = [
        //     'subject' => "Device Armed is not Active. Scheduled Time: " . $device->customer->close_time,
        //     'body' => $body_content1,
        // ];
        // $body_content1 = new EmailContentDefault($emailData);


        try {
            if ($whatsapp_number != '') (new WhatsappController())->sendWhatsappNotification($device['company'], $body_content1, $whatsapp_number, []);
        } catch (\Exception $e) {

            Log::error("Whatsapp sending failed: " . $e->getMessage());
        }


        return $body_content1;
    }

    public function GetCustomerTicketsStatistics(Request $request)
    {



        $tickets = Tickets::where("customer_id", $request->customer_id)->get();


        $deviceCounts = Device::where("company_id", $request->company_id)

            // ->whereHas('customer', function ($query) {
            //     $query->where('deleted', 0);
            // })
            ->where("device_type", "!=", "Mobile")

            ->where("device_type", "!=", "Manual")
            ->where("device_id", "!=", "Manual")
            ->where("serial_number", "!=", null)

            ->when($request->filled("filter_customers_list"), function ($q) use ($request) {
                $q->whereIn("customer_id", $request->filter_customers_list);
            })
            ->when($request->filled("customer_id"), function ($q) use ($request) {
                $q->where("customer_id", $request->customer_id);
            })



            ->selectRaw("
        COUNT(CASE WHEN armed_status = 1 THEN 1 END) as armedCount,
        COUNT(CASE WHEN armed_status = 0 THEN 1 END) as disarmCount,
        COUNT(CASE WHEN status_id = 1 THEN 1 END) as onlineCount,
        COUNT(CASE WHEN status_id = 2 THEN 1 END) as offlineCount
    ")->first();

        return [
            "openCount" => $tickets->where("status", 1)->count(),
            "closedCount" => $tickets->where("status", 0)->count(),

            "onlineCount" => $deviceCounts->onlinecount,
            "offlineCount" => $deviceCounts->offlinecount,
            "forwardCount" => 0
        ];
    }

    public function GetCustomerMaintenanceInfo(Request $request)
    {


        $nextMaintenanceDue = null;
        $lastMaintenanceDate = null;
        $invoiceDue = 0;

        $customer = Customers::with(["devices"])->where("id", $request->customer_id)->first();


        if ($customer && $customer->start_date != '') {

            $startDate = $customer->start_date;

            $customDate = Carbon::parse($startDate);
            $currentDate = Carbon::now();

            $monthDifference = $customDate->diffInMonths($currentDate); // Get month difference
            $newDate = $customDate->addMonths($monthDifference); // Add difference to custom date

            $nextMaintenanceDue = $newDate->toDateString();
            $daysDifference = $newDate->diffInDays($currentDate);
            if ($daysDifference > 0) {
                $nextMaintenanceDue = $newDate->copy()->addMonth()->toDateString();
            }
        }

        $maintenance = Tickets::where("customer_id", $request->customer_id)
            ->where("category_id", 3) //3 is maintenance
            ->orderby("created_datetime", "desc")->first();

        if ($maintenance) {
            $lastMaintenanceDate = $maintenance->created_datetime;
        }

        $invoice = CustomerPayments::where("customer_id", $request->customer_id)
            ->where("status", 'Pending')

            ->selectRaw("SUM(amount) as amount")->first();

        if ($invoice) {
            $invoiceDue = $invoice->amount ?? 0;
        }


        return  [
            "next_maintenance_due_date" => $nextMaintenanceDue,
            "last_maintenance_date" => $lastMaintenanceDate,
            "invoice_due" => $invoiceDue,
            "devices" =>  $customer->devices
        ];
    }
}

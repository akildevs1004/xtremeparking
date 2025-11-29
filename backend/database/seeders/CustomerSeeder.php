<?php

namespace Database\Seeders;

use App\Models\AlarmEvents;
use App\Models\Customers\CustomerBuildingPictures;
use App\Models\Customers\CustomerContacts;
use App\Models\Customers\Customers;
use App\Models\Deivices\DeviceZones;
use App\Models\Device;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    protected $faker;
    protected $customer_id;
    protected $CompanyId;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // php artisan db:seed --class=CustomerSeeder

        // if (env("APP_ENV") == "production") {
        //     return;
        // }

        $this->faker = Faker::create();

        $this->CompanyId = 1;

        $CustomerLoopCount = 1;
        $DeviceLoopCount = 1;
        $DeviceZones = [
            "Burglary",
            "Medical",
            "Temperature",
            "Water",
            "Fire"
        ];

        // Customers::truncate();
        // CustomerContacts::truncate();
        // CustomerBuildingPictures::truncate();
        // Device::truncate();
        // DeviceZones::truncate();
        // AlarmEvents::truncate();

        foreach (range(1, $CustomerLoopCount) as $_) {

            $location = $this->getRandomLocationsWithLatLon();

            $customer = Customers::create([
                'company_id' => $this->CompanyId,
                'start_date' => $this->faker->date,
                'end_date' => $this->faker->date,
                'email' => $this->faker->unique()->safeEmail,
                'password' => Hash::make('password'),

                'house_number' => $this->faker->buildingNumber,
                'building_name' => $this->faker->company,
                'building_type_id' => Arr::random([1, 4]),

                'country' => "UAE",
                'city' => "Dubai",
                'state' => "UAE",
                'area' => $location['name'],
                'latitude' => $location['lat'],
                'longitude' => $location['lon'],
                'street_number' => $location['name'] . ", Dubai UAE",
                'landmark' => "----",
            ]);

            $this->customer_id = $customer->id;

            CustomerContacts::insert([
                $this->getContact("primary"),
                $this->getContact("secondary"),
            ]);

            CustomerBuildingPictures::create([
                'company_id' => $this->CompanyId,
                'customer_id' => $customer->id,
                'title' => "Test",
                'display_order' => 0,
                'picture' => "dummy-floor-plan.png",
            ]);

            foreach (range(1, $DeviceLoopCount) as $key => $value) {
                $device = Device::create($this->getDevice());

                foreach ($DeviceZones as $DeviceZone) {
                    $deviceZone = [
                        "sensor_name" => $DeviceZone,
                        "wired" =>  "Wired",
                        "location" =>  $device->location,
                        "area_code" =>  substr($this->faker->uuid, 0, 6),
                        "zone_code" => Arr::random([1, 10]),
                        "device_id" => $device->id,
                        "company_id" => $this->CompanyId,
                        "delay" =>  0,
                        "hours24" => "Yes",
                    ];

                    DeviceZones::create($deviceZone);
                }

                $data = [
                    "company_id" => $this->CompanyId,
                    "serial_number" => $device->device_id,
                    "alarm_start_datetime" => date("Y-m-d H:i:s"),
                    "customer_id" => $customer->id,
                    "zone" => Arr::random(DeviceZones::pluck("id")->toArray()),
                    "area" => "test",
                    "alarm_type" => Arr::random($DeviceZones),
                    "alarm_status" => 1,
                    "created_event_from" => "customerseeder",
                ];

                AlarmEvents::create($data);
            }
        }
    }

    public function getContact($address_type)
    {
        $contact = [];
        $contact["company_id"] = $this->CompanyId;
        $contact["customer_id"] = $this->customer_id;
        $contact["email"] = $this->faker->unique()->safeEmail;
        $contact["first_name"] = $this->faker->firstName;
        $contact["last_name"] = $this->faker->lastName;
        $contact["phone1"] = $this->faker->phoneNumber;
        $contact["phone2"] = $this->faker->phoneNumber;
        $contact["office_phone"] = $this->faker->phoneNumber;
        $contact["whatsapp"] = $this->faker->phoneNumber;
        $contact["display_order"] = 0;
        $contact["address_type"] = $address_type;

        return $contact;
    }

    public function getDevice()
    {
        $contact = [];
        $contact["company_id"] = $this->CompanyId;
        $contact["customer_id"] = $this->customer_id;
        $contact["name"] = "Test Device";
        $contact["device_id"] = substr($this->faker->uuid, 0, 8); // should be device_id
        $contact["serial_number"] = substr($this->faker->uuid, 0, 8); // should be device_id
        $contact["device_type"] = Arr::random($this->getDeviceTypes());
        $contact["model_number"] = Arr::random($this->getModels()); // should be model_number
        $contact["location"] = Arr::random($this->getLocations());
        $contact["utc_time_zone"] = Arr::random($this->getTimezones());
        $contact["alarm_delay_minutes"] = 0;
        $contact["ip"] = "0.0.0.0";
        $contact["port"] = "0000";
        $contact["function"] = "auto";
        $contact["status_id"] = 1;

        return $contact;
    }

    public function getTimezones()
    {
        return [
            "America/New_York",
            "America/Los_Angeles",
            "America/Chicago",
            "America/Denver",
            "America/Anchorage",
            "America/Honolulu",
            "Pacific/Pago_Pago",
            "Pacific/Midway",
            "Pacific/Wake",
            "Pacific/Tongatapu",
            "Pacific/Kiritimati",
            "Pacific/Apia",
            "Pacific/Niue",
            "Pacific/Palau",
            "America/Argentina/Buenos_Aires",
            "America/Sao_Paulo",
            "Europe/Lisbon",
            "Europe/London",
            "Africa/Lagos",
            "Africa/Lusaka",
            "Asia/Baghdad",
            "Asia/Tehran",
            "Asia/Dubai",
            "Asia/Kabul",
            "Asia/Karachi",
            "Asia/Kolkata",
            "Asia/Kathmandu",
            "Asia/Dhaka",
            "Indian/Cocos",
            "Asia/Bangkok",
            "Asia/Shanghai",
            "Australia/Eucla",
            "Asia/Tokyo",
            "Australia/Adelaide",
            "Australia/Brisbane",
            "Australia/Lord_Howe",
            "Pacific/Honolulu"
        ];
    }

    public function getModels()
    {
        return [
            "OX-866",
            "OX-886",
            "OX-966",
            "OX-900",
            "XT-CPANEL",
            // "XT-TAB",
            "XT-FIRE",
            "XT-WATER",
        ];
    }

    public function getDeviceTypes()
    {
        return [
            "Control Panel",
            // "Burglary", "Medical", "Temperature", "Water", "Humidity", "Fire"
        ];
    }

    public function getLocations()
    {
        return [
            "Downtown Dubai",
            "Dubai Marina",
            "Jumeirah Beach Residence",
            "Palm Jumeirah",
            "Deira",
            "Bur Dubai",
            "Business Bay",
            "Al Barsha",
            "Jumeirah",
            "Dubai International Financial Centre (DIFC)",
            "Dubai Silicon Oasis",
            "Dubai Sports City",
            "Al Quoz",
            "Jumeirah Lake Towers",
            "Arabian Ranches",
            "Mirdif",
            "Dubai Festival City",
            "The Springs",
            "The Meadows",
            "The Greens"
        ];
    }

    public function getLocationsWithLatLon()
    {
        return [
            ["name" => "Downtown Dubai", "lat" => 25.1972, "lon" => 55.2744],
            ["name" => "Dubai Marina", "lat" => 25.0801, "lon" => 55.1400],
            ["name" => "Jumeirah Beach Residence", "lat" => 25.0700, "lon" => 55.1411],
            ["name" => "Palm Jumeirah", "lat" => 25.1125, "lon" => 55.1380],
            ["name" => "Deira", "lat" => 25.2673, "lon" => 55.3085],
            ["name" => "Bur Dubai", "lat" => 25.2567, "lon" => 55.2914],
            ["name" => "Business Bay", "lat" => 25.1894, "lon" => 55.2750],
            ["name" => "Al Barsha", "lat" => 25.0914, "lon" => 55.1889],
            ["name" => "Jumeirah", "lat" => 25.2072, "lon" => 55.2708],
            ["name" => "Dubai International Financial Centre (DIFC)", "lat" => 25.2241, "lon" => 55.2900],
            ["name" => "Dubai Silicon Oasis", "lat" => 25.0921, "lon" => 55.3430],
            ["name" => "Dubai Sports City", "lat" => 25.0218, "lon" => 55.2200],
            ["name" => "Al Quoz", "lat" => 25.1304, "lon" => 55.2308],
            ["name" => "Jumeirah Lake Towers", "lat" => 25.0754, "lon" => 55.1568],
            ["name" => "Arabian Ranches", "lat" => 25.0133, "lon" => 55.2542],
            ["name" => "Mirdif", "lat" => 25.2238, "lon" => 55.3992],
            ["name" => "Dubai Festival City", "lat" => 25.2408, "lon" => 55.3314],
            ["name" => "The Springs", "lat" => 25.0214, "lon" => 55.2406],
            ["name" => "The Meadows", "lat" => 25.0515, "lon" => 55.2268],
            ["name" => "The Greens", "lat" => 25.0903, "lon" => 55.2202]
        ];
    }

    public function getRandomLocationsWithLatLon()
    {
        return $this->getLocationsWithLatLon()[array_rand($this->getLocationsWithLatLon())];
    }
}

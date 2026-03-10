<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Permission::truncate();

        $data = [

            ['module' => 'dashboard', 'title' => 'Dashboard access', 'name' => 'dashboard_access'],
            ['module' => 'dashboard', 'title' => 'Dashboard view', 'name' => 'dashboard_view'],
            ['module' => 'dashboard', 'title' => 'Dashboard create', 'name' => 'dashboard_create'],
            ['module' => 'dashboard', 'title' => 'Dashboard edit', 'name' => 'dashboard_edit'],
            ['module' => 'dashboard', 'title' => 'Dashboard delete', 'name' => 'dashboard_delete'],

            ['module' => 'intruder', 'title' => 'Intruder access', 'name' => 'intruder_access'],
            ['module' => 'intruder', 'title' => 'Intruder view', 'name' => 'intruder_view'],
            ['module' => 'intruder', 'title' => 'Intruder create', 'name' => 'intruder_create'],
            ['module' => 'intruder', 'title' => 'Intruder edit', 'name' => 'intruder_edit'],
            ['module' => 'intruder', 'title' => 'Intruder delete', 'name' => 'intruder_delete'],

            ['module' => 'map', 'title' => 'Map access', 'name' => 'map_access'],
            ['module' => 'map', 'title' => 'Map view', 'name' => 'map_view'],
            ['module' => 'map', 'title' => 'Map create', 'name' => 'map_create'],
            ['module' => 'map', 'title' => 'Map edit', 'name' => 'map_edit'],
            ['module' => 'map', 'title' => 'Map delete', 'name' => 'map_delete'],

            ['module' => 'map', 'title' => 'Alarms access', 'name' => 'map_access'],
            ['module' => 'map', 'title' => 'Alarms view', 'name' => 'map_view'],
            ['module' => 'map', 'title' => 'Alarms create', 'name' => 'map_create'],
            ['module' => 'map', 'title' => 'Alarms edit', 'name' => 'map_edit'],
            ['module' => 'map', 'title' => 'Alarms delete', 'name' => 'map_delete'],





        ];

        // // run this command to seed the data => php artisan db:seed --class=PermissionSeeder
        // Permission::insert($data);



        foreach ($data as $key => $dataArray) {
            Permission::updateOrCreate(['name' => $dataArray['name']], $dataArray);
        }

        // run this command to seed the data => php artisan db:seed --class=PermissionSeeder
        //Permission::insert($data);
    }
}

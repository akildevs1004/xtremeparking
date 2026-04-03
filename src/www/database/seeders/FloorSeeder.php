<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FloorSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // The unique floor list for your system
        $floorNames = [
            'B6', 'B5', 'B4', 'B3', 'B2', 'B1', 
            'G', 'M', 
            'P1', 'P2', 'P3', 'P4', 'P5'
        ];

        foreach ($floorNames as $name) {
            DB::table('floors')->updateOrInsert(
                ['name' => $name],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
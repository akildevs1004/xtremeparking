<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParkingSlotSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 8;

        // Clean consecutive ranges for each parking floor
        $data = [
            "B1" => ["272-333"],
            "B2" => ["210-271"],
            "B3" => ["145-209"],
            "B4" => ["80-144"],
            "B5" => ["15-79"],
            "B6" => ["1-14"],
            "G"  => ["334-335", "337-359"],
            "M"  => ["360-360", "362-391"],
            "P1" => ["392-393", "395-435"],
            "P2" => ["436-493"],
            "P3" => ["494-494", "495-495", "496-496", "497-548"],
            "P4" => ["549-576"],
            "P5" => ["577-610"],
        ];

        foreach ($data as $floor => $ranges) {
            foreach ($ranges as $range) {
                [$start, $end] = explode('-', $range);

                for ($i = (int)$start; $i <= (int)$end; $i++) {
                    // Padding the number with leading zeros for consistency (e.g., B6-001)
                    $slotNumber = (string)$i;

                    DB::table('parking_slots')->updateOrInsert(
                        [
                            'slot_number' => $slotNumber,
                            'company_id'  => $companyId
                        ],
                        [
                            'floor_no'    => $floor,
                            'created_at'  => now(),
                            'updated_at'  => now()
                        ]
                    );
                }
            }
        }
    }
}
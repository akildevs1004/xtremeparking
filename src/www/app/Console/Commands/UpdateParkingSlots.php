<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateParkingSlots extends Command
{
    protected $signature = 'parking:split-slots';
    protected $description = 'Splits parking_slot into floor_no and slot_number, filtering out noise';

    public function handle()
    {
        // 1. Get only rows that have a value
        $members = DB::table('parking_members')
            ->whereNotNull('parking_slot')
            ->where('parking_slot', '!=', '')
            ->get();

        $this->withProgressBar($members, function ($member) {
            // Use regex to match the pattern: [Floor]-[Slot]
            // This grabs "P5" and "587" even if the string is "P5-587 (RENTED...)"
            if (preg_match('/^([A-Z0-9]+)-([0-9]+)/i', $member->parking_slot, $matches)) {
                
                $floorNo    = $matches[1]; // First group: P5
                $slotNumber = $matches[2]; // Second group: 587

                DB::table('parking_members')
                    ->where('id', $member->id)
                    ->update([
                        'floor_no'    => $floorNo,
                        'slot_number' => $slotNumber
                    ]);
            }
        });

        $this->newLine();
        $this->info('Update complete.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\DeviceSosRooms;
use App\Models\SecurityLogin;
use App\Models\SecuritySosRoomsList;
use Illuminate\Http\Request;

class SecuritySosRoomsListController extends Controller
{
    public function index(Request $request, SecurityLogin $security)
    {
        // Optional: company scope if you have company_id column in security_logins/device_sos_rooms
        // abort_unless($security->company_id == $request->company_id, 403);

        $assigned = $security->sosRooms()
            ->select('device_sos_rooms.id', 'device_sos_rooms.name', 'device_sos_rooms.room_id') // adjust columns
            ->orderBy('name')
            ->get();

        return response()->json([
            'security_id' => $security->id,
            'assigned' => $assigned,
        ]);
    }



    public function store(Request $request, SecurityLogin $security)
    {
        $request->validate([
            'sos_room_table_id' => ['required', 'integer'],
        ]);

        $securityUserId = (int) $security->id;
        $roomId         = (int) $request->sos_room_table_id;

        /**
         * 1. Verify SOS room exists
         */
        $roomExists = DeviceSosRooms::where('id', $roomId)->exists();

        if (!$roomExists) {
            return response()->json([
                'message' => 'Invalid SOS room',
            ], 422);
        }

        /**
         * 2. Check duplicate assignment
         */
        $alreadyAssigned = SecuritySosRoomsList::where('security_user_id', $securityUserId)
            ->where('sos_room_table_id', $roomId)
            ->exists();

        if ($alreadyAssigned) {
            return response()->json([
                'message' => 'Room already assigned',
            ], 200);
        }

        /**
         * 3. Insert new mapping
         */
        SecuritySosRoomsList::create([
            'security_user_id'  => $securityUserId,
            'sos_room_table_id' => $roomId,
        ]);

        return response()->json([
            'message' => 'Room assigned successfully',
        ], 201);
    }


    public function destroy(Request $request, SecurityLogin $security, DeviceSosRooms $room)
    {
        $security->sosRooms()->detach($room->id);

        return response()->json([
            'message' => 'Room removed successfully',
        ]);
    }

    public function roomsList(Request $request)
    {
        // Optional filters if needed (company/building/branch)
        // $companyId = $request->company_id;

        $rooms = DeviceSosRooms::query()
            ->select('id', 'name') // adjust columns
            ->orderBy('name')
            ->get();

        return response()->json($rooms);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\Parking\StoreParkingSlotsRequest;
use App\Http\Requests\Parking\UpdateParkingSlotsRequest;
use App\Models\ParkingSlots;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ParkingSlotsController extends Controller
{
    /**
     * Store newly created parking slots in storage.
     * Creates multiple slot records based on floor_no and slot number range
     *
     * @param  StoreParkingSlotsRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreParkingSlotsRequest $request)
    {
        // ParkingSlots::truncate();

        // return;

        try {
            $data = $request->validated();

            $company_id = $data['company_id'];
            $floor_no = $data['floor_no'];
            $start_number = (int) $data['start_number'];
            $end_number = (int) $data['end_number'];

            // Validate start_number is not greater than end_number
            if ($start_number > $end_number) {
                return [
                    'status' => false,
                    'message' => 'Start number cannot be greater than end number',
                    'record' => null,
                ];
            }

            // Prepare data for bulk insert
            $slots = [];
            $now = now();

            for ($i = $start_number; $i <= $end_number; $i++) {
                $slots[] = [
                    'company_id' => $company_id,
                    'floor_no' => $floor_no,
                    'slot_number' => $i,
                    'status' => 'available',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Check if slots already exist
            $existingSlots = ParkingSlots::where('company_id', $company_id)
                ->where('floor_no', $floor_no)
                ->whereBetween('slot_number', [$start_number, $end_number])
                ->count();

            if ($existingSlots > 0) {
                return [
                    'status' => false,
                    'message' => 'Some parking slots already exist for this floor and range',
                    'record' => null,
                ];
            }

            // Begin transaction for data consistency
            DB::beginTransaction();

            try {
                // Bulk insert all slots
                ParkingSlots::insert($slots);

                // Fetch created records
                $createdSlots = ParkingSlots::where('company_id', $company_id)
                    ->where('floor_no', $floor_no)
                    ->whereBetween('slot_number', [$start_number, $end_number])
                    ->orderBy('slot_number')
                    ->get();

                DB::commit();

                return $this->response(
                    'Parking slots created successfully',
                    [
                        'total_created' => $createdSlots->count(),
                        'floor_no' => $floor_no,
                        'slot_range' => "$start_number - $end_number",
                        'slots' => $createdSlots,
                    ],
                    true
                );
            } catch (Throwable $th) {
                DB::rollBack();
                throw $th;
            }
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error creating parking slots: ' . $th->getMessage(),
                'record' => null,
            ], 500);
        }
    }

    /**
     * Display parking slots listing with filters
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(\Illuminate\Http\Request $request)
    {
        try {
            $model = ParkingSlots::where('company_id', $request->company_id);

            // Filter by floor number
            if ($request->filled('floor_no')) {
                $model->where('floor_no', $request->floor_no);
            }

            // Filter by floor number
            if ($request->filled('slot_number')) {
                $model->where('slot_number', $request->slot_number);
            }

            // Filter by status
            if ($request->filled('status')) {
                $model->where('status', $request->status);
            }

            // Search by slot number
            if ($request->filled('common_search')) {
                $model->where('slot_number', 'like', $request->common_search . '%');
            }

            return $model->orderBy('floor_no')
                ->orderBy('slot_number')
                ->paginate($request->perPage ?? 50);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving parking slots: ' . $th->getMessage(),
                'record' => null,
            ], 500);
        }
    }


    /**
     * Update a specific parking slot status
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, UpdateParkingSlotsRequest $request)
    {
        try {
            // 1. Locate the specific slot
            $slot = ParkingSlots::where('id', $id)
                ->where('company_id', $request->company_id)
                ->firstOrFail();

            // 2. Perform the update with validated data
            $slot->update($request->validated());

            return $this->response('Parking slot updated successfully', $slot, true);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Parking slot not found or access denied.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specific parking slot
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, \Illuminate\Http\Request $request)
    {
        try {
            $slot = ParkingSlots::where('id', $id)
                ->where('company_id', $request->company_id)
                ->firstOrFail();

            $slot->delete();

            return $this->response('Parking slot deleted successfully', $slot, true);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting parking slot: ' . $th->getMessage(),
                'record' => null,
            ], 500);
        }
    }

    /**
     * Update parking slots status for a range of slots
     * Similar to store() but updates existing slots in bulk
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRange(\Illuminate\Http\Request $request)
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|min:1',
                'floor_no' => 'required|string|min:1|max:50',
                'start_number' => 'required_without:slots|integer|min:1',
                'end_number' => 'required_without:slots|integer|min:1',
                'status' => 'required_without:slots|in:available,occupied,reserved,maintenance',
                'slots' => 'required_without_all:start_number,end_number|array',
                'slots.*.slot_number' => 'required_with:slots|integer|min:1',
                'slots.*.status' => 'required_with:slots|in:available,occupied,reserved,maintenance',
            ]);

            $company_id = $request->company_id;
            $floor_no = $request->floor_no;
            $isRowUpdate = $request->filled('slots');

            // Begin transaction for data consistency
            DB::beginTransaction();

            try {
                if ($isRowUpdate) {
                    $rows = $request->slots;

                    $updated = 0;
                    $slotNumbers = [];

                    foreach ($rows as $row) {
                        $slotNumber = (int) $row['slot_number'];
                        $slotStatus = $row['status'];

                        $updated += ParkingSlots::where('company_id', $company_id)
                            ->where('floor_no', $floor_no)
                            ->where('slot_number', $slotNumber)
                            ->update(['status' => $slotStatus]);

                        $slotNumbers[] = $slotNumber;
                    }

                    if ($updated === 0) {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => 'No parking slots found for the specified slots',
                            'record' => null,
                        ], 404);
                    }

                    $updatedSlots = ParkingSlots::where('company_id', $company_id)
                        ->where('floor_no', $floor_no)
                        ->whereIn('slot_number', array_unique($slotNumbers))
                        ->orderBy('slot_number')
                        ->get();

                    DB::commit();

                    return $this->response(
                        'Parking slots updated successfully',
                        [
                            'total_updated' => $updated,
                            'floor_no' => $floor_no,
                            'slots' => $updatedSlots,
                        ],
                        true
                    );
                }

                $start_number = (int) $request->start_number;
                $end_number = (int) $request->end_number;
                $status = $request->status;

                if ($start_number > $end_number) {
                    return [
                        'status' => false,
                        'message' => 'Start number cannot be greater than end number',
                        'record' => null,
                    ];
                }

                $updated = ParkingSlots::where('company_id', $company_id)
                    ->where('floor_no', $floor_no)
                    ->whereBetween('slot_number', [$start_number, $end_number])
                    ->update(['status' => $status]);

                if ($updated === 0) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'No parking slots found for the specified range',
                        'record' => null,
                    ], 404);
                }

                $updatedSlots = ParkingSlots::where('company_id', $company_id)
                    ->where('floor_no', $floor_no)
                    ->whereBetween('slot_number', [$start_number, $end_number])
                    ->orderBy('slot_number')
                    ->get();

                DB::commit();

                return $this->response(
                    'Parking slots updated successfully',
                    [
                        'total_updated' => $updated,
                        'floor_no' => $floor_no,
                        'slot_range' => "$start_number - $end_number",
                        'new_status' => $status,
                        'slots' => $updatedSlots,
                    ],
                    true
                );
            } catch (Throwable $th) {
                DB::rollBack();
                throw $th;
            }
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating parking slots: ' . $th->getMessage(),
                'record' => null,
            ], 500);
        }
    }
    public function parkingSlotsFloors(Request $request)
    {
        return DB::table('parking_slots')
            ->where('company_id', $request->company_id)
            ->select('floor_no as id', 'floor_no as name')
            ->distinct()
            ->orderBy('floor_no')
            ->get();
    }

    public function parkingSlotsByFloors(Request $request)
{
    return DB::table('parking_slots')
        ->where('company_id', $request->company_id)
        ->when($request->filled('floor_no'), function ($query) use ($request) {
            $query->where('floor_no', $request->floor_no);
        })
        ->select('slot_number as id', 'slot_number as name')
        ->orderBy('slot_number')
        ->get();
}
}

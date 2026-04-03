<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\Unit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class UnitController extends Controller
{
    public function dropDown()
    {
        $units = Unit::get(['id', 'name']);

        try {
            return response()->json($units);
        } catch (Exception $e) {
            return $this->response($e->getMessage(), null, false);
        }
    }

    public function index(Request $request)
    {
        try {
            $model = Unit::where('company_id', $request->company_id);

            // Filter by floor number
            if ($request->filled('floor_no')) {
                $model->where('floor_no', $request->floor_no);
            }

            // Filter by status
            if ($request->filled('status')) {
                $model->where('status', $request->status);
            }

            // Search by slot number
            if ($request->filled('common_search')) {
                $model->where('unit_number', 'like', $request->common_search . '%');
            }

            return $model->orderBy('floor_no')
                ->orderBy('unit_number')
                ->paginate($request->perPage ?? 50);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving parking slots: ' . $th->getMessage(),
                'record' => null,
            ], 500);
        }
    }

    public function store(StoreUnitRequest $request)
    {
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
                    'unit_number' => $i,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Check if slots already exist
            $existingSlots = Unit::where('floor_no', $floor_no)
                ->whereBetween('unit_number', [$start_number, $end_number])
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
                Unit::insert($slots);

                DB::commit();

                return $this->response('Unit created successfully', null, true);
            } catch (Throwable $th) {
                DB::rollBack();
                throw $th;
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error creating parking slots: ' . $e->getMessage(),
                'record' => null,
            ], 500);
        }
    }

    public function update($id, StoreUnitRequest $request)
    {
        try {
            // 1. Locate the specific slot
            $slot = Unit::where('id', $id)->firstOrFail();

            // 2. Perform the update with validated data
            $slot->update($request->validated());

            return $this->response('Unit updated successfully', $slot, true);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unit not found or access denied.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        try {
            return $this->response('Unit deleted successfully', null, true);
        } catch (Exception $e) {
            return $this->response($e->getMessage(), null, false);
        }
    }

    public function roomsByFloors(Request $request)
    {
        return DB::table('units')
            ->where('company_id', $request->company_id)
            ->where('floor_no', $request->floor_no)
            ->select('unit_number as id', 'unit_number as name')
            ->orderBy('unit_number')
            ->get();
    }
}

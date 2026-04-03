<?php

namespace App\Http\Controllers;

use App\Http\Requests\Floor\StoreRequest;
use App\Http\Requests\Floor\UpdateRequest;
use App\Models\Floor;
use Illuminate\Http\Request;
use Throwable;

class FloorController extends Controller
{
    public function dropDown()
    {
        return Floor::orderBy('name')
            ->get(['name']) // Fetch only the name column
            ->map(function ($floor) {
                return [
                    'id'   => $floor->name,
                    'name' => $floor->name,
                ];
            });
    }

    public function store(StoreRequest $request)
    {
        try {
            $created = Floor::create($request->validated());

            return $this->response('Floor created successfully', $created, true);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error creating Floor: ' . $th->getMessage(),
                'record' => null,
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $model = Floor::query();

            // Filter by floor name
            if ($request->filled('id')) {
                $model->where('name', $request->id);
            }

            // Search by slot number
            if ($request->filled('common_search')) {
                $model->where('name', 'like', $request->common_search . '%');
            }

            return $model->orderBy('name')->paginate($request->perPage ?? 50);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving parking slots: ' . $th->getMessage(),
                'record' => null,
            ], 500);
        }
    }

    public function update($id, UpdateRequest $request)
    {
        try {
            // 1. Locate the specific slot
            $slot = Floor::where('id', $id)->firstOrFail();

            // 2. Perform the update with validated data
            $slot->update($request->validated());

            return $this->response('Floor updated successfully', $slot, true);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Floor not found or access denied.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            $slot = Floor::where('id', $id)
                ->firstOrFail();

            $slot->delete();

            return $this->response('Floor deleted successfully', $slot, true);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting Floor: ' . $th->getMessage(),
                'record' => null,
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CamerasList;
use Illuminate\Http\Request;

class CamerasListController extends Controller
{
    public function index(Request $request)
    {
        $q = CamerasList::query();

        if ($request->filled('common_search') && strlen($request->common_search) >= 3) {
            $s = $request->common_search;
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%$s%")
                    ->orWhere('rtsp_url', 'like', "%$s%")
                    ->orWhere('node_server_ip', 'like', "%$s%");
            });
        }

        $perPage = (int) ($request->per_page ?? 10);

        $p = $q->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'data' => $p->items(),
            'total' => $p->total(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'rtsp_url' => ['required', 'string'],
            'node_server_ip' => ['nullable', 'string', 'max:64'],
        ]);
        $ip = (new ParkingCameraLogsController())->getServerIp();

        $validated["node_server_ip"] =  $ip;
        $camera = CamerasList::create($validated);

        return response()->json([
            'message' => 'Camera created',
            'data' => $camera
        ], 201);
    }

    public function show(CamerasList $camera)
    {
        return response()->json(['data' => $camera]);
    }

    public function update(Request $request, $id)
    {


        $camera = CamerasList::find($id);

        if (!$camera) {
            return response()->json([
                'message' => 'Camera not found',
                'status' => false,
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rtsp_url' => ['required', 'string'],
            'node_server_ip' => ['nullable', 'string', 'max:64'],
        ]);

        $ip = (new ParkingCameraLogsController())->getServerIp();
        $validated['node_server_ip'] = $ip;

        $camera->update($validated);

        return response()->json([
            'message' => 'Camera updated',
            'data' => $camera->fresh(),
            'status' => true,
        ], 200);
    }

    public function destroy(CamerasList $camera, $id)
    {
        //$camera->delete();

        $camera = CamerasList::where("id", $id);
        $camera->delete();

        return response()->json([
            'message' => 'Camera deleted'
        ]);
    }
}

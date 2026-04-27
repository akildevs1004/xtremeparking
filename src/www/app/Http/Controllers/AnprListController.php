<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use App\Models\ParkingMembers;

/*
Routes:
Route::post('/anpr/add', [AnprListController::class, 'add']);
Route::post('/anpr/delete', [AnprListController::class, 'deletePlate']);
Route::get('/anpr/updateWhitelist', [AnprListController::class, 'updateWhitelist']); // or POST
*/

class AnprListController extends Controller
{
    private const WHITE = 'TrafficRedList';     // camera whitelist
    private const BLACK = 'TrafficBlackList';   // camera blacklist

    // =========================
    // FULL RECONCILIATION:
    // - DB active plates => WHITE
    // - DB blocked/expired => BLACK
    // - Remove old plates from camera lists (not in DB)
    // - Never allow a plate in both lists
    // =========================
    public function updateWhitelist(): JsonResponse
    {
        $today = Carbon::today();

        $members = ParkingMembers::with('parkingFamilyMembers')
            ->select('id', 'plate_number', 'is_active', 'member_end_date')
            ->get();

        // DB canonical sets
        $dbWhiteSet = []; // canonicalKey => true
        $dbBlackSet = []; // canonicalKey => true

        // Map canonicalKey -> cameraPlate (first occurrence wins)
        $dbWhitePlateMap = []; // canonicalKey => cameraPlate
        $dbBlackPlateMap = []; // canonicalKey => cameraPlate

        // -------------------------
        // 1) Build DB sets + maps
        // -------------------------
        foreach ($members as $member) {
            $blocked = !$member->is_active ||
                ($member->member_end_date && Carbon::parse($member->member_end_date)->lt($today));

            $category = $blocked ? 'black' : 'white';

            // main plate
            if (!empty($member->plate_number)) {
                $camPlate = $this->normalizeDbToCameraPlate($member->plate_number);
                if ($camPlate) {
                    $key = $this->plateCanonicalKey($camPlate);
                    if ($key !== '') {
                        if ($category === 'white') {
                            $dbWhiteSet[$key] = true;
                            $dbWhitePlateMap[$key] = $dbWhitePlateMap[$key] ?? $camPlate;
                        } else {
                            $dbBlackSet[$key] = true;
                            $dbBlackPlateMap[$key] = $dbBlackPlateMap[$key] ?? $camPlate;
                        }
                    }
                }
            }

            // family plates
            if ($member->parkingFamilyMembers && $member->parkingFamilyMembers->count()) {
                foreach ($member->parkingFamilyMembers as $vehicle) {
                    if (empty($vehicle->vehicle_number)) continue;

                    $camPlate = $this->normalizeDbToCameraPlate($vehicle->vehicle_number);
                    if ($camPlate) {
                        $key = $this->plateCanonicalKey($camPlate);
                        if ($key !== '') {
                            if ($category === 'white') {
                                $dbWhiteSet[$key] = true;
                                $dbWhitePlateMap[$key] = $dbWhitePlateMap[$key] ?? $camPlate;
                            } else {
                                $dbBlackSet[$key] = true;
                                $dbBlackPlateMap[$key] = $dbBlackPlateMap[$key] ?? $camPlate;
                            }
                        }
                    }
                }
            }
        }

        // Ensure exclusivity in DB sets:
        // If plate appears in both, BLACK wins (safer). Remove from white.
        foreach ($dbBlackSet as $key => $_) {
            if (isset($dbWhiteSet[$key])) {
                unset($dbWhiteSet[$key], $dbWhitePlateMap[$key]);
            }
        }

        $synced = [];
        $deleted = [];
        $errors = [];

        // -------------------------
        // 2) Sync WHITE plates to camera
        // -------------------------
        foreach ($dbWhiteSet as $key => $_) {
            $plate = $dbWhitePlateMap[$key] ?? null;
            if (!$plate) continue;

            try {
                $synced[] = array_merge(['list' => 'white'], $this->addToCamera($plate, 'white'));
            } catch (\Throwable $e) {
                $errors[] = ['stage' => 'sync_white', 'plate' => $plate, 'error' => $e->getMessage()];
            }
        }

        // -------------------------
        // 3) Sync BLACK plates to camera
        // -------------------------
        foreach ($dbBlackSet as $key => $_) {
            $plate = $dbBlackPlateMap[$key] ?? null;
            if (!$plate) continue;

            try {
                $synced[] = array_merge(['list' => 'black'], $this->addToCamera($plate, 'black'));
            } catch (\Throwable $e) {
                $errors[] = ['stage' => 'sync_black', 'plate' => $plate, 'error' => $e->getMessage()];
            }
        }

        // -------------------------
        // 4) Cleanup camera WHITE (remove plates not in DB white set)
        // -------------------------
        try {
            $cameraWhiteRecords = $this->listCameraRecords(self::WHITE); // [['recno'=>..,'plate'=>..],..]
            foreach ($cameraWhiteRecords as $r) {
                $camPlate = strtoupper(trim((string)($r['plate'] ?? '')));
                $recno = (int)($r['recno'] ?? 0);
                if ($camPlate === '' || $recno <= 0) continue;

                $key = $this->plateCanonicalKey($camPlate);
                if ($key === '' || !isset($dbWhiteSet[$key])) {
                    $this->removeRecno(
                        "172.16.4.217",
                        "admin",
                        "Admin@123",
                        config('services.dahua.timeout', 15),
                        self::WHITE,
                        $recno
                    );
                    $deleted[] = ['from' => 'white', 'plate' => $camPlate, 'recno' => $recno];
                }
            }
        } catch (\Throwable $e) {
            $errors[] = ['stage' => 'cleanup_white', 'error' => $e->getMessage()];
        }

        // -------------------------
        // 5) Cleanup camera BLACK (remove plates not in DB black set)
        // -------------------------
        try {
            $cameraBlackRecords = $this->listCameraRecords(self::BLACK);
            foreach ($cameraBlackRecords as $r) {
                $camPlate = strtoupper(trim((string)($r['plate'] ?? '')));
                $recno = (int)($r['recno'] ?? 0);
                if ($camPlate === '' || $recno <= 0) continue;

                $key = $this->plateCanonicalKey($camPlate);
                if ($key === '' || !isset($dbBlackSet[$key])) {
                    $this->removeRecno(
                        "172.16.4.217",
                        "admin",
                        "Admin@123",
                        config('services.dahua.timeout', 15),
                        self::BLACK,
                        $recno
                    );
                    $deleted[] = ['from' => 'black', 'plate' => $camPlate, 'recno' => $recno];
                }
            }
        } catch (\Throwable $e) {
            $errors[] = ['stage' => 'cleanup_black', 'error' => $e->getMessage()];
        }

        return response()->json([
            'ok' => count($errors) === 0,
            'db_white_count' => count($dbWhiteSet),
            'db_black_count' => count($dbBlackSet),
            'synced_count' => count($synced),
            'deleted_count' => count($deleted),
            'errors_count' => count($errors),
            'errors' => $errors,
        ]);
    }

    // =========================
    // SINGLE PLATE ADD/UPDATE API
    // Input: { "vehicle":"DXBA1234", "category":"white" }
    // =========================
    public function add(Request $request): JsonResponse
    {
        $rawPlate = (string) $request->input('vehicle', '');
        $category = strtolower(trim((string) $request->input('category', '')));

        $plate = $this->normalizeDbToCameraPlate($rawPlate);

        if (!$plate || !in_array($category, ['white', 'black'], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Send {"vehicle":"DXBA1234","category":"white|black"}'
            ], 422);
        }

        try {
            $result = $this->addToCamera($plate, $category);
            return response()->json(['ok' => true, 'result' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // =========================
    // DELETE API
    // Input: { "vehicle":"DXBA1234", "category":"white|black|both" }
    // =========================
    public function deletePlate(Request $request): JsonResponse
    {
        $rawPlate = (string) $request->input('vehicle', '');
        $category = strtolower(trim((string) $request->input('category', '')));

        $plate = $this->normalizeDbToCameraPlate($rawPlate);

        if (!$plate || !in_array($category, ['white', 'black', 'both'], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Send {"vehicle":"DXBA1234","category":"white|black|both"}'
            ], 422);
        }

        try {
            $result = $this->deleteFromCamera($plate, $category);
            return response()->json(['ok' => true, 'result' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // =========================
    // INTERNAL: enforce single list + upsert
    // =========================
    private function addToCamera(string $plate, string $category): array
    {
        $plate = strtoupper(trim($plate));
        $category = strtolower(trim($category));

        if ($plate === '' || !in_array($category, ['white', 'black'], true)) {
            throw new RuntimeException("Invalid plate/category");
        }

        $host = "172.16.4.217";
        $user = "admin";
        $pass = "Admin@123";
        $timeout = config('services.dahua.timeout', 15);

        $targetList = $category === 'white' ? self::WHITE : self::BLACK;
        $otherList  = $category === 'white' ? self::BLACK : self::WHITE;

        // remove from opposite list
        $foundOther = $this->findPlate($host, $user, $pass, $timeout, $otherList, $plate);
        if ($foundOther) {
            $this->removeRecno($host, $user, $pass, $timeout, $otherList, $foundOther['recno']);
        }

        // upsert target
        $action = $this->upsertPlate($host, $user, $pass, $timeout, $targetList, $plate);

        return [
            'plate' => $plate,
            'category' => $category,
            'action' => $action,
        ];
    }

    private function deleteFromCamera(string $plate, string $category): array
    {
        $plate = strtoupper(trim($plate));
        $category = strtolower(trim($category));

        $host = "172.16.4.217";
        $user = "admin";
        $pass = "Admin@123";
        $timeout = config('services.dahua.timeout', 15);

        $removed = [];

        if ($category === 'white' || $category === 'both') {
            $found = $this->findPlate($host, $user, $pass, $timeout, self::WHITE, $plate);
            if ($found) {
                $this->removeRecno($host, $user, $pass, $timeout, self::WHITE, $found['recno']);
                $removed[] = 'white';
            }
        }

        if ($category === 'black' || $category === 'both') {
            $found = $this->findPlate($host, $user, $pass, $timeout, self::BLACK, $plate);
            if ($found) {
                $this->removeRecno($host, $user, $pass, $timeout, self::BLACK, $found['recno']);
                $removed[] = 'black';
            }
        }

        return ['plate' => $plate, 'removed_from' => $removed];
    }

    // =========================
    // CAMERA: list all records in a list (paging)
    // =========================
    private function listCameraRecords(string $listName): array
    {
        $host = "172.16.4.217";
        $user = "admin";
        $pass = "Admin@123";
        $timeout = config('services.dahua.timeout', 15);

        $all = [];
        $index = 0;
        $pageSize = 100;

        while (true) {
            $txt = $this->request($host, $user, $pass, $timeout, 'recordFinder.cgi', [
                'action' => 'find',
                'name'   => $listName,
                'count'  => $pageSize,
                'index'  => $index,
            ]);

            $kv = $this->parse($txt);

            if (!isset($kv['records[0].RecNo'])) {
                break;
            }

            for ($i = 0; $i < $pageSize; $i++) {
                $recnoKey = "records[{$i}].RecNo";
                $plateKey = "records[{$i}].PlateNumber";
                if (!isset($kv[$recnoKey])) break;

                $all[] = [
                    'recno' => (int) $kv[$recnoKey],
                    'plate' => (string) ($kv[$plateKey] ?? ''),
                ];
            }

            $index += $pageSize;
            if ($index > 50000) break; // safety
        }

        return $all;
    }

    // =========================
    // NORMALIZATION RULES (YOUR RULES)
    // DXBA1234 => A-1234
    // DXBB1234 => B-1234
    // A1234    => A-1234
    // SHA1234  => 1234
    // 1234     => 1234
    // =========================
    private function normalizeDbToCameraPlate(string $raw): ?string
    {
        $s = strtoupper(trim($raw));
        if ($s === '') return null;

        $s = preg_replace('/[\s_]+/', '', $s) ?? '';

        if (preg_match('/^[A-Z]-\d+$/', $s)) {
            return $s;
        }

        if (preg_match('/^DXB([A-Z])(\d+)$/', $s, $m)) {
            return $m[1] . '-' . $m[2];
        }

        if (preg_match('/^([A-Z])(\d+)$/', $s, $m)) {
            return $m[1] . '-' . $m[2];
        }

        if (preg_match('/^SHA(\d+)$/', $s, $m)) {
            return $m[1]; // digits only
        }

        if (preg_match('/^\d+$/', $s)) {
            return $s;
        }

        return null;
    }

    private function plateCanonicalKey(string $raw): string
    {
        $cam = $this->normalizeDbToCameraPlate($raw);
        if ($cam === null) {
            return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($raw))) ?? '';
        }

        if (preg_match('/^([A-Z])-(\d+)$/', $cam, $m)) {
            return $m[1] . $m[2];
        }

        return $cam; // digits-only
    }

    // =========================
    // DAHUA HELPERS (BASIC AUTH)
    // =========================
    private function request($host, $user, $pass, $timeout, $cgi, $query)
    {
        $url = "http://{$host}/cgi-bin/{$cgi}?" . http_build_query($query);

        $res = Http::timeout($timeout)
            ->withBasicAuth($user, $pass)
            ->withHeaders(['Connection' => 'close'])
            ->withoutVerifying()
            ->get($url);

        if (!$res->successful()) {
            throw new RuntimeException("Dahua error {$res->status()} : {$res->body()}");
        }

        return trim((string)$res->body());
    }

    private function parse($txt)
    {
        $out = [];
        foreach (preg_split("/\r?\n/", trim($txt)) as $line) {
            $line = trim($line);
            if ($line !== '' && str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $out[trim($k)] = trim($v);
            }
        }
        return $out;
    }

    private function findPlate($host, $user, $pass, $timeout, $list, $plate)
    {
        $txt = $this->request($host, $user, $pass, $timeout, 'recordFinder.cgi', [
            'action' => 'find',
            'name' => $list,
            'count' => 50,
            'condition.PlateNumber' => $plate
        ]);

        $kv = $this->parse($txt);
        $recno = $kv['records[0].RecNo'] ?? null;

        return $recno ? ['recno' => (int)$recno] : null;
    }

    private function upsertPlate($host, $user, $pass, $timeout, $list, $plate): string
    {
        $existing = $this->findPlate($host, $user, $pass, $timeout, $list, $plate);

        if (!$existing) {
            $this->request($host, $user, $pass, $timeout, 'recordUpdater.cgi', [
                'action' => 'insert',
                'name' => $list,
                'PlateNumber' => $plate
            ]);
            return 'insert';
        }

        $this->request($host, $user, $pass, $timeout, 'recordUpdater.cgi', [
            'action' => 'update',
            'name' => $list,
            'recno' => $existing['recno'],
            'PlateNumber' => $plate
        ]);
        return 'update';
    }

    private function removeRecno($host, $user, $pass, $timeout, $list, $recno): void
    {
        $this->request($host, $user, $pass, $timeout, 'recordUpdater.cgi', [
            'action' => 'remove',
            'name' => $list,
            'recno' => $recno
        ]);
    }
}

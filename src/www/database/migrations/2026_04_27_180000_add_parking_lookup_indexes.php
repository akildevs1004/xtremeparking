<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Indexes that target the hot path in CameraLogListenerController::CameraLogProcessing.
     * Every parking event runs the same five lookups; without these, each one is a sequential
     * scan and 4 concurrent php-cgi workers contend on Postgres CPU.
     *
     * Raw SQL (not Schema Blueprint) because we need:
     *   - IF NOT EXISTS (idempotent re-run)
     *   - Functional index on (COALESCE(prefix,'') || plate_number)
     *   - Partial index on parking_camera_logs WHERE out_time IS NULL
     */
    public function up(): void
    {
        // ParkingMembers plate lookup — direct match
        DB::statement('CREATE INDEX IF NOT EXISTS idx_parking_members_company_plate
            ON parking_members (company_id, plate_number)');

        // ParkingMembers prefixed-plate lookup — matches the rewritten whereRaw using `||`
        // (NOT `CONCAT(...)` — concat is STABLE, can't be in an expression index)
        DB::statement("CREATE INDEX IF NOT EXISTS idx_parking_members_company_prefixplate
            ON parking_members (company_id, ((COALESCE(prefix, '') || plate_number)))");

        // Guest vehicle lookup (Eloquent pluralizes the model to "parking_members_vehicles_lists")
        DB::statement('CREATE INDEX IF NOT EXISTS idx_pmvl_vehicle_number
            ON parking_members_vehicles_lists (vehicle_number)');

        // Device lookup by camera name (used as where().orWhere() — two single-column
        // indexes let Postgres do a bitmap OR)
        DB::statement('CREATE INDEX IF NOT EXISTS idx_devices_camera_in_name
            ON devices (camera_in_name)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_devices_camera_out_name
            ON devices (camera_out_name)');

        // "Parking full?" count + open-IN lookup — partial index keeps it tiny since
        // the vast majority of rows have an out_time set.
        DB::statement('CREATE INDEX IF NOT EXISTS idx_parking_camera_logs_open
            ON parking_camera_logs (company_id, log_vehicle_number)
            WHERE out_time IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_parking_members_company_plate');
        DB::statement('DROP INDEX IF EXISTS idx_parking_members_company_prefixplate');
        DB::statement('DROP INDEX IF EXISTS idx_pmvl_vehicle_number');
        DB::statement('DROP INDEX IF EXISTS idx_devices_camera_in_name');
        DB::statement('DROP INDEX IF EXISTS idx_devices_camera_out_name');
        DB::statement('DROP INDEX IF EXISTS idx_parking_camera_logs_open');
    }
};

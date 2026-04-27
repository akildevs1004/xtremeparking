<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ANPRCameraEvents extends Command
{
    protected $signature = 'camera:test';
    protected $description = 'Test reading camera events and saving images';

    public function handle()
    {
        $baseUrl = "http://192.168.12.11"; // camera IP
        $username = "admin";
        $password = "Admin@123";
        $channel = 1;

        $this->info("Connecting to camera...");

        try {
            // For testing, fetch a single snapshot instead of full streaming
            $response = Http::withDigestAuth($username, $password)
                ->get("$baseUrl/cgi-bin/snapManager.cgi", [
                    'action' => 'snapPicture', // single snapshot
                    'channel' => $channel,
                ]);

            if ($response->failed()) {
                $this->error("Failed to fetch camera: " . $response->body());
                return;
            }

            $data = $response->body();

            // Detect JPEG start/end bytes
            $start = strpos($data, "\xFF\xD8"); // start of JPEG
            $end   = strpos($data, "\xFF\xD9", $start); // end of JPEG

            if ($start !== false && $end !== false) {
                $image = substr($data, $start, $end - $start + 2);
                $filename = "cam_test_" . now()->timestamp . ".jpg";
                Storage::disk('local')->put("captures/$filename", $image);
                $this->info("Saved image: $filename");
            } else {
                $this->warn("No image detected in response");
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
<?php

namespace App\Services;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ParkingCameraLogsController;
use App\Http\Controllers\ParkingMembersVehiclesListController;
use App\Http\Controllers\StripeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttServiceQRCodePayment
{
    // ---- HARD-CODED BROKER SETTINGS (no env, no creds)
    private const BROKER_IP   = '165.22.222.17'; // <— your static IP
    private const BROKER_PORT = 1883;            // <— your static port
    private const DEVICE_ROOT = 'xtreemparking'; // topic root/prefix
    private const KEEPALIVE   = 30;

    protected MqttClient $mqtt;
    protected string $clientId;

    public function __construct()
    {


        $this->clientId = 'laravel-xtparking-qrcode-' . uniqid();
        $this->mqtt     = new MqttClient(self::BROKER_IP, self::BROKER_PORT, $this->clientId);
        echo "MQTT init client={$this->clientId} " . self::BROKER_IP . ':' . self::BROKER_PORT;
        Log::info("MQTT init client={$this->clientId} " . self::BROKER_IP . ':' . self::BROKER_PORT);

        $this->tryConnect();
    }

    protected function makeSettings(): ConnectionSettings
    {
        // No username/password, no TLS
        return (new ConnectionSettings())
            ->setKeepAliveInterval(self::KEEPALIVE)
            ->setLastWillTopic(self::DEVICE_ROOT . '/server/status')
            ->setLastWillMessage(json_encode(['status' => 'down', 'at' => now()->toIso8601String()], JSON_UNESCAPED_SLASHES))
            ->setLastWillQualityOfService(0)
            ->setRetainLastWill(false);
    }

    protected function tryConnect(): void
    {
        try {
            $this->mqtt->connect($this->makeSettings(), true);
            echo " ✅ connected\n";
            Log::info('✅ MQTT connected (init)');
        } catch (\Throwable $e) {
            echo " ❌ connect failed: {$e->getMessage()}\n";
            Log::error("❌ MQTT initial connect failed: {$e->getMessage()}");
        }
    }

    public function publish(string $topic, string $message, int $qos = 0, bool $retain = false): void
    {
        try {
            $this->ensureConnected();
            $this->mqtt->publish($topic, $message, $qos, $retain);
            echo "MQTT published: topic={$topic}, bytes=" . strlen($message) . "\n";
            Log::info("MQTT published: topic={$topic}, bytes=" . strlen($message));
        } catch (\Throwable $e) {
            echo " ❌ MQTT publish error on {$topic}: {$e->getMessage()}\n";
            Log::error("❌ MQTT publish error on {$topic}: {$e->getMessage()}");
        }
    }

    /** Run from: php artisan mqtt:qrcode-listen (blocking) */
    public function subscribeAndListen(): void
    {
        $pattern = self::DEVICE_ROOT . '/+/qrcodepaymentsapi/laravel';
        $this->ensureConnected();



        $this->mqtt->subscribe($pattern, function (string $topic, string $payload): void {

            echo $payload;
            "\n";
            $logPath = base_path('../../mqtt-laravel/mqtt-qr-logs/' . date('Y-m-d') . '.log');
            $dir = dirname($logPath);
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0775, true);
            }

            File::append($logPath, "[" . now() . "] ▶ topic={$topic} bytes=" . strlen($payload) . "\n");

            $companyId = $this->extractCompanyId($topic);
            if (!$companyId) {
                echo " ⚠ company_id not found in: {$topic}\n";
                File::append($logPath, "[" . now() . "] ⚠ company_id not found in: {$topic}\n");
                return;
            }

            $json = json_decode($payload, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            if (!is_array($json)) {
                echo " ⚠ invalid JSON company={$companyId}\n";
                File::append($logPath, "[" . now() . "] ⚠ invalid JSON company={$companyId}\n");
                return;
            }

            // if (($json['action'] ?? null) !== 'parking_qr_get_vehicle_details') {
            //     echo " ⚠ ignored action=" . ($json['action'] ?? 'null') . " company={$companyId}\n";
            //     File::append($logPath, "[" . now() . "] ℹ ignored action=" . ($json['action'] ?? 'null') . " company={$companyId}\n");
            //     return;
            // }

            $action = $json['action'] ?? null;
            $response = null;
            echo " ▶ action=" . ($action ?? 'null') . " company={$companyId}\n";
            if ($action === 'parking_qr_get_vehicle_details') {
                echo "  action=" . ($action ?? 'null') . " company={$companyId}\n";
                try {
                    /** @var \App\Http\Controllers\Parking\CameraLogListenerController $controller */
                    $controller = app(\App\Http\Controllers\Parking\CameraLogListenerController::class);
                    $response   = $controller->getQROutVehicleDetails(new Request($json));
                } catch (\Throwable $e) {
                    echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                    File::append($logPath, "[" . now() . "] ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n");
                    Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
                }
            } else  if ($action === 'stripe/create-payment-link') {
                try {
                    /** @var \App\Http\Controllers\Parking\CameraLogListenerController $controller */
                    $controller = app(StripeController::class);
                    $response   = $controller->createLink(new Request($json));
                } catch (\Throwable $e) {
                    echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                    File::append($logPath, "[" . now() . "] ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n");
                    Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
                }
            } else  if ($action === 'parking_qr_paymentresponse') {
                try {
                    /** @var \App\Http\Controllers\Parking\CameraLogListenerController $controller */
                    $controller = app(ParkingCameraLogsController::class);
                    $response   = $controller->ParkingPaymentResponseUpdate(new Request($json));
                } catch (\Throwable $e) {
                    echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                    File::append($logPath, "[" . now() . "] ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n");
                    Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
                }
            } else if ($action === '/stripe/session') {
                try {

                    /** @var \App\Http\Controllers\Parking\CameraLogListenerController $controller */
                    $controller = app(StripeController::class);
                    $response   = $controller->getSession($json["payment_sessionid"]);
                } catch (\Throwable $e) {
                    echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                    File::append($logPath, "[" . now() . "] ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n");
                    Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
                }
            } else if ($action === 'parking_qr_pay_extra_minutes') {
                try {

                    /** @var \App\Http\Controllers\Parking\CameraLogListenerController $controller */
                    $controller = app(ParkingCameraLogsController::class);
                    $response   = $controller->qrParkingExtraPayment(new Request($json));
                } catch (\Throwable $e) {
                    echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                    File::append($logPath, "[" . now() . "] ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n");
                    Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
                }
            } else if ($action === 'login') {
                try {

                    /** @var \App\Http\Controllers\Parking\CameraLogListenerController $controller */
                    $controller = app(AuthController::class);
                    $response   = $controller->login(new Request($json));
                } catch (\Throwable $e) {
                    echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                    File::append($logPath, "[" . now() . "] ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n");
                    Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
                }
            } else if ($action === 'parking_members_vehiclesList') {
                try {

                    /** @var \App\Http\Controllers\Parking\CameraLogListenerController $controller */
                    $controller = app(ParkingMembersVehiclesListController::class);
                    $response   = $controller->index(new Request($json));
                } catch (\Throwable $e) {
                    echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                    File::append($logPath, "[" . now() . "] ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n");
                    Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
                }
            } else if ($action === 'parking_members_vehiclesList_update') {
                try {

                    /** @var \App\Http\Controllers\Parking\CameraLogListenerController $controller */
                    $controller = app(ParkingMembersVehiclesListController::class);
                    $response   = $controller->update(new Request($json));
                } catch (\Throwable $e) {
                    echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                    File::append($logPath, "[" . now() . "] ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n");
                    Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
                }
            } else if ($action === 'parking_members_vehiclesList_create') {
                try {

                    /** @var \App\Http\Controllers\Parking\CameraLogListenerController $controller */
                    $controller = app(ParkingMembersVehiclesListController::class);
                    $response   = $controller->store(new Request($json));
                } catch (\Throwable $e) {
                    echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                    File::append($logPath, "[" . now() . "] ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n");
                    Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
                }
            } else if ($action === 'parking_members_vehiclesList_delete') {
                try {

                    /** @var \App\Http\Controllers\Parking\CameraLogListenerController $controller */
                    $controller = app(ParkingMembersVehiclesListController::class);
                    $response   = $controller->destroy(new Request($json));
                } catch (\Throwable $e) {
                    echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                    File::append($logPath, "[" . now() . "] ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n");
                    Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
                }
            } else if ($action === 'parking_camera_logs') {
                try {

                    /** @var \App\Http\Controllers\Parking\CameraLogListenerController $controller */
                    $controller = app(ParkingCameraLogsController::class);
                    $response   = $controller->index(new Request($json));
                } catch (\Throwable $e) {
                    echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                    File::append($logPath, "[" . now() . "] ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n");
                    Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
                }
            }







            try {

                if ($response !== null) {

                    // Normalize to JSON string (always wrap in ["data" => ...])
                    if ($response instanceof \Illuminate\Http\JsonResponse || $response instanceof \Illuminate\Http\Response) {
                        $decoded = json_decode($response->getContent(), true);
                        $out = json_encode(['data' => $decoded, 'action' => $action], JSON_UNESCAPED_SLASHES);
                    } elseif (is_array($response) || is_object($response)) {
                        $out = json_encode(['data' => $response, 'action' => $action], JSON_UNESCAPED_SLASHES);
                    } else {
                        $out = json_encode(['data' => $response, 'action' => $action], JSON_UNESCAPED_SLASHES);
                    }


                    echo " ◀ {$action} company={$companyId} bytes=" . strlen($out) . "\n";
                    File::append($logPath, "[" . now() . "] ◀ {$action} company={$companyId} bytes=" . strlen($out) . "\n");

                    $replyTopic = self::DEVICE_ROOT . "/{$companyId}/qrcodepaymentsapi/vue";
                    echo "publish to {$action}\n";
                    $this->publish($replyTopic, $out, 0, false);
                }
            } catch (\Throwable $e) {
                echo " ❌ {$action} handler error company={$companyId}: {$e->getMessage()}\n";
                File::append($logPath, "[" . now() . "] ❌ { $action} handler error company={$companyId}: {$e->getMessage()}\n");
                Log::error("{$action} QR handler error (company={$companyId}): " . $e->getMessage());
            }
        }, 0);



        $this->publish(self::DEVICE_ROOT . '/server/status', json_encode(['status' => 'up', 'at' => now()->toIso8601String()], JSON_UNESCAPED_SLASHES));

        if (App::runningInConsole() && function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, fn() => $this->shutdown());
            pcntl_signal(SIGINT,  fn() => $this->shutdown());
        }

        echo "MQTT listening on {$pattern}\n";
        Log::info("MQTT listening on {$pattern}");

        while (true) {
            try {
                $this->mqtt->loop(true);
            } catch (\Throwable $e) {
                echo " ❌ MQTT loop error: {$e->getMessage()}\n";
                Log::error("❌ MQTT loop error: {$e->getMessage()}");
                $this->reconnectWithBackoff();
            }
        }
    }

    protected function ensureConnected(): void
    {
        if (!$this->mqtt || !$this->mqtt->isConnected()) {
            $this->reconnectWithBackoff();
        }
    }

    protected function reconnectWithBackoff(): void
    {
        $delay = 1;
        $max = 30;
        while (true) {
            try {
                $this->clientId = 'laravel-xtparking-qrcode-' . uniqid();
                $this->mqtt     = new MqttClient(self::BROKER_IP, self::BROKER_PORT, $this->clientId);
                $this->mqtt->connect($this->makeSettings(), true);
                echo " ✅ Reconnected MQTT client={$this->clientId}\n";
                Log::info("🔌 Reconnected MQTT client={$this->clientId}");
                return;
            } catch (\Throwable $e) {
                echo " ❌ Reconnect failed: {$e->getMessage()}, retrying in {$delay}s\n";
                Log::warning("Reconnect failed, retrying in {$delay}s: " . $e->getMessage());
                sleep($delay);
                $delay = min($delay * 2, $max);
            }
        }
    }

    protected function shutdown(): void
    {
        try {
            $this->publish(self::DEVICE_ROOT . '/server/status', json_encode(['status' => 'down', 'at' => now()->toIso8601String()], JSON_UNESCAPED_SLASHES));
            if ($this->mqtt && $this->mqtt->isConnected()) {
                $this->mqtt->disconnect();
            }
        } catch (\Throwable $e) {
            // ignore
        } finally {
            echo "🛑 MQTT listener stopped\n";
            Log::info('🛑 MQTT listener stopped');
            if (App::runningInConsole()) {
                exit(0);
            }
        }
    }

    /** Extract {company_id} from: xtreemparking/{company_id}/qrcodepaymentsapi/laravel */
    protected function extractCompanyId(string $topic): ?string
    {
        $parts = explode('/', $topic);
        if (
            count($parts) >= 4
            && $parts[0] === self::DEVICE_ROOT
            && $parts[2] === 'qrcodepaymentsapi'
            && $parts[3] === 'laravel'
        ) {
            return $parts[1];
        }
        if (preg_match('#^' . preg_quote(self::DEVICE_ROOT, '#') . '/([^/]+)/qrcodepaymentsapi/laravel#', $topic, $m)) {
            return $m[1] ?? null;
        }
        return null;
    }
}

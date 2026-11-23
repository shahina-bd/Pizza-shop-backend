<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\WebpushConfig;

class FirebaseNotificationService
{
    protected Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Alert Admin when a new order is placed
     */
    public function sendToAdmins(string $title, string $body, array $data = []): void
    {
        $adminTokens = User::whereNotNull('fcm_token')
            ->where(function ($q) {
                $q->whereIn('role', ['admin', 'super-admin', 'manager', 'Admin'])
                  ->orWhere('email', 'admin@example.com')
                  ->orWhere('mobile', '01700000000');
                if (Schema::hasColumn('users', 'is_admin')) {
                    $q->orWhere('is_admin', 1);
                }
                if (method_exists(User::class, 'roles')) {
                    $q->orWhereHas('roles', function ($rq) {
                        $rq->whereIn('name', ['admin', 'super-admin', 'manager', 'Admin']);
                    });
                }
            })
            ->pluck('fcm_token')
            ->filter()
            ->unique()
            ->toArray();

        if (empty($adminTokens)) {
            Log::info('[FCM] No admin tokens found to notify.');
            return;
        }

        foreach ($adminTokens as $token) {
            $this->sendToToken($token, $title, $body, $data);
        }
    }

    /**
     * Alert Customer when order status changes
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        if (!empty($user->fcm_token)) {
            $this->sendToToken($user->fcm_token, $title, $body, $data);
        }
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): void
    {
        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data)
                ->withWebpushConfig(WebpushConfig::fromArray([
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'icon' => '/favicon.ico',
                    ],
                    'fcm_options' => [
                        'link' => '/admin/orders',
                    ],
                ]))
                ->withAndroidConfig(AndroidConfig::fromArray([
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'pizzneapol_orders_channel',
                        'sound' => 'default',
                        'default_sound' => true,
                    ],
                ]));

            $this->messaging->send($message);
            Log::info("[FCM] Sent message to token: " . substr($token, 0, 15) . "...");
        } catch (\Throwable $e) {
            Log::error('[FCM] Error: ' . $e->getMessage());
        }
    }
}

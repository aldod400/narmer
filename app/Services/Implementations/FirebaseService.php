<?php

namespace App\Services\Implementations;

use App\Repository\Contracts\NotificationRepositoryInterface;
use App\Services\Contracts\FirebaseServiceInterface;
use Google\Client as GoogleClient;
use Google\Service\FirebaseCloudMessaging;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService implements FirebaseServiceInterface
{
    protected $notificationRepo;
    public function __construct(NotificationRepositoryInterface $notificationRepo)
    {
        $this->notificationRepo = $notificationRepo;
    }
    public function getFirebaseAccessToken()
    {
        $keyFilePath = storage_path('firebase/credintials.json');
        $googleClient = new GoogleClient();
        $googleClient->setAuthConfig($keyFilePath);
        $googleClient->addScope(FirebaseCloudMessaging::CLOUD_PLATFORM);
        $googleClient->fetchAccessTokenWithAssertion();
        return $googleClient->getAccessToken()['access_token'] ?? null;
    }

    public function sendNotification($title, $body, $type, $to, $save = false, $user_id = null, $image = null, $data = null)
    {
        if ($save == true) {
            $this->notificationRepo->create([
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'to' => $to,
                'user_id' => $user_id,
                'image' => $image,
                'data' => $data,
                'is_read' => false,
            ]);
        }
        $accessToken = self::getFirebaseAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => __('message.Failed to get Firebase Access Token')
            ];
        }

        $client = new HttpClient();
        $url = "https://fcm.googleapis.com/v1/projects/talaa-2bd5f/messages:send";

        $topicOrToken = $type == 'topic' ? 'topic' : 'token';
        $payload = [
            "message" => [
                $topicOrToken => $to,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                    "image" => env('APP_URL') . '/storage/' . $image ?? "",
                ],
                "data" => $data,
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'Content-Type' => 'application/json',
        ])->post($url, $payload);
        Log::info(json_decode($response->getBody(), true));
        return [
            'success' => true,
            'message' => __('message.Notification sent successfully'),
            'data'    => json_decode($response->getBody(), true)
        ];
    }
}

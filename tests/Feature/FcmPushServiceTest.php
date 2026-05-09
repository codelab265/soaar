<?php

use App\Models\User;
use App\Services\FcmPushService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('sends push notification using firebase http v1', function () {
    $privateKeyResource = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);

    expect($privateKeyResource)->not->toBeFalse();

    $privateKey = '';
    $exported = openssl_pkey_export($privateKeyResource, $privateKey);
    expect($exported)->toBeTrue();

    config()->set('fcm.project_id', 'soaar-d0f20');
    config()->set('fcm.client_email', 'firebase-adminsdk-fbsvc@soaar-d0f20.iam.gserviceaccount.com');
    config()->set('fcm.private_key', str_replace("\n", '\n', $privateKey));
    config()->set('fcm.oauth_token_uri', 'https://oauth2.googleapis.com/token');
    config()->set('fcm.scope', 'https://www.googleapis.com/auth/firebase.messaging');

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response([
            'access_token' => 'access-token-123',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]),
        'https://fcm.googleapis.com/v1/projects/soaar-d0f20/messages:send' => Http::response([
            'name' => 'projects/soaar-d0f20/messages/abc',
        ]),
    ]);

    $user = User::factory()->create([
        'fcm_token' => 'device-token-123',
    ]);

    app(FcmPushService::class)->sendToUser(
        user: $user,
        title: 'Test title',
        body: 'Test body',
        data: ['notification_type' => 'PointsChangedNotification'],
    );

    Http::assertSentCount(2);
    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://fcm.googleapis.com/v1/projects/soaar-d0f20/messages:send'
            && $request->hasHeader('Authorization', 'Bearer access-token-123')
            && data_get($request->data(), 'message.token') === 'device-token-123'
            && data_get($request->data(), 'message.notification.title') === 'Test title';
    });
});

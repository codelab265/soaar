<?php

return [
    'base_url' => env('PAYCHANGU_BASE_URL', 'https://api.paychangu.com'),
    'secret_key' => env('PAYCHANGU_SECRET_KEY'),
    'public_key' => env('PAYCHANGU_PUBLIC_KEY'),
    'webhook_secret' => env('PAYCHANGU_WEBHOOK_SECRET'),
    'checkout_callback_url' => env('PAYCHANGU_CHECKOUT_CALLBACK_URL', env('APP_URL').'/paychangu/callback'),
    'checkout_return_url' => env('PAYCHANGU_CHECKOUT_RETURN_URL', env('APP_URL').'/paychangu/return'),
    'card_enabled' => env('PAYCHANGU_CARD_ENABLED', false),
];

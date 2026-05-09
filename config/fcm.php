<?php

return [
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'client_email' => env('FIREBASE_CLIENT_EMAIL'),
    'private_key' => env('FIREBASE_PRIVATE_KEY'),
    'oauth_token_uri' => env('FIREBASE_OAUTH_TOKEN_URI', 'https://oauth2.googleapis.com/token'),
    'scope' => env('FIREBASE_OAUTH_SCOPE', 'https://www.googleapis.com/auth/firebase.messaging'),
];

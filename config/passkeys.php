<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Relying Party (RP) Configuration
    |--------------------------------------------------------------------------
    |
    | The relying party parameters specify your application security identity.
    | The 'id' must strictly match your active browser address domain.
    |
    */
    'relying_party' => [
        'name' => env('APP_NAME', 'D&G Construction Inc.'),
        // Let's use an environmental look-up key for strict routing stability
        'id' => env('PASSKEY_RP_ID', 'localhost'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Models & Tables Configuration
    |--------------------------------------------------------------------------
    */
    'user_model' => \App\Models\User::class,
];
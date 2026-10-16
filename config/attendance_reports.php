<?php

return [
    'mailer' => env('ATTENDANCE_REPORT_MAILER', 'mailjet'),
    'from' => [
        'address' => env('MAILJET_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
        'name' => env('MAILJET_FROM_NAME', env('MAIL_FROM_NAME', env('APP_NAME', 'D&G Construction'))),
    ],
];

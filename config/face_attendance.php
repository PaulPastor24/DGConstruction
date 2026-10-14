<?php

return [
    // Off until AWS, worker privacy review, and staging tests have been completed.
    'enabled' => (bool) env('FACE_ATTENDANCE_ENABLED', false),
    // Available only server side. Never pass through Vite or JSON responses.
    'aws_key' => env('AWS_ACCESS_KEY_ID'),
    'aws_secret' => env('AWS_SECRET_ACCESS_KEY'),
    'privacy_notice_version' => env('FACE_PRIVACY_NOTICE_VERSION', 'v1'),
    'region' => env('FACE_AWS_REGION', 'ap-northeast-1'),
    'collection_id' => env('FACE_AWS_COLLECTION_ID', 'dgconstruction-workers-staging'),
    // AWS STS role with ONLY rekognition:StartFaceLivenessSession.
    'stream_role_arn' => env('FACE_AWS_STREAM_ROLE_ARN'),
    'liveness_threshold' => (float) env('FACE_LIVENESS_THRESHOLD', 90),
    'similarity_threshold' => (float) env('FACE_SIMILARITY_THRESHOLD', 98),
    'ambiguity_margin' => (float) env('FACE_AMBIGUITY_MARGIN', 5),
];

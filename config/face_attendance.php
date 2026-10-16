<?php

return [
    'enabled' => (bool) env('FACE_ATTENDANCE_ENABLED', true),
    'privacy_notice_version' => env('FACE_PRIVACY_NOTICE_VERSION', 'local-v1'),
    'model_version' => 'human-3.3.6-faceres',
    'similarity_threshold' => (float) env('FACE_SIMILARITY_THRESHOLD', 65),
    'ambiguity_margin' => (float) env('FACE_AMBIGUITY_MARGIN', 8),
];

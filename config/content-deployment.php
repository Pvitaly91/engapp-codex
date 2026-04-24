<?php

return [
    'lock' => [
        'enabled' => true,
        'key' => 'global_content_operations',
        'ttl_seconds' => 3600,
        'stale_after_seconds' => 900,
        'heartbeat_touch_per_package' => true,
        'allow_stale_takeover_default' => false,
    ],
    'doctor' => [
        'artifact_history_limit' => 20,
    ],
];

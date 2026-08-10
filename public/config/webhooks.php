<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Phase 2 §12 — future integration webhooks
    |--------------------------------------------------------------------------
    | Signature headers are validated when present. If a signature header is
    | sent but the shared secret is configured and does not match → 401.
    | Requests with no signature header are accepted (provider may not sign).
    */

    'sources' => [
        'judge' => [
            'secret' => env('JUDGE_WEBHOOK_SECRET', env('JUDGEME_WEBHOOK_SECRET')),
            'headers' => [
                'X-Judgeme-Hmac-Sha256',
                'X-Judge-Me-Signature',
                'X-Webhook-Signature',
                'X-Hub-Signature-256',
                'X-Signature',
            ],
            'bearer' => env('JUDGE_WEBHOOK_BEARER'),
        ],
        'video-ai' => [
            'secret' => env('VIDEO_AI_WEBHOOK_SECRET'),
            'headers' => [
                'X-Video-Ai-Signature',
                'X-Webhook-Signature',
                'X-Hub-Signature-256',
                'X-Signature',
            ],
            'bearer' => env('VIDEO_AI_WEBHOOK_BEARER'),
        ],
        'wallpass' => [
            'secret' => env('WALLPASS_WEBHOOK_SECRET'),
            'headers' => [
                'X-Wallpass-Signature',
                'X-Webhook-Signature',
                'X-Hub-Signature-256',
                'X-Signature',
            ],
            'bearer' => env('WALLPASS_WEBHOOK_BEARER'),
        ],
    ],
];

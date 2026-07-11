<?php

declare(strict_types=1);

return [
    'defaults' => [
        'enabled' => true,
        'force_https_in_production' => true,
        'immutable_dates' => true,
        'prohibit_destructive_commands' => true,
        'passwords' => [
            'enabled' => true,
            'min' => 12,
            'mixed_case' => true,
            'letters' => true,
            'numbers' => true,
            'symbols' => true,
            'uncompromised' => true,
        ],
    ],

    'users' => [
        'model' => 'App\\Models\\User',
        'list_columns' => ['id', 'name', 'email', 'created_at'],
    ],

    'database' => [
        'binaries' => [
            'mysql' => 'mysql',
            'mysqldump' => 'mysqldump',
        ],
    ],

    'search' => [
        'meilisearch' => [
            'embedder' => env('MEILISEARCH_EMBEDDER', 'default'),
            'hybrid_semantic_ratio' => (float) env('MEILISEARCH_HYBRID_SEMANTIC_RATIO', 0.75),
        ],
    ],

    'frontend' => [
        'brand' => [
            'name' => env('APP_NAME', 'Performing App'),
            'logo' => '/logo.svg',
            'mark' => '/logo.svg',
        ],
    ],
];

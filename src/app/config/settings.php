<?php

declare(strict_types=1);

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'projects' => [
            'example' => [
                'storage' => [
                    'path' => 'example'
                ],
                'upload' => [
                    'token' => 'N3edBMSnQrakH9nBK98Gmmrz367JxWCT',
                ],
                'download' => [
                    'token' => 'pzScy2w6Kuhz2djvMUg6TeNpBmt9rFvW',
                ]
            ]
        ],
        // Monolog settings
        'logger' => [
            'name' => 'file-storage',
            'path' => __DIR__ . 'php://stdout',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'storage' => [
            'directory' => '/storage',
        ]
    ],
];

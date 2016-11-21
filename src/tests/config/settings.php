<?php
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
            ],
            'example-test' => [
                'storage' => [
                    'path' => 'example_test'
                ],
                'upload' => [
                    'token' => 'BMSnQraN3edkH9nBK98Gmmrz367JxWCT',
                ],
                'download' => [
                    'token' => '6Kuhz2djvMUpzScy2wg6TeNpBmt9rFvW',
                ]
            ],
            'example-test2' => [
                'storage' => [
                    'path' => 'example_test2'
                ],
                'upload' => [
                    'token' => 'Gmmrz3BMSnQraN3edkH9nBK9867JxWCT',
                ],
                'download' => [
                    'token' => 'KuhzNpBmt962djvMUpzScy2wg6TerFvW',
                ]
            ],
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

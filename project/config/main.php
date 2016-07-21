<?php
return [
    /** Secret tokens for upload files **/
    'uploadToken' => [
        'd41d8cd98f00b204e9800998ecf8427e',
    ],
    'availableImageExtensions' => [
        'jpg', 'jpeg', 'png', 'gif', 'bmp',
    ],
    'mime-types' => include __DIR__ . '/mime-types.php',
];

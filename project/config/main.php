<?php

$params = array_merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'file',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\controllers',
    'components' => [
        'image' => [
            'class' => \app\components\Image::class,
            'uploadSecret' => 'd41d8cd98f00b204e9800998ecf8427e',
            'downloadSecret' => '9038463',
        ],
        'request' => [
            'enableCookieValidation' => false,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                'POST upload/<project:\w+>/<uploadToken:\w+>' => 'file/upload',
                'GET <file:\w+>_<hash:\w{1,7}>.<extension:\w{3,4}>' => 'image/generate',
                'GET <file:\w+>_<hash:\w{1,7}>/<translit>.<extension:\w{3,4}>' => 'image/generate',
                'GET <file:\w+>_<hash:\w{1,7}><params:_[\w\_-]+>.<extension:\w{3,4}>' => 'image/generate',
                'GET <file:\w+>_<hash:\w{1,7}><params:_[\w\_-]+>/<translit>.<extension:\w{3,4}>' => 'image/generate',
            ],
        ]
    ],
    'params' => $params,
];

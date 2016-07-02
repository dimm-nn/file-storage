<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api\controllers',
    'components' => [
        'user' => [
            'enableSession' => false,
            'identity' => \api\components\Identity::class,
        ],
        'urlManager' => [
            'ruleConfig' => [
                'class' => \yii\rest\UrlRule::class,
            ],
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                [
                    'controller' => 'upload',
                    'extraPatterns' => [
                        'POST upload/<secret:\w+>/<project:\w+>' => 'index',
                    ],
                ],
            ],
        ]
    ],
    'params' => $params,
];

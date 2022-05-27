<?php
return [
    'id' => 'coach',
    'language' => 'ru-RU',
    'sourceLanguage' => 'ru-RU',
    'timeZone' => 'Europe/Moscow',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '/' => 'site/index',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => YII_DEBUG ? [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                ],
            ] : [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                ],
            ],
        ],
        'db' => require __DIR__ . '/db.php',
    ],
    'params' => require __DIR__ . '/params.php',
];

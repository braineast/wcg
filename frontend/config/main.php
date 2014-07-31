<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

$db = require(__DIR__.'/db.php');

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'language' => 'zh_CN',
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'user' => [
            'identityClass' => 'frontend\models\User',
            'enableAutoLogin' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'db'=>$db,
        'urlManager'=>[
            'enablePrettyUrl'=>true,
            'showScriptName'=>false
        ],
        'i18n'=>[
            'translations'=>[
                'yii*'=>[
                    'class'=>'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
    ],
    'params' => $params,
];

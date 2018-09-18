<?php

$db = require __DIR__.'/db.php';
$keys = require __DIR__.'/keys.php';
$params = require __DIR__.'/params.php';
$container = require __DIR__.'/container.php';

return [
    'id'         => 'vetais-api',
    'basePath'   => dirname(__DIR__),
    'bootstrap'  => ['log'],
    'params'     => $params,
    'modules'    => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
    ],
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'sardaryanhrant@gmail.com',
                'password' => 'f!8*DNgmailcom',
                'port' => '587',
                'encryption' => 'tls',
            ],
        ],
        'request'    => [
            'enableCsrfCookie' => false,
            'parsers'          => [
                'application/json' => 'yii\web\JsonParser'
            ],
        ],
        'log'        => [
            'traceLevel'    => 3,
            'flushInterval' => 1,
            'targets'       => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/runtime/logs/main.log',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl'     => true,
            'enableStrictParsing' => true,
            'showScriptName'      => false,
            'rules'               => [
                'test-scheme' => 'test/scheme',
                'test-rels' => 'test/relations',
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/user',
                    'extraPatterns' => [
                        'POST token'     => 'token',
                        'OPTIONS token'     => 'options',

                        'GET {id}/getfriends' => 'getfriends',
                        'OPTIONS {id}/getfriends' => 'options',

                        'PUT updateuser'=>'updateuser',
                        'OPTIONS updateuser'=>'options',

                        'PUT addchatlist/{id}'  => 'addchatlist',
                        'OPTIONS addchatlist/{id}' => 'options',

                        'DELETE removeuserfromchatlist'  => 'removeuserfromchatlist',
                        'OPTIONS removeuserfromchatlist' => 'options',

                        'PUT adduserblacklist'  => 'adduserblacklist',
                        'OPTIONS adduserblacklist' => 'options',    

                        'PUT updatecontact'  => 'updatecontact',
                        'OPTIONS updatecontact' => 'options',  
                        
                        'PUT updatepersonalinfo'  => 'updatepersonalinfo',
                        'OPTIONS updatepersonalinfo' => 'options', 

                        'GET getchatlist'  => 'getchatlist',
                        'OPTIONS getchatlist' => 'options',

                        'POST forgotpassword'     => 'forgotpassword',
                        'OPTIONS forgotpassword'     => 'options',
                    ],
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/messages',
                    'extraPatterns' => [
                        'POST'     => 'createmessage',
                        'OPTIONS'     => 'options',

                        'GET getmessagebyuserid/{id}'   => 'getmessagebyuserid',
                        'OPTIONS getmessagebyuserid/{id}'  => 'options',
                    ]
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/files',
                    'extraPatterns' => [
                        'POST'     => 'upload',
                        'OPTIONS'     => 'options',
                    ]
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/friends',
                    'extraPatterns' => [
                        'POST confirm'     => 'confirm',
                        'OPTIONS confirm'     => 'options',

                        'POST unconfirm'     => 'unconfirm',
                        'OPTIONS unconfirm'     => 'options',
                    ]
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/groups',
                    'extraPatterns' => [
                        'GET {id}/followers'     => 'groupfollowers',
                        'OPTIONS {id}/followers'     => 'options',
                    ]
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/followers',
                    'extraPatterns'=>[

                        'GET news'     => 'news',
                        'OPTIONS news'     => 'options',
                    ]
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/chats',
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller'    => 'v1/posts',
                    'extraPatterns' => [
                        'POST'     => 'createpost',
                        'OPTIONS'     => 'options',

                        'GET wall/{id}'     => 'getpostsbywallid',
                        'OPTIONS wall/{id}'     => 'options',
                    ]
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/comments',
                    'extraPatterns' => [
                        'POST'      => 'createcomment',
                        'OPTIONS'   => 'options',

                    ]
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/albums',
                    'extraPatterns' => [
                        'GET user-albums/{id}'     => 'user-albums',
                        'OPTIONS user-albums/{id}' => 'options',

                        'POST update-files'    => 'update-files',
                        'OPTIONS update-files' => 'options',
                    ]
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/searchs',
                    'extraPatterns' => [
                        'GET'       => 'search',
                        'OPTIONS'   => 'options',
                    ]
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/likes',
                    'extraPatterns' => [
                        'POST'      => 'createlike',
                        'OPTIONS'   => 'options',

                    ]
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/taxonomies',
                    'extraPatterns' => [
                        'POST'      => 'createtax',
                        'OPTIONS'   => 'options',
                    ]
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/videos',
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/notes',

                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/audios',
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/block-users'

                ],

                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/privacys'

                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/votes'
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/questions'
                ],

//                ['class' => 'yii\rest\UrlRule', 'controller' => $routes]
                [
                    'class' => 'app\common\components\entity\EntityUrlRule',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET view/{id}' => 'view',
                        // 'GET {id}' => 'viewusers',
                        'POST sms' => 'sms',                       
                        'GET relationships/{entity}' => 'getrel',
                        'GET {id}/relationships/{entity}' => 'getrel',
                        'GET {id}/{entity}' => 'getrel',
                        'POST {id}/{entity}' => 'addrel',
                        'DELETE {parent_id}/{entity}/{id}' => 'delrel',
                    ]
                ]
            ],
        ],
        'user'       => [
            'identityClass' => 'app\common\models\UserModel',
            'enableSession' => false,
        ],
        'db'         => $db,
        'jwt'        => [
            'class'          => 'app\common\components\Jwt',
            'privateKeyFile' => $keys['privateKeyFile'],
            'publicKeyFile'  => $keys['publicKeyFile'],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'php:Y-m-d',
        ],
        'response'   => [
            'format' => yii\web\Response::FORMAT_JSON,     
        ],
        'errorHandler' => [
            'class' => 'app\common\components\ErrorHandler',
        ]
    ],
    'container' => $container
];
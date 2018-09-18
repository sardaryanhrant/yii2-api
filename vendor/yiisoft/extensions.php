<?php

$vendorDir = dirname(__DIR__);

return array (
  'tuyakhov/yii2-json-api' => 
  array (
    'name' => 'tuyakhov/yii2-json-api',
    'version' => '0.1.6.0',
    'alias' => 
    array (
      '@tuyakhov/jsonapi' => $vendorDir . '/tuyakhov/yii2-json-api/src',
      '@tuyakhov/jsonapi/tests' => $vendorDir . '/tuyakhov/yii2-json-api/tests',
    ),
  ),
  'yiisoft/yii2-faker' => 
  array (
    'name' => 'yiisoft/yii2-faker',
    'version' => '2.0.4.0',
    'alias' => 
    array (
      '@yii/faker' => $vendorDir . '/yiisoft/yii2-faker',
    ),
  ),
  'yiisoft/yii2-swiftmailer' => 
  array (
    'name' => 'yiisoft/yii2-swiftmailer',
    'version' => '2.1.1.0',
    'alias' => 
    array (
      '@yii/swiftmailer' => $vendorDir . '/yiisoft/yii2-swiftmailer/src',
    ),
  ),
  'tigrov/yii2-pgsql' => 
  array (
    'name' => 'tigrov/yii2-pgsql',
    'version' => '1.3.1.0',
    'alias' => 
    array (
      '@tigrov/pgsql' => $vendorDir . '/tigrov/yii2-pgsql/src',
    ),
  ),
);

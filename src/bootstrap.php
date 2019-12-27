<?php
require_once __DIR__.'/../vendor/autoload.php';

// this file is needed when you use this project directly.

date_default_timezone_set("Asia/Shanghai");

$config=[];
if(file_exists(__DIR__.'/../config/config.php')) {
    require __DIR__ . '/../config/config.php';
}
Ark()->setConfig($config);

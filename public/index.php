<?php

use frame\core\App;

error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('PRC');
define('ROOT_PATH', dirname(__DIR__));

// 版本限制
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    throw new \Exception('php version must greater than or equal 7.0.0');
}

// app初始化，自动载入类文件
require ROOT_PATH . '/vendor/autoload.php';
require ROOT_PATH . '/frame/core/App.php';

$response = App::run();
App::send($response);
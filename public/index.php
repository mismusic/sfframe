<?php

use frame\core\App;
use \frame\utils\CoreReflection;

error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('PRC');
define('ROOT_PATH', dirname(__DIR__));

// 版本限制
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    throw new \Exception('php version must greater than or equal 7.0.0');
}

// 加载公共函数
require ROOT_PATH . '/app/common/helper/helper.php';
// app初始化，自动载入类文件
require ROOT_PATH . '/frame/core/App.php';

/*$result = CoreReflection::getConstructor(\test\ReflectionTest::class, false);
foreach ($result as $item) {
    var_dump(CoreReflection::getParameter(\test\ReflectionTest::class, '__construct', $item['name']));
}
var_dump(CoreReflection::getMethods(\test\ReflectionTest::class));
var_dump(CoreReflection::getMethod(\test\ReflectionTest::class, 'test'));*/


//$container = App::getContainer();
//$container->bind(\test\B::class);
/*$result = $container->bind('a', function (App $app) {
    $b = $app->getContainer()->resolve(\test\B::class);
    return new \test\A($b);
});*/
//var_dump($result);
/*$container->alias('a', \test\A::class);
$container->alias('test', 'a');
$container->aliases('a', 'a1');
$container->aliases('a', ['a2', 'a3']);
$result = $container->resolve('a2', ['foo' => '小姐姐']);*/
//var_dump($result);
//var_dump($container->resolve('app'));
//var_dump($container->resolve('container'));
//var_dump($container->resolveMethod(\app\controller\TestController::class, 'index', ['id' => 10000]));
//var_dump($container->resolve('request')->get('name'));
$response = App::run();
App::send($response);
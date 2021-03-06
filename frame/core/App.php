<?php

namespace frame\core;

use app\facade\Config as ConfigFacade;
use frame\core\Config;
use app\console\AppConsole;
use app\exception\AppException;
use frame\core\console\Console;
use frame\core\database\interfaces\DBInterface;
use frame\core\database\Mysql;
use frame\core\event\Event;
use frame\core\log\Log;
use frame\core\route\RouteDispatcher;

class App
{
    protected static $namespace = 'app\\controller';
    /**
     * @var Container
     */
    protected static $container;
    protected static $version = '1.0.0';
    private static $router;
    /**
     * app初始化
     */
    public static function init()
    {
        static::autoload();
        static::$container = static::getContainer();
        $containerParams = App::initContainer();
        foreach ($containerParams as $containerAbstract => $containerConcrete) {
            static::$container->bind($containerAbstract, $containerConcrete);
        }
        $configProviders = ConfigFacade::get('provider');
        foreach ($configProviders as $providerAbstract => $providerConcrete) {
            static::$container->bind($providerAbstract, $providerConcrete);
        }
        static::loadRouter();
        static::dbInit();
    }
    public static function consoleInit()
    {
        App::autoload();
        static::$container = static::getContainer();
        $containerParams = App::initContainer();
        foreach ($containerParams as $containerAbstract => $containerConcrete) {
            static::$container->bind($containerAbstract, $containerConcrete);
        }
    }
    /**
     * 自动加载类文件
     * @param string $extension
     */
    public static function autoload($extension = '.php')
    {
        spl_autoload_register(function ($class) use ($extension) {
            $rootDir = ROOT_PATH . '/';
            $fileName = $rootDir . ltrim(str_replace('\\', '/', $class), '/') . $extension;
            if (! is_file($fileName)) {
                throw new AppException('app autoload file not found');
            }
            include_once $fileName;
        });
    }
    /**
     * 获取容器
     * @return Container
     */
    public static function getContainer()
    {
        return Container::getInstance();
    }
    /**
     * 初始化容器
     * @return array
     */
    public static function initContainer()
    {
        return [
            'app' => static::$container->resolve(App::class),
            'container' => static::$container,
            Console::class => AppConsole::class,
            'route' => RouteDispatcher::class,
            'request' => static::$container->resolve(Request::class),
            'response' => static::$container->resolve(Response::class),
            'env' => Env::class,
            'config' => Config::class,
            'log' => Log::class,
            'event' => Event::class,
        ];
    }
    public static function parseRouter()
    {
        $path = static::$container->resolve('request')->path();
        $pathArr = array_filter(explode('/', trim($path, '/')), 'trim');
        if (empty($pathArr)) {
            throw new AppException('app parse route missing controller and method');
        }
        if (count($pathArr) === 1) {
            throw new AppException('app parse route missing method');
        }
        list($controller, $method) = $pathArr;
        static::$router = [
            'controller' => $controller,
            'method' => $method,
        ];
    }
    /**
     * 运行app
     * @return mixed|null|object
     * @throws \Exception
     */
    public static function run()
    {
        App::init();
        $container = Container::getInstance();
        $request = $container->resolve('request');
        $route = $container->resolve('route');
        $response = $route->run($request);
        return $response;
    }

    /**
     * 加载用户定义的路由
     */
    public static function loadRouter()
    {
        $routeFileList = find_dir(static::routePath(), '.php');
        // 加载
        foreach ($routeFileList as $routeFile) {
            require_once $routeFile;
        }
    }
    /**
     * 发送响应内容
     */
    public static function send($response)
    {
        if (! is_string($response) && ! is_bool($response) && ! is_numeric($response)
            && ! is_null($response) && ! is_array($response) && ! $response instanceof Response)
        {
            throw new AppException('app send param response type error');
        }
        if ($response instanceof Response) {
            return $response->send();
        }
        $responseClass = static::$container->resolve('response');
         if (is_array($response)) {
            return $responseClass->json($response)->send();
        }
        return $responseClass->body($response)->send();
    }
    public static function getVersion()
    {
        return self::$version;
    }
    public static function rootPath()
    {
        return preg_replace('#[/\\\\]$#', '', ROOT_PATH);
    }
    public static function appPath()
    {
        return static::rootPath() . DIRECTORY_SEPARATOR . 'app';
    }
    public static function routePath()
    {
        return static::rootPath() . DIRECTORY_SEPARATOR . 'route';
    }
    public static function configPath()
    {
        return static::rootPath() . DIRECTORY_SEPARATOR . 'config';
    }
    public static function logPath()
    {
        return static::rootPath() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'log';
    }
    protected static function dbInit()
    {
        $config = static::$container->resolve(Config::class)->get('database');
        $dbType = $config['connections'][$config['default']]['type'];
        switch (strtolower($dbType)) {
            case 'mysql':
                static::$container->bind(DBInterface::class, Mysql::class);
                break;
            default:
        }
        static::$container->alias('DB', DBInterface::class);
    }
}
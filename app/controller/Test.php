<?php

namespace app\controller;

use app\facade\DB;
use app\facade\Event;
use app\facade\Log;
use app\facade\Route;
use frame\core\App;
use frame\core\command\Command;
use frame\core\command\Input;
use frame\core\command\input\InputArgument;
use frame\core\command\input\InputOption;
use frame\core\Config;
use frame\core\database\interfaces\DBInterface;
use frame\core\database\Mysql;
use frame\core\database\query\Query;
use frame\core\Env;
use frame\core\Request;
use frame\core\Response;
use frame\core\route\RouteDispatcher;

class Test
{
    protected $config;
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    public function index(Request $request, Response $response)
    {
        $data = [
            //'id' => $id,
            'name' => $request->get('name'),
            'params' => $request->get(),
            'ip' => $request->ip(),
            'document_root' => $request->root(),
            'software' => $request->serverSoftware(),
        ];
        return $response->json($data)->header('Foo', 'bar')->headers(['Set-Cookie' => 'session_id=abc123', 'Access-Control-Allow-Origin' => '*']);
    }
    public function abc()
    {
        return [
            'class_name' => __CLASS__ . __FILE__ . __LINE__,
            'content' => 'this is abc',
            'time' => date('Y-m-d H:i:s'),
        ];
    }
    public function test(DBInterface $db)
    {
        $result = DB::query()->name('user')
            ->where('id', 'exp', 'in (1,3,4) or id is null')
            ->orWhere('name', 'like', '艾%')
            ->where(function ($query) {
                $query->where('status', '=', 1)->orWhere(function ($query) {
                    $query->where(['sex' => '女'])->orWhere('age', '=', 18);
                });
            })
           /* ->orWhere(function ($query) {
                $query->where('create_time', 'between', ['2021-02-20 10:00:00', '2021-02-20 10:00:00']);
            })*/
            ->field('id', 'name', 'sex', 'age')
            ->limit(15)
            ->get();
        //var_dump($db->getSql());
        return $result;
    }
    public function test1(DBInterface $db)
    {
        /*$result = $db::query()->name('user')->insert([
            ['name' => '小姐姐', 'sex' => '女', 'age' => 22],
            ['name' => '艾比', 'sex' => '女', 'age' => 21],
            ['name' => '唐山', 'sex' => '男', 'age' => 24],
            ['name' => '菜鸟', 'sex' => '男', 'age' => 21],
            ['name' => '小白菜', 'sex' => '男', 'age' => 18],
        ]);*/
        /*$result = $db::query()->name('article')->insert([
            ['user_id' => 2, 'title' => '文章标题4', 'content' => '内容4', 'create_time' => date('YmdHis')],
            ['user_id' => 3, 'title' => '文章标题5', 'content' => '内容5', 'create_time' => date('YmdHis')],
            ['user_id' => 5, 'title' => '文章标题6', 'content' => '内容6', 'create_time' => date('YmdHis')],
        ]);*/
        //$result = $mysql->getAttr();
        /*$result = $db::query()
            ->name('`user` mu')
            ->field('mu.id', 'mu.name', 'ma.title')
            //->leftJoin('mis_article as ma', 'mu.id = ma.user_id')
            //->leftJoin('comment as mc', 'ma.id = mc.article_id')
            ->where('mu.id', 1)
            //->where('mu.name', '艾比')
            ->order(['mu.id' => 'desc', 'mu.age' => 'asc'])
            ->limit(10)
            ->getQuerySql();*/
        /*$result = $db::query()
            ->name('user')
            ->where('id', 5)
            ->update(['age' => 18, 'name' => '菜鸟']);*/
        $result = DB::query()
            ->name('user')
            ->where('id', 'in', [1,2,4])
            ->where('name', 'instr', '小')
            ->where('age', 'not null')
            ->get();
        //var_dump($db->getSql(), $db->rumTime());
        /*$result = $db::query()
            ->name('user')
            ->updateBatch([
                ['id' => 1, 'name' => '艾比1', 'age' => 18],
                ['id' => 4, 'name' => '吴白', 'age' => 20],
            ]);*/
        //$result = DB::select('select * from mis_user where name like :name',['name' => '罗%']);
        /*$result = DB::execute('insert into mis_user (name,sex,age,create_time) values
          ("罗峰", "男", 10000, 20210221145600)');*/
        /*$result = DB::query()
            ->name('user')
            ->where('id', 'in', [6, 7])
            ->delete();*/
        return $result;
    }

    public function log()
    {
        return Log::getLog();
    }
    public function env(Env $env)
    {
        return $env->get('app');
    }
    public function event()
    {
        Event::bind('aa', function (App $app) {
            return $app::getContainer()->resolve('log')->info('ddd event bind test');
        });
        //Event::dispatch('aa');
        var_dump(Event::getJob(), $this->config);
    }
    public function input(Input $input, Command $command)
    {
        /*$options = $input->setDefinitions([
            new InputArgument('a', InputArgument::REQUIRED, '参数a'),
            new InputArgument('foo', InputArgument::OPTIONAL, '参数foo', '可选参数bar'),
            new InputOption('ooo', InputOption::VALUE_NONE, '参数ooo', 'O'),
            new InputOption('oa', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, '参数oa，是一个数组类型',
                'A', '数组值oa'),
        ])->getDefinitions()->getOptions();
        $args = $input->getArgument();
        //var_dump($input);
        return $args + $options;*/
        $sign = 'command:name {-Q | --option=test : The option description.}
        {foo=bar:test描述} {argument* : The argument description.}';
        //var_dump($sign);
    }
    public function route(RouteDispatcher $routeDispatcher, Request $request)
    {
        $routeDispatcher->prefix('/index')
            ->namespace('app\\controller\\index')
            ->middleware('indexMiddleware')
            ->group(function ($routes) {
                $routes->addRule('/route', ['Test', 'route'], ['patch', 'put', 'options'])->prefix('/test/')->name('route');
                $routes->post('/test', ['Test', 'test'])->middleware('TestMiddleware')->name('test');
                $routes->prefix('/Admin')
                    ->name('admin.')
                    ->namespace('admin')
                    ->group(function ($routes) {
                        $routes->addRule('user/', ['User', 'register'], 'post')->prefix('/my/')->middleware('UserMiddleware')->name('reg');
                    });
            })
            ->name('index.');
        $routeDispatcher->addRule('test/route/<id?>/<name>', ['Foo', 'index'], 'get')->name('foo.bar')
            //->match('name', '.+')
            ->match('id', '\d+')
            ->namespace('app');
        $routeDispatcher->prefix('/group2')->group(function ($routes) {
            $routes->only('custom', ['C', 'm'], ['get', 'post'])->name('cust');
        })->name('group222.');
        //$routes = $routeDispatcher->getRouteList();
        //$routeDispatcher->findRoute($request);
        //var_dump($routes);
        //foreach ($routes as $k => $route) {
            //var_dump($route->getMatches());
            //$route = $route->getRoute();
            /*var_dump([
                $k, //$route['variables'], $route['matches'], //
                //$route['route'], $route['name'], //$route['method'], //$route['middleware'], $route['callable'],
            ]);*/
        //}
        return 'this Test route';
    }
}
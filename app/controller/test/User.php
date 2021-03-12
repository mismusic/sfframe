<?php

namespace app\controller\test;

use app\facade\DB;
use frame\core\App;

class User {

    public function index()
    {
        ob_start();
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $traceString = ob_get_clean();
        var_dump($traceString);
        var_dump('这里执行控制器逻辑');
        return 'this is test/User index';
    }

    public function getUser()
    {
        return DB::query()->name('user')
            //->where('id', 1)
            ->get();
    }

}
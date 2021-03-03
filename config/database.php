<?php

return [
    'default'       =>    'mysql',
    'connections'    =>    [
        'mysql'    =>    [
            // 数据库类型
            'type'        => env('database.type', 'mysql'),
            // 服务器地址
            'host'        => env('database.host', '127.0.0.1'),
            // 数据库名
            'db_name'     => env('database.db_name'),
            // 数据库用户名
            'username'    => env('database.username'),
            // 数据库密码
            'password'    => env('database.password'),
            // 数据库连接端口
            'port'         => env('database.port', 3306),
            // 数据库连接参数
            'options'      => [],
            // 数据库编码默认采用utf8
            'charset'      => env('database.charset', 'utf8'),
            // 数据库表前缀
            'prefix'       => env('database.prefix', ''),
        ],
    ],
];
<?php

if (! function_exists('is_cgi')) {
    function is_cgi()
    {
        $sapiType = php_sapi_name();
        if (substr($sapiType, 0, 3) == 'cgi')
        {
            return true;
        }
        return false;
    }
}

if (! function_exists('is_cli')) {
    function is_cli()
    {
        $sapiType = php_sapi_name();
        if (substr($sapiType, 0, 3) == 'cli')
        {
            return true;
        }
        return false;
    }
}

if (! function_exists('env')) {
    function env(string $name = null, $default = null)
    {
        $env = app()->getContainer()->resolve('env');
        return $env->get($name, $default);
    }
}

if (! function_exists('is_dev')) {
    function is_dev() :bool
    {
        $env = strtolower(env('app.env', 'dev'));
        if (strcmp($env, 'dev') === 0) {
            return true;
        }
        return false;
    }
}

if (! function_exists('config')) {
    function config(string $name = null, $default = null)
    {
        $env = app()->getContainer()->resolve('config');
        return $env->get($name, $default);
    }
}

if (! function_exists('app')) {
    function app() :\frame\core\App
    {
        return \frame\core\App::getContainer()->resolve('app');
    }
}

if (! function_exists('request')) {
    function request() :\frame\core\Request
    {
        return app()->getContainer()->resolve('request');
    }
}

if (! function_exists('response')) {
    function response() :\frame\core\Response
    {
        return app()->getContainer()->resolve('response');
    }
}

if (! function_exists('find_dir')) {
    function find_dir(string $dir, string $pattern = null, int $level = null) :array
    {
        $i = 1;
        $files = [];
        $dir = realpath(preg_replace("#[/\\\\]$#", '', $dir));
        $dirs = [$dir];
        while ($dirs) {
            if (! is_null($level) && $i > $level) {
                break 1;
            }
            $dirsTmp = [];
            foreach ($dirs as $dirItem) {
                if (empty($dirItem)) {
                    continue 1;
                }
                $scanList = scandir($dirItem);
                if (! $scanList) {
                    continue 1;
                }
                foreach ($scanList as $scanItem) {
                    if ($scanItem === '.' || $scanItem === '..') {
                        continue 1;
                    }
                    $fullPath = $dirItem . DIRECTORY_SEPARATOR . $scanItem;
                    if (is_dir($fullPath)) {
                        $dirsTmp[] = $fullPath;
                    }
                    else if (is_file($fullPath)) {
                        if (is_null($pattern)) {
                            $files[] = $fullPath;
                        } else {
                            if (stripos($fullPath, $pattern) !== false) {
                                $files[] = $fullPath;
                            }
                        }
                    }
                }
            }
            $dirs = $dirsTmp;
            $i ++;
        }
        return $files;
    }
}

if (! function_exists('select_file')) {
    function select_file(string $pattern) :array
    {
        $files = glob($pattern);
        if (empty($files)) {
            return [];
        }
        foreach ($files as & $file) {
            $file = realpath($file);
        }
        return $files;
    }
}

if (! function_exists('gen_random')) {
    function gen_random(int $length = 16, $isNumber = false, $isUpper = false) :string
    {
        $number = range(0, 9);
        $upperLetter = range('A', 'Z');
        $letter = $upperLetter;
        if (empty($isUpper)) {
            $letter = array_merge($upperLetter, range('a', 'z'));
        }
        $randomStr = $number;
        if (empty($isNumber)) {
            $randomStr = array_merge($number, $letter);
        }
        $strCount = count($randomStr);
        $result = '';
        for ($i = 0; $i < $length; $i ++)
        {
            $result .= $randomStr[random_int(0, $strCount - 1)];
        }
        return (string) $result;
    }
}

/**
 * 生成批量更新的sql语句
 * @param array $data
 * @param string $tableName
 * @param string $pkName
 * @return string
 */
if (! function_exists('batch_update_sql')) {
    function batch_update_sql(array $data, string $tableName, $pkName = 'id') :string
    {
        if (! is_array(reset($data))) {  // 如果数据只是一维数组就把它转变成一个二维数组
            $data = [$data];
        }
        if (! array_contain_keys($data, [$pkName])) {
            throw_error(sprintf('sql column %s not exists', $pkName));
        }
        $uColumns = array_keys(reset($data));  // 获取所有需要更新的字段名
        $pIndex = array_search($pkName, $uColumns);
        unset($uColumns[$pIndex]);  // 去掉主键字段值
        $ids = '"' . join('","', array_column($data, $pkName)) . '"';
        $sql = "update {$tableName} set ";
        $case = '';
        foreach ($uColumns as $uColumn) {
            $case .= "`$uColumn` = case `{$pkName}` ";
            $when = '';
            foreach ($data as $item) {
                $value = addslashes($item[$uColumn]);  // 对要更新的值进行过滤处理
                $when .= "when \"{$item[$pkName]}\" then \"$value\" ";
            }
            $case .= $when . 'end,';
        }
        $case = rtrim($case, ',');  // 去掉最后多余的逗号 数据示例 name = case id when 1 then test when 2 then foo end
        $sql .= $case . " where {$pkName} in({$ids})";
        return $sql;
    }
}

/**
 * 检查数组里面是否包括那些key值
 * @param array $data
 * @param $keys
 * @return bool
 */
if (! function_exists('array_contain_keys')) {
    function array_contain_keys(array $data, $keys) {
        $keys = (array) $keys;
        if (! array_diff($keys, array_keys($data))) {
            return true;
        }
        foreach ($data as $values) {
            if (is_array($values)) {
                foreach ($keys as $key) {
                    if (! array_key_exists($key, $values)) {
                        return array_contain_keys($values, $keys);
                    }
                }
            } else {
                return false;
            }
        }
        return true;
    }
}
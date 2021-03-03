<?php

declare(strict_types = 1);

//cli_set_process_title('my process name mismusic');
/*var_dump($argv, $_SERVER['argv'], $_SERVER['argc'], phpversion(), getmypid(),
    php_uname('a'), cli_get_process_title());*/
//$optArg = getopt('a:b', ['required:', 'optional::'], $optind);
//var_dump($argv, $optind, $optArg);
include __DIR__ . '/' . Input::class . '.php';
include __DIR__ . '/' . Output::class . '.php';

//var_dump("\033[0;31m测试颜色\033[0m");
//var_dump($test);
/*$input = new Input();
$input->addArgument('a', Input::ARGUMENT_REQUIRED, '参数a')
    ->addOption('required', Input::OPTION_VALUE_REQUIRED, '参数required')
    ->addOption('optional', Input::OPTION_VALUE_OPTIONAL, '测试optional', '默认值optional')
    ->addOption('t', Input::OPTION_VALUE_OPTIONAL, '参数t', '默认值t')
    ->parseParams();*/

//$output = new Output();
//$output->writeln('行下输。');
$a = 1;
$b = 2;
function test() {
    var_dump($GLOBALS['argv']);
    $GLOBALS['argv'][] = 'aaa';
}
test();
var_dump($argv, $_SERVER['argv']);
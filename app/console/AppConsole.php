<?php

namespace app\console;

use app\console\command\TestCommand;
use frame\core\App;
use frame\core\console\Console;

class AppConsole extends Console
{
    /*protected function customCommands(): array
    {
        return [
            'sf:test' => TestCommand::class,
        ];
    }*/

    protected function loading(): array
    {
        return [
            //App::rootPath() . '/frame/core/console/command',
            App::appPath() . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR . 'command',
        ];
    }
}
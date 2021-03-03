<?php

namespace frame\core\console\command;

use frame\core\App;
use frame\core\console\Command;
use frame\core\console\Console;
use frame\core\console\Input;
use frame\core\console\Output;

class ListCommand extends Command
{
    public static $command = 'sf:list';
    public static $description = '命令列表';

    public function handle(Input $input, Output $output)
    {
        $string = <<<EOF
Version:%s
Usage:
    command [options] [arguments]
    
Available commands:
%s    
EOF;
        $string = sprintf($string, App::getVersion(), $this->commandsString());
        $output->writeln($string);
    }
    protected function commandsString() :string
    {
        $console = App::getContainer()->resolve(Console::class);
        $commandList = $console->getCommandList();

        $maxLength = 0;
        $string = '';
        foreach ($commandList as $groupName => $items) {
            foreach ($items as $item) {
                $commandLen = strlen($item['command']);
                if ($commandLen > $maxLength) {
                    $maxLength = $commandLen;
                }
            }
        }
        $maxLength += 6;
        foreach ($commandList as $groupName => $items) {
            $string .= ' ' . $groupName . PHP_EOL;
            foreach ($items as $item) {
                $string .= sprintf("  %s%s%s",
                    $item['command'], str_repeat(' ', $maxLength - strlen($item['command'])), $item['description']) . PHP_EOL;
            }
        }
        $string = rtrim($string, PHP_EOL);
        return $string;
    }
}
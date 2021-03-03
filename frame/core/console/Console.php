<?php

namespace frame\core\console;

use frame\core\App;
use frame\core\console\command\ControllerCommand;
use frame\core\console\command\ListCommand;

abstract class Console
{
    // 命令列表
    protected $commandMap = [
        'sf:list' => ListCommand::class,
        'make:controller' => ControllerCommand::class,
    ];
    protected $input;

    public function __construct(Input $input)
    {
        $this->input = $input;
        $this->parseCommands();
    }
    public function run()
    {
        $command = $this->findCommand();
        return $command->run();
    }
    public function findCommand() :Command
    {
        $commandName = $this->input->getCommand();
        if (! isset($this->commandMap[$commandName])) {
            throw new \LogicException('command not found');
        }
        return App::getContainer()->resolve($this->commandMap[$commandName]);
    }
    public function getCommandList() :array
    {
        $result = [];
        foreach ($this->commandMap as $k => $command) {
            [$groupName] = explode(':', $k);
            $description = $command::$description;
            $result[$groupName][] = [
                'command' => $k,
                'description' => $description,
            ];
        }
        return $result;
    }
    protected function parseCommands()
    {
        $loadingCommands = $this->loading();
        $loadingFiles = [];
        foreach ($loadingCommands as $loadingCommand) {
            $loadingFiles = array_merge($loadingFiles, find_dir($loadingCommand, '.php', 1));
        }
        $loadingCommands = [];
        foreach ($loadingFiles as $loadingFile) {
            $className = str_replace([App::rootPath(), '.php', '/'], ['', '', '\\'], $loadingFile);
            if (is_subclass_of($className, Command::class)) {
                $loadingCommands[$className::getCommand()] = $className;
            }
        }
        $custCommands = $this->customCommands();
        foreach ($custCommands as $custCommand) {
            if (! is_subclass_of($custCommand, Command::class)) {
                throw new \LogicException('custom command must extends class ' . Command::class);
            }
        }
        $this->commandMap = array_merge($this->commandMap, $loadingCommands, $custCommands);
    }
    protected function customCommands() :array
    {
        return [];
    }
    abstract protected function loading() :array;
}
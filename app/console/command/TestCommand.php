<?php

namespace app\console\command;

use frame\core\console\Command;
use frame\core\console\Input;
use frame\core\console\input\InputArgument;
use frame\core\console\input\InputOption;
use frame\core\console\Output;

class TestCommand extends Command
{
    public static $command = 'sf:test';
    public static $signature = '{sex=无}
    {-A | --age=18 : 年龄}';
    public static $description = 'Test命令行';

    protected function configure()
    {
        $this->input->setDefinitions([
            new InputArgument('length', InputArgument::REQUIRED, '长度'),
            new InputArgument('name', InputArgument::OPTIONAL, '名称', '小姐姐'),
            //new InputOption('data', InputOption::VALUE_IS_ARRAY, '数据', 'D'),
        ]);
    }
    public function handle(Input $input, Output $output)
    {
        $output->writeln('this is ' . __CLASS__);
        $output->writeln($input->getArgument(), $input->getOption());
    }
}
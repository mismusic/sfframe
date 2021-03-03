<?php

declare(strict_types = 1);

namespace frame\core\console;

use frame\core\App;
use frame\core\console\input\InputArgument;
use frame\core\console\input\InputOption;

class Command
{
    /**
     * @var App
     */
    protected $app;
    /**
     * @var Input
     */
    protected $input;
    /**
     * @var Output
     */
    protected $output;
    public static $command = '';
    public static $signature = '';
    public static $description = '';

    public function __construct(App $app, Input $input, Output $output)
    {
        $this->app = $app;
        $this->input = $input;
        $this->output = $output;
        $this->configure();
        $this->parseSignature();
    }
    public function run()
    {
        $this->input->explainParameters();  // 分析需要的参数
        $this->handle($this->input, $this->output);
    }
    public function input()
    {
        return $this->input;
    }
    public function handle(Input $input, Output $output)
    {
        $this->execute($input, $output);
    }
    public static function getCommand() :string
    {
        if (static::$command) {
            return static::$command;
        }
        if (preg_match('#[\w]+:[\w]+#ix', static::$signature, $mat))
        {
            return $mat[0];
        }
        throw new \LogicException('get command name failed');
    }
    protected function execute(Input $input, Output $output)
    {
        $output->writeln(PHP_OS);
        $output->writeln(array_merge($input->getArgument(), $input->getOption()));
    }
    protected function configure()
    {
    }
    protected function parseSignature()
    {
        $params = [];
        if (preg_match('#( [\w]+:[\w]+ )?[^{]*( (\{[^{}]+\}[^{]*)* )#ix', static::$signature, $mat)) {
            if (isset($mat[1])) {
                static::$command = trim($mat[1]);
            }
            if (isset($mat[2])) {
                if (preg_match_all('#\{ ( (?:[a-zA-Z-][-\w]* [\*=]? [^\s\|\}]* \s*\|?\s*)+ ) \s* :? \s* ([^}]*) \}#ix', $mat[2], $mat1))
                {
                    if ($mat1[1]) {
                        foreach ($mat1[1] as $k => $val) {
                            $val = trim($val);
                            $description = '';
                            if (isset($mat1[2][$k])) {
                                $description = $mat1[2][$k];
                            }
                            if ($val[0] === '-' || strpos($val, '--') === 0) {
                                if ($val === '-' || $val === '--') {
                                    continue 1;
                                }
                                $valArr = explode('=', $val);
                                $shortName = '';
                                $name = preg_replace('#^(--|-)#', '', trim($valArr[0]));
                                if (strpos($valArr[0], '|')) {
                                    $nameArr = array_map(function ($val) {
                                        return trim($val);
                                    }, explode('|', $valArr[0]));
                                    foreach ($nameArr as $nameItem) {
                                        if (substr($nameItem, 0, 2) === '--') {
                                            $name = substr($nameItem, 2);
                                        }
                                        else if (substr($nameItem, 0, 1) === '-') {
                                            $shortName = substr($nameItem, 1);
                                        }
                                    }
                                }
                                $default = null;
                                if (! isset($valArr[1])) {
                                    $type = InputOption::VALUE_NONE;
                                    $default = false;
                                } else {
                                    if (empty($valArr[1])) {
                                        $type = InputOption::VALUE_REQUIRED;
                                        $default = null;
                                    } else {
                                        $valArr[1] = trim($valArr[1]);
                                        if ($valArr[1] === '*') {
                                            $type = InputOption::VALUE_IS_ARRAY;
                                            $default = null;
                                        } else {
                                            $type = InputOption::VALUE_OPTIONAL;
                                            $default = $valArr[1];
                                        }
                                    }
                                }
                                $params[] = new InputOption($name, $type, $description, $shortName, $default);
                            } else {
                                if (empty($val)) {
                                    continue 1;
                                }
                                $endStr = strlen($val) - 1;
                                if ($val[$endStr] === '*') {  // is_array
                                    $name = str_replace('*', '', $val);
                                    $type = InputArgument::IS_ARRAY;
                                    $default = null;
                                }
                                else if (strpos($val, '=')) {
                                    $valArr = explode('=', $val);
                                    $name = $valArr[0];
                                    if (empty($valArr[1])) {  // required
                                        $type = InputArgument::REQUIRED;
                                        $default = null;
                                    } else {  // optional
                                        $type = InputArgument::OPTIONAL;
                                        $default = $valArr[1];
                                    }
                                }
                                else {
                                    continue 1;
                                }
                                $params[] = new InputArgument($name, $type, $description, $default);
                            }
                        }
                    }
                }
            }
        }
        $this->input->setDefinitions($params);
        return $this;
    }
}
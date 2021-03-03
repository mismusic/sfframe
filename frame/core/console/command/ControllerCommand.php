<?php

namespace frame\core\console\command;

use frame\core\App;
use frame\core\console\Command;
use frame\core\console\Input;
use frame\core\console\Output;

class ControllerCommand extends Command
{
    public static $signature = 'make:controller
     {--class= : 类名}
     {-n | --namespace=app\controller : 命名空间}
     ';
    public static $description = '创建控制器类';

    public function handle(Input $input, Output $output)
    {
        $string = <<<EOF
<?php

namespace %s;

class %s {
    
}
EOF;

        extract($input->getOption());
        $dir = App::rootPath() . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR,
                trim($namespace, '\\'));
        if (! is_dir($dir)) {
            mkdir($dir, true, 0755);
        }
        $file = $dir . DIRECTORY_SEPARATOR . $class . '.php';
        $fp = fopen($file, 'w');
        if (fwrite($fp, sprintf($string, $namespace, $class))) {
            $output->writeln('created controller ' . trim($namespace, '\\') . '\\' . $class . ' successful');
        }
    }
}
<?php

namespace frame\core\event;

use app\exception\EventException;
use frame\core\Config;
use frame\core\Container;
use frame\core\event\interfaces\ListenerInterface;
use frame\utils\CoreReflection;

class Event
{
    protected $config;
    protected $jobs = [];

    public function __construct(Config $config)
    {
        $this->config = $config->get();
        if ($this->config['event']) {
            $this->jobs = $this->config['event'];
        }
    }
    public function bind(string $identified, $value) :void
    {
        $identified = trim($identified);
        $this->jobs[$identified][] = $value;
    }
    public function dispatch($identified) :bool
    {
        if (is_object($identified)) {
            $className = get_class($identified);
        } else {
            $className = $identified;
        }
        if (! isset($this->jobs[$className])) {
            return false;
        }
        try {
            foreach ((array) $this->jobs[$className] as $listener)
            {
                try {
                    $isClass = class_exists($className);
                } catch (\Exception $e) {
                    $isClass = false;
                }
                if ($isClass) {
                    if (is_object($identified)) {
                        $event = $identified;
                    } else {
                        $event = Container::getInstance()->resolve($className, [], false);
                    }
                    if (! CoreReflection::implementsInterface($listener, ListenerInterface::class)) {
                        throw new EventException(sprintf('event dispatch value must of %s', ListenerInterface::class));
                    }
                    Container::getInstance()->resolveMethod($listener, 'handle', ['event' => $event]);
                } else {
                    if (is_callable($listener)) {
                        $this->closureHandler($listener);
                    }
                    else if (is_array($listener) || is_string($listener)) {
                        call_user_func($listener);
                    }
                }
            }
        } catch (EventException $e) {
            throw new EventException($e->getMessage());
        }
        return true;
    }
    public function getJob(string $identified = null) :array
    {
        if (empty($identified)) {
            return $this->jobs;
        }
        else if (is_string($identified)) {
            return $this->jobs[$identified] ?? [];
        }
        return [];
    }
    protected function closureHandler(callable $closure)
    {
        return $closure(Container::getInstance()->resolve('app'));
    }
}
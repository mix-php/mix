<?php

namespace Mix\Console;

use Mix\Bean\ApplicationContext;
use Mix\Bean\BeanInjector;
use Mix\Console\CommandLine\Argument;
use Mix\Console\CommandLine\Flag;
use Mix\Console\Event\CommandBeforeExecuteEvent;
use Mix\Console\Exception\NotFoundException;
use Mix\Console\Helper\ConfigHelper;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Application
 * @package Mix\Console
 * @author liu,jian <coder.keda@gmail.com>
 */
class Application
{

    /**
     * 应用名称
     * @var string
     */
    public $appName = 'app-console';

    /**
     * 应用版本
     * @var string
     */
    public $appVersion = '0.0.0';

    /**
     * 应用调试
     * @var bool
     */
    public $appDebug = true;

    /**
     * 基础路径
     * @var string
     */
    public $basePath = '';

    /**
     * 命令路径
     * @var string
     */
    public $commandPath = '';

    /**
     * 依赖路径
     * @var string
     */
    public $beanPath = '';

    /**
     * 协程
     * @var array
     */
    public $coroutine = [
        'enable'  => true,
        'options' => [
            'max_coroutine' => 300000,
            'hook_flags'    => 1879048191, // SWOOLE_HOOK_ALL
        ],
    ];

    /**
     * Context
     * @var ApplicationContext
     */
    public $context;

    /**
     * 命令
     * @var array
     */
    public $commands = [];

    /**
     * 依赖配置
     * @var array
     */
    public $beans = [];

    /**
     * Error
     * @var Error
     */
    public $error;

    /**
     * EventDispatcher
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * 是否为单命令
     * @var bool
     */
    protected $isSingleCommand = false;

    /**
     * Application constructor.
     * @param array $config
     * @param string $dispatcherName
     * @param string $errorName
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config, string $dispatcherName = 'dispatcher', string $errorName = 'error')
    {
        // 注入
        BeanInjector::inject($this, $config);
        // 保存引用
        \Mix::$app = $this;
        // 初始化上下文
        if ($this->beanPath != '') {
            $this->beans = ConfigHelper::each($this->beanPath);
        }
        $this->context = new ApplicationContext($this->beans);
        // 加载核心库
        $this->dispatcher = $this->context->get($dispatcherName);
        $this->error      = $this->context->get($errorName);
        // 加载命令
        if ($this->commandPath != '') {
            $this->commands = ConfigHelper::each($this->commandPath);
        }
        // 是否为单命令
        $commands              = $this->commands;
        $frist                 = array_shift($commands);
        $this->isSingleCommand = is_string($frist);
    }

    /**
     * 执行功能 (CLI模式)
     */
    public function run()
    {
        if (PHP_SAPI != 'cli') {
            throw new \RuntimeException('please run in cli mode.');
        }
        Flag::init();
        if (Argument::command() == '') {
            if (Flag::bool(['h', 'help'], false)) {
                $this->help();
                return;
            }
            if (Flag::bool(['v', 'version'], false)) {
                $this->version();
                return;
            }
            $options = Flag::options();
            if (empty($options)) {
                $this->help();
                return;
            } elseif ($this->isSingleCommand) {
                // 单命令执行
                $this->callCommand(Argument::command());
                return;
            }
            $keys   = array_keys($options);
            $flag   = array_shift($keys);
            $script = Argument::script();
            throw new NotFoundException("flag provided but not defined: '{$flag}', see '{$script} --help'."); // 这里只是全局flag效验
        }
        if (Argument::command() !== '' && Flag::bool('help', false)) {
            $this->commandHelp();
            return;
        }
        // 非单命令执行
        $this->callCommand(Argument::command());
    }

    /**
     * 帮助
     */
    protected function help()
    {
        $script = Argument::script();
        println("Usage: {$script}" . ($this->isSingleCommand ? '' : ' [OPTIONS] COMMAND') . " [opt...]");
        $this->printOptions();
        if (!$this->isSingleCommand) {
            $this->printCommands();
        } else {
            $this->printCommandOptions();
        }
        println('');
        println("Run '{$script}" . ($this->isSingleCommand ? '' : ' COMMAND') . " --help' for more information on a command.");
        println('');
        println("Developed with Mix PHP framework. (openmix.org/mix-php)");
    }

    /**
     * 命令帮助
     */
    protected function commandHelp()
    {
        $script  = Argument::script();
        $command = Argument::command();
        println("Usage: {$script} {$command} [opt...]");
        $this->printCommandOptions();
        println('');
        println("Developed with Mix PHP framework. (openmix.org/mix-php)");
    }

    /**
     * 版本
     */
    protected function version()
    {
        $appName          = \Mix::$app->appName;
        $appVersion       = \Mix::$app->appVersion;
        $frameworkVersion = \Mix::$version;
        println("{$appName} version {$appVersion}, framework version {$frameworkVersion}");
    }

    /**
     * 打印选项列表
     */
    protected function printOptions()
    {
        $tabs = "\t";
        println('');
        println('Global Options:');
        println("  -h, --help{$tabs}Print usage");
        println("  -v, --version{$tabs}Print version information");
    }

    /**
     * 打印命令列表
     */
    protected function printCommands()
    {
        println('');
        println('Commands:');
        foreach ($this->commands as $key => $item) {
            $command    = $key;
            $subCommand = '';
            $usage      = $item['usage'] ?? '';
            if (strpos($key, ' ') !== false) {
                list($command, $subCommand) = explode(' ', $key);
            }
            if ($subCommand == '') {
                println("  {$command}\t{$usage}");
            } else {
                println("  {$command} {$subCommand}\t{$usage}");
            }
        }
    }

    /**
     * 打印命令选项列表
     */
    protected function printCommandOptions()
    {
        $command = Argument::command();
        $options = [];
        if (!$this->isSingleCommand) {
            if (isset($this->commands[$command]['options'])) {
                $options = $this->commands[$command]['options'];
            }
        } else {
            if (isset($this->commands['options'])) {
                $options = $this->commands['options'];
            }
        }
        if (empty($options)) {
            return;
        }
        println('');
        println('Command Options:');
        foreach ($options as $option) {
            $names = array_shift($option);
            if (is_string($names)) {
                $names = [$names];
            }
            $flags = [];
            foreach ($names as $name) {
                if (strlen($name) == 1) {
                    $flags[] = "-{$name}";
                } else {
                    $flags[] = "--{$name}";
                }
            }
            $flag  = implode(', ', $flags);
            $usage = $option['usage'] ?? '';
            println("  {$flag}\t{$usage}");
        }
    }

    /**
     * 调用命令
     * @param string $command
     */
    public function callCommand(string $command)
    {
        // 生成类名，方法名
        $class = '';
        if (!$this->isSingleCommand) {
            if (!isset($this->commands[$command])) {
                $script = Argument::script();
                throw new NotFoundException("'{$command}' is not command, see '{$script} --help'.");
            }
            $class = $this->commands[$command];
            if (is_array($class)) {
                $class = array_shift($class);
            }
        } else {
            $tmp   = $this->commands;
            $class = array_shift($tmp);
        }
        $method = 'main';
        // 命令行选项效验
        $this->validateOptions($command);
        // 协程执行
        if (isset($this->coroutine['enable'])) {
            $enable  = $this->coroutine['enable'];
            $options = $this->coroutine['options'] ?? [];
        } else {
            // 兼容老版本
            list($enable, $options) = $this->coroutine;
        }
        if ($enable) {
            // 环境效验
            if (!extension_loaded('swoole') || !class_exists(\Swoole\Coroutine\Scheduler::class)) {
                throw new \RuntimeException('Application has coroutine enabled, require swoole extension >= v4.4 to run. install: https://www.swoole.com/');
            }
            // 触发执行命令前置事件
            $event          = new CommandBeforeExecuteEvent();
            $event->command = $class;
            $this->dispatcher->dispatch($event);
            // 协程执行
            $scheduler = new \Swoole\Coroutine\Scheduler;
            $scheduler->set($options);
            $scheduler->add(function () use ($class, $method) {
                if (\Swoole\Coroutine::getCid() == -1) {
                    xgo([$this, 'callMethod'], $class, $method);
                } else {
                    try {
                        call_user_func([$this, 'callMethod'], $class, $method);
                    } catch (\Throwable $e) {
                        $this->error->handleException($e);
                    }
                }
            });
            $scheduler->start();
            return;
        }
        // 触发执行命令前置事件
        $event          = new CommandBeforeExecuteEvent();
        $event->command = $class;
        $this->dispatcher->dispatch($event);
        // 普通执行
        $this->callMethod($class, $method);
    }

    /**
     * 调用方法
     * @param $class
     * @param $method
     */
    public function callMethod($class, $method)
    {
        // 判断类是否存在
        if (!class_exists($class)) {
            throw new NotFoundException("'{$class}' class not found.");
        }
        // 实例化
        $instance = new $class();
        // 判断方法是否存在
        if (!method_exists($instance, $method)) {
            throw new NotFoundException("'{$class}::main' method not found.");
        }
        // 调用方法
        call_user_func([$instance, $method]);
    }

    /**
     * 命令行选项效验
     * @param string $command
     */
    protected function validateOptions(string $command)
    {
        $options = [];
        if (!$this->isSingleCommand) {
            $options = $this->commands[$command]['options'] ?? [];
        } else {
            $options = $this->commands['options'] ?? [];
        }
        $regflags = [];
        foreach ($options as $option) {
            $names = array_shift($option);
            if (is_string($names)) {
                $names = [$names];
            }
            foreach ($names as $name) {
                if (strlen($name) == 1) {
                    $regflags[] = "-{$name}";
                } else {
                    $regflags[] = "--{$name}";
                }
            }
        }
        foreach (array_keys(Flag::options()) as $flag) {
            if (!in_array($flag, $regflags)) {
                $script  = Argument::script();
                $command = Argument::command();
                $command = $command ? " {$command}" : $command;
                throw new NotFoundException("flag provided but not defined: '{$flag}', see '{$script}{$command} --help'.");
            }
        }
    }

}

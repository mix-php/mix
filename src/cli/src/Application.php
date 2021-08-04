<?php

namespace Mix\Cli;

use Mix\Cli\Argument\ArgumentVector;
use Mix\Cli\Flag\Flag;
use Mix\Cli\Exception\NotFoundException;

/**
 * Class Application
 * @package Mix\Cli
 */
class Application
{

    /**
     * @var string
     */
    public $name = 'app';

    /**
     * @var string
     */
    public $version = '0.0.0';

    /**
     * @var bool
     */
    public $debug = true;

    /**
     * @var string
     */
    public $basePath = '';

    /**
     * @var Command[]
     */
    protected $commands = [];

    /**
     * @var \Closure[]
     */
    protected $handlers = [];

    /**
     * @var bool
     */
    protected $singleton = false;

    /**
     * Application constructor.
     * @param string $name
     * @param string $version
     */
    public function __construct(string $name, string $version)
    {
        $this->name = $name;
        $this->version = $version;
        $this->basePath = dirname(realpath(ArgumentVector::script()));

        ArgumentVector::parse();
        Flag::parse();
    }

    /**
     * @param Command ...$commands
     * @return $this
     */
    public function addCommand(Command ...$commands): Application
    {
        array_push($this->commands, ...$commands);
        // init
        foreach ($this->commands as $command) {
            if ($command->singleton) {
                $this->singleton = true;
            }
        }
        if ($this->singleton) {
            ArgumentVector::parse(true);
            Flag::parse();
        }
        return $this;
    }

    /**
     * @param \Closure ...$handlerFunc
     */
    public function use(\Closure ...$handlerFunc)
    {
        array_push($this->handlers, ...$handlerFunc);
    }

    /**
     * run
     */
    public function run(): void
    {
        if (PHP_SAPI != 'cli') {
            throw new \RuntimeException('Please run in cli mode.');
        }

        if (count($this->commands) == 0) {
            throw new \RuntimeException('Command cannot be empty');
        }

        if (ArgumentVector::command() == '') {
            if (Flag::match('h', 'help')->bool()) {
                $this->globalHelp();
                return;
            }
            if (Flag::match('v', 'version')->bool()) {
                $this->version();
                return;
            }
            $options = Flag::options();
            if (empty($options)) {
                $this->globalHelp();
                return;
            } elseif ($this->singleton) {
                // 单命令执行
                $this->call();
                return;
            }
            $keys = array_keys($options);
            $flag = array_shift($keys);
            $script = ArgumentVector::script();
            throw new NotFoundException("flag provided but not defined: '{$flag}', see '{$script} --help'."); // 这里只是全局flag效验
        }
        if (ArgumentVector::command() !== '' && Flag::match('help')->bool()) {
            $this->commandHelp();
            return;
        }
        // 非单命令执行
        $this->call();
    }

    protected function globalHelp(): void
    {
        $script = ArgumentVector::script();
        static::println("Usage: {$script}" . ($this->singleton ? '' : ' [OPTIONS] COMMAND') . " [opt...]");
        $this->printGlobalOptions();
        if ($this->singleton) {
            $this->printCommandOptions();
        } else {
            $this->printCommands();
        }
        static::println('');
        static::println("Run '{$script}" . ($this->singleton ? '' : ' COMMAND') . " --help' for more information on a command.");
        static::println('');
        static::println("Developed with Mix PHP framework. (openmix.org/mix-php)");
    }

    protected function commandHelp(): void
    {
        $script = ArgumentVector::script();
        $command = ArgumentVector::command();
        $cmd = $this->getCommand($command);
        if (!$cmd) {
            return;
        }
        if ($cmd->long) {
            static::println($cmd->long);
            static::println('');
        }
        if ($cmd->usageFormat) {
            static::println(sprintf($cmd->usageFormat, $script, $command));
        } else {
            static::println(sprintf('Usage: %s %s [ARG...]', $script, $command));
        }
        $this->printCommandOptions();
        static::println('');
        static::println("Developed with Mix PHP framework. (openmix.org/mix-php)");
    }

    protected function version(): void
    {
        $appName = $this->name;
        $appVersion = $this->version;
        static::println("{$appName} version {$appVersion}");
    }

    protected function printGlobalOptions(): void
    {
        $tabs = "\t";
        static::println('');
        static::println('Global Options:');
        static::println("  -h, --help{$tabs}Print usage");
        static::println("  -v, --version{$tabs}Print version information");
    }

    protected function printCommands(): void
    {
        static::println('');
        static::println('Commands:');
        foreach ($this->commands as $command) {
            $name = $command->name;
            $short = $command->short;
            static::println("  {$name}\t{$short}");
        }
    }

    protected function printCommandOptions(): void
    {
        $cmd = $this->getCommand(ArgumentVector::command());
        if (!$cmd) {
            return;
        }
        $options = $cmd->options;
        if (empty($options)) {
            return;
        }
        static::println('');
        static::println('Command Options:');
        foreach ($options as $option) {
            $flags = [];
            foreach ($option->names as $name) {
                if (strlen($name) == 1) {
                    $flags[] = "-{$name}";
                } else {
                    $flags[] = "--{$name}";
                }
            }
            $flag = implode(', ', $flags);
            $usage = $option->usage;
            static::println("  {$flag}\t{$usage}");
        }
    }

    /**
     * @param string $command
     * @return Command|null
     */
    protected function getCommand(string $command): ?Command
    {
        $cmd = null;
        if (!$this->singleton) {
            foreach ($this->commands as $c) {
                if ($c->singleton) {
                    $cmd = $c;
                    break;
                }
            }
        } else {
            foreach ($this->commands as $c) {
                if ($c->name == $command) {
                    $cmd = $c;
                    break;
                }
            }
        }
        return $cmd;
    }

    protected function validateOptions(): void
    {
        $command = ArgumentVector::command();
        $cmd = $this->getCommand($command);
        if (!$cmd) {
            return;
        }
        $options = $cmd->options;
        $flags = [];
        foreach ($options as $option) {
            foreach ($option->names as $name) {
                if (strlen($name) == 1) {
                    $flags[] = "-{$name}";
                } else {
                    $flags[] = "--{$name}";
                }
            }
        }
        foreach (array_keys(Flag::options()) as $flag) {
            if (!in_array($flag, $flags)) {
                $script = ArgumentVector::script();
                $command = ArgumentVector::command();
                $command = $command ? " {$command}" : $command;
                throw new NotFoundException("flag provided but not defined: '{$flag}', see '{$script}{$command} --help'.");
            }
        }
    }

    protected function call(): void
    {
        $this->validateOptions();

        $command = ArgumentVector::command();
        $cmd = $this->getCommand($command);
        if (!$cmd) {
            return;
        }
        $run = $cmd->run;
        if (empty($run)) {
            throw new \RuntimeException(sprintf("'%s' command 'run' field is empty", $command));
        }

        $exec = function () use ($run) {
            if ($run instanceof \Closure) {
                $run();
                return;
            }
            if ($run instanceof RunInterface) {
                $run->main();
            }
        };
        if (count($this->handlers) > 0) {
            $tmp = array_merge($this->handlers, $cmd->handlers);
            $tmp = array_reverse($tmp);
            $next = null;
            foreach ($tmp as $k => $f) {
                if ($k == 0) {
                    $n = $exec;
                    $c = $f;
                    $next = function () use ($n, $c) {
                        $c($n);
                    };
                    if (count($tmp) == 1) {
                        $c($n);
                    }
                } elseif (count($tmp) - 1 == $k) {
                    $f($next);
                } else {
                    $n = $next;
                    $c = $f;
                    $next = function () use ($n, $c) {
                        $c($n);
                    };
                }
            }
        } else {
            $exec();
        }
    }

    /**
     * @param $string
     */
    protected static function println($string)
    {
        printf("%s\n", $string);
    }

}

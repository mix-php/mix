<?php

namespace Mix\Cli;

use Mix\Cli\Argument\Arguments;
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
        $this->basePath = dirname(Arguments::script());

        Arguments::parse();
        Flag::parse();
    }

    /**
     * @return $this
     */
    public function addCommand(Command $command): Application
    {
        $this->commands[] = $command;
        // init
        foreach ($this->commands as $command) {
            if ($command->singleton) {
                $this->singleton = true;
            }
        }
        if ($this->singleton) {
            Arguments::parse(true);
            Flag::parse();
        }
        return $this;
    }

    public function run(): void
    {
        if (PHP_SAPI != 'cli') {
            throw new \RuntimeException('Please run in cli mode.');
        }

        if (count($this->commands) == 0) {
            throw new \RuntimeException('Command cannot be empty');
        }

        if (Arguments::command() == '') {
            if (Flag::bool(['h', 'help'], false)) {
                $this->globalHelp();
                return;
            }
            if (Flag::bool(['v', 'version'], false)) {
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
            $script = Arguments::script();
            throw new NotFoundException("flag provided but not defined: '{$flag}', see '{$script} --help'."); // 这里只是全局flag效验
        }
        if (Arguments::command() !== '' && Flag::bool('help', false)) {
            $this->commandHelp();
            return;
        }
        // 非单命令执行
        $this->call();
    }

    protected function globalHelp(): void
    {
        $script = Arguments::script();
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
        $script = Arguments::script();
        $command = Arguments::command();
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
        $cmd = $this->getCommand(Arguments::command());
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
        $command = Arguments::command();
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
                $script = Arguments::script();
                $command = Arguments::command();
                $command = $command ? " {$command}" : $command;
                throw new NotFoundException("flag provided but not defined: '{$flag}', see '{$script}{$command} --help'.");
            }
        }
    }

    protected function call(): void
    {
        $this->validateOptions();

        $command = Arguments::command();
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
        $exec();
    }

    /**
     * @param $string
     */
    protected static function println($string)
    {
        printf("%s\n", $string);
    }

}

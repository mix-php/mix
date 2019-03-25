<?php

namespace Console\Commands;

use Mix\Console\CommandLine\Flag;

/**
 * Class HelloCommand
 * @package Console\Commands
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class HelloCommand
{

    /**
     * 主函数
     */
    public function main()
    {
        $name = Flag::string(['name'], 'World');
        println("Hello, {$name}!");
    }

}

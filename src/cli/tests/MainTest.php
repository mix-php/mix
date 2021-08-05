<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function testPrint(): void
    {
        $GLOBALS['argv'] = [$GLOBALS['argv'][0], 'hello'];
        $app = new \Mix\Cli\Application('app', '0.0.0-alpha');
        $cmd = new \Mix\Cli\Command([
            'name' => 'hello',
            'short' => 'Echo demo',
            'run' => function () {
                $this->assertTrue(true);
            }
        ]);
        $opt = new Mix\Cli\Option([
            'names' => ['n', 'name'],
            'usage' => 'Your name'
        ]);
        $cmd->addOption($opt);
        $app->addCommand($cmd);
        $app->run();

        echo PHP_EOL . "---------------------------------" . PHP_EOL;
        $GLOBALS['argv'] = [$GLOBALS['argv'][0]];
        $app = new \Mix\Cli\Application('test', '1.0.0');
        $cmd = new \Mix\Cli\Command([
            'name' => 'foo',
            'short' => 'bar',
            'run' => function () {
                $this->assertTrue(true);
            }
        ]);
        $app->addCommand($cmd);
        $app->run();

        echo "---------------------------------" . PHP_EOL;
        $GLOBALS['argv'] = [$GLOBALS['argv'][0], 'xxx'];
        $app = new \Mix\Cli\Application('test', '1.0.0');
        $cmd = new \Mix\Cli\Command([
            'name' => 'foo',
            'short' => 'bar',
            'run' => function () {
            }
        ]);
        $app->addCommand($cmd);
        $app->run();

        echo "---------------------------------" . PHP_EOL;
        $GLOBALS['argv'] = [$GLOBALS['argv'][0], 'foo', '--help'];
        $app = new \Mix\Cli\Application('test', '1.0.0');
        $cmd = new \Mix\Cli\Command([
            'name' => 'foo',
            'short' => 'bar',
            'run' => function () {
            }
        ]);
        $cmd->addOption(new \Mix\Cli\Option([
            'names' => ['a', 'bc'],
            'usage' => 'abc'
        ]));
        $app->addCommand($cmd);
        $app->run();
    }

    public function testFlag(): void
    {
        $GLOBALS['argv'] = [$GLOBALS['argv'][0], "foo", "-a=a1", "-b", "--cd", "--ab=ab1", "--de", "de1", "-c", "c1", "--sw", "false"];
        Mix\Cli\Argv::parse();
        Mix\Cli\Flag::parse();
        $this->assertEquals(Mix\Cli\Flag::options(), [
            '-a' => 'a1',
            '-b' => '',
            '--cd' => '',
            '--ab' => 'ab1',
            '--de' => 'de1',
            '-c' => 'c1',
            '--sw' => 'false',
        ]);
    }

}

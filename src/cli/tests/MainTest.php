<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function test(): void
    {
        $GLOBALS['argv'] = [$GLOBALS['argv'][0], 'foo'];
        $app = new \Mix\Cli\Application('test', '1.0.0');
        $cmd = new \Mix\Cli\Command('foo', 'bar', function () {
            $this->assertTrue(true);
        });
        $app->addCommand($cmd);
        $app->run();

        echo PHP_EOL . "---------------------------------" . PHP_EOL;
        $GLOBALS['argv'] = [$GLOBALS['argv'][0]];
        $app = new \Mix\Cli\Application('test', '1.0.0');
        $cmd = new \Mix\Cli\Command('foo', 'bar', function () {
            $this->assertTrue(true);
        });
        $app->addCommand($cmd);
        $app->run();

        echo "---------------------------------" . PHP_EOL;
        $GLOBALS['argv'] = [$GLOBALS['argv'][0], 'xxx'];
        $app = new \Mix\Cli\Application('test', '1.0.0');
        $cmd = new \Mix\Cli\Command('foo', 'bar', function () {
        });
        $app->addCommand($cmd);
        $app->run();

        echo "---------------------------------" . PHP_EOL;
        $GLOBALS['argv'] = [$GLOBALS['argv'][0], 'foo', '--help'];
        $app = new \Mix\Cli\Application('test', '1.0.0');
        $cmd = new \Mix\Cli\Command('foo', 'bar', function () {
        });
        $cmd->addOption(new \Mix\Cli\Option(['a', 'bc'], 'abc'));
        $app->addCommand($cmd);
        $app->run();
    }

}

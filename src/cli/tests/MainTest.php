<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function test(): void
    {
        $GLOBALS['argv'] = [$GLOBALS['argv'][0], 'foo'];

        $app = new \Mix\Cli\Application('test', '1.0.0');
        $cmds[] = new \Mix\Cli\Command('foo', 'bar', function () {
            $this->assertTrue(true);
        });
        $app->addCommand(...$cmds);
        $app->run();
    }

}

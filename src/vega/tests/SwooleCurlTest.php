<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SwooleCurlTest extends TestCase
{

    public function test(): void
    {
        exec('curl http://0.0.0.0:9501/hello');
        exec('curl http://0.0.0.0:9501/foo/hello');
        exec('curl http://0.0.0.0:9501/foo/hello1');
        exec("ps -ef | grep \"tests/bootstrap.php tests/SwooleServerTest.php\" | grep -v grep | awk '{print $2}' | xargs kill");
    }

}

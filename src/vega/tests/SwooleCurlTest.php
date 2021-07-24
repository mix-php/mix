<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SwooleCurlTest extends TestCase
{

    public function test(): void
    {
        exec('curl http://0.0.0.0:9501/hello');
        exec('curl -d \'user=abc&password=123\' -X POST http://0.0.0.0:9501/hello');
        exec('curl http://0.0.0.0:9501/hello1');
        exec('curl http://0.0.0.0:9501/foo/hello');
        exec('curl http://0.0.0.0:9501/foo/hello1');
        exec('curl http://0.0.0.0:9501/users/1000?name=keda');
        exec('curl -H "Content-Type: application/json" -X POST -d \'{"user_id": "123", "coin":100}\' "http://0.0.0.0:9501/users"');
        exec("ps -ef | grep \"tests/bootstrap.php tests/SwooleServerTest.php\" | grep -v grep | awk '{print $2}' | xargs kill");
    }

}

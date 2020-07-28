<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function testClient(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $server = new \Mix\SyncInvoke\Server(9505, true);
            go(function () use ($server) {
                $server->start();
            });

            $dialer = new \Mix\SyncInvoke\Client\Dialer();
            $client = $dialer->dial(9505);
            $data   = $client->invoke(function () {
                $obj = new Hello();
                return [1, 2, 3, $obj];
            });

            $_this->assertEquals(count($data), 4);
            $_this->assertEquals(get_class($data[3]), Hello::class);
            $server->shutdown();
        };
        run($func);
    }

}

class Hello
{

}

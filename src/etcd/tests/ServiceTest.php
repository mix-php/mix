<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ServiceTest extends TestCase
{

    public function testGet(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $center  = new \Mix\Etcd\ServiceCenter([
                'host'    => '127.0.0.1',
                'port'    => 2379,
                'version' => 'v3',
                'ttl'     => 10,
            ]);
            $service = $center->service('php.micro.srv.test');
            var_dump($service);
        };
        run($func);
    }

}

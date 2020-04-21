<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ServiceGetTest extends TestCase
{

    public function testGet(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $center = new \Mix\Micro\Etcd\Registry([
                'host'    => '127.0.0.1',
                'port'    => 2379,
                'version' => 'v3',
                'ttl'     => 10,
            ]);
            for ($i = 0; $i < 5; $i++) {
                $service = $center->service('php.micro.srv.test');
                var_dump($service);
                sleep(1);
            }
            $center->clear();
        };
        run($func);
    }

}

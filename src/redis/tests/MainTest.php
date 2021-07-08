<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function testGetSet(): void
    {
        $rds = redis();

        $rds->set('foo', 'bar');
        $result = $rds->get('foo');
        $this->assertEquals('bar', $result);

        $rds->del('foo');
        $result = $rds->get('foo');
        $this->assertEquals(false, $result);
    }

    public function testMulti(): void
    {
        $rds = redis();

        $tx = $rds->multi();
        $tx->set('foo2', "bar2");
        $tx->del('foo1');
        $tx->incr('foo1');
        $tx->incr('foo1');
        $result = $tx->exec();

        $value = $rds->get('foo1');
        $this->assertEquals(2, $value);

        $this->assertEquals([true, 1, 1, 2], $result);
    }

}

<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class WhereTest extends TestCase
{

    // ['or', ['foo', '=', 1]]
    public function testOr(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $db = db();
            $result = $db->table('users')
                ->where(['id', '=', 1])
                ->where(['or', ['id', '=', 2]])
                ->get();
            var_dump($db->getLastSql());
            $_this->assertTrue($db->getLastLog()['time'] > 0);
        };
        run($func);
    }

    // ['foo', '=', null]
    public function testNull(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $db = db();
            try {
                $result = $db->table('users')->where([
                    ['id', '=', null],
                ])->get();
            } catch (\Throwable $ex) {
                var_dump($ex->getMessage());
                $_this->assertContains('Invalid where format', $ex->getMessage());
            }
        };
        run($func);
    }

}




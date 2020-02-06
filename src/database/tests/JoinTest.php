<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JoinTest extends TestCase
{

    // ['or', ['foo', '=', 1]]
    public function testOr(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $db = db();
            $result = $db->table('users')
                ->join('news', [['users.id', '=', 'news.id'], ['or', ['users.id', '=', 'users.id']]])
                ->get();
            var_dump($db->getLastSql());
            $_this->assertTrue($db->getLastLog()['time'] > 0);
        };
        run($func);
    }

}

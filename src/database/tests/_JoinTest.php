<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JoinTest extends TestCase
{

    public function testMulti(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $conn = conn();
            $result = $conn->table('users')
                ->join('news as n1', ['users.id', '=', 'n1.id'])
                ->join('news as n2', ['users.id', '=', 'n2.id'])
                ->get();
            $sql    = $conn->getLastSql();
            $_this->assertContains('SELECT * FROM users INNER JOIN news as n1 ON users.id = n1.id INNER JOIN news as n2 ON users.id = n2.id', $sql);
        };
        run($func);
    }

    public function testAnd(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $conn = conn();
            $result = $conn->table('users')
                ->join('news', [['users.id', '=', 'news.id'], ['users.id', '=', 'users.id']])
                ->get();
            $sql    = $conn->getLastSql();
            $_this->assertContains('SELECT * FROM users INNER JOIN news ON users.id = news.id AND users.id = users.id', $sql);
        };
        run($func);
    }

    // ['or', ['foo', '=', 1]]
    public function testOr(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $conn = conn();
            $result = $conn->table('users')
                ->join('news', [['users.id', '=', 'news.id'], ['or', ['users.id', '=', 'users.id']]])
                ->get();
            $sql    = $conn->getLastSql();
            $_this->assertContains('SELECT * FROM users INNER JOIN news ON users.id = news.id OR users.id = users.id', $sql);
        };
        run($func);
    }

}

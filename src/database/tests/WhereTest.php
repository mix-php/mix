<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Mix\Database\ConnectionInterface;

final class WhereTest extends TestCase
{

    public function testAnd(): void
    {
        $db = db();
        $_this = $this;

        $db->table('users')
            ->where('id = ?', 1)
            ->where('name = ?', 'test1')
            ->debug(function (ConnectionInterface $conn) use ($_this) {
                $log = $conn->queryLog();
                $sql = "SELECT * FROM users WHERE id = ? AND name = ?";
                $_this->assertEquals($log['sql'], $sql);
                $_this->assertEquals($log['bindings'], [1, 'test1']);
            })
            ->get();

        $db->table('users')
            ->where('id = ? and name = ?', 1, 'test1')
            ->debug(function (ConnectionInterface $conn) use ($_this) {
                $log = $conn->queryLog();
                $sql = "SELECT * FROM users WHERE id = ? and name = ?";
                $_this->assertEquals($log['sql'], $sql);
                $_this->assertEquals($log['bindings'], [1, 'test1']);
            })
            ->get();
    }

    public function testOr(): void
    {
        $db = db();
        $_this = $this;

        $db->table('users')
            ->where('id = ? or id = ?', 1, 2)
            ->debug(function (ConnectionInterface $conn) use ($_this) {
                $log = $conn->queryLog();
                $sql = "SELECT * FROM users WHERE id = ? or id = ?";
                $_this->assertEquals($log['sql'], $sql);
                $_this->assertEquals($log['bindings'], [1, 2]);
            })
            ->get();
    }

    public function testIn(): void
    {
        $db = db();
        $_this = $this;

        $db->table('users')
            ->where('id IN (?) or id IN (?)', [1, 2], [3, 4])
            ->debug(function (ConnectionInterface $conn) use ($_this) {
                $log = $conn->queryLog();
                $sql = "SELECT * FROM users WHERE id IN (1,2) or id IN (3,4)";
                $_this->assertEquals($log['sql'], $sql);
                $_this->assertEquals($log['bindings'], []);
            })
            ->get();
    }

}

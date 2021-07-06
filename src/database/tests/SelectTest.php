<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Mix\Database\ConnectionInterface;

final class SelectTest extends TestCase
{

    public function testOrder(): void
    {
        $db = db();
        $_this = $this;

        $db->debug(function (ConnectionInterface $conn) use ($_this) {
            $log = $conn->queryLog();
            $_this->assertEquals('SELECT * FROM users ORDER BY id DESC', $log['sql']);
        })->table('users')->order('id', 'desc')->get();

        $db->debug(function (ConnectionInterface $conn) use ($_this) {
            $log = $conn->queryLog();
            $_this->assertEquals('SELECT * FROM users ORDER BY id DESC, name ASC', $log['sql']);
        })->table('users')->order('id', 'desc')->order('name', 'asc')->get();
    }

    public function testLimit(): void
    {
        $db = db();
        $_this = $this;

        $db->debug(function (ConnectionInterface $conn) use ($_this) {
            $log = $conn->queryLog();
            $_this->assertEquals('SELECT * FROM users LIMIT ?, ?', $log['sql']);
            $_this->assertEquals([0, 5], $log['bindings']);
        })->table('users')->limit(5)->get();

        $db->debug(function (ConnectionInterface $conn) use ($_this) {
            $log = $conn->queryLog();
            $_this->assertEquals('SELECT * FROM users LIMIT ?, ?', $log['sql']);
            $_this->assertEquals([10, 5], $log['bindings']);
        })->table('users')->offset(10)->limit(5)->get();
    }

    public function testGroupHaving(): void
    {
        $db = db();
        $_this = $this;

        $db->debug(function (ConnectionInterface $conn) use ($_this) {
            $log = $conn->queryLog();
            $_this->assertEquals('SELECT uid, COUNT(*) AS total FROM news GROUP BY uid HAVING COUNT(*) > ?', $log['sql']);
            $_this->assertEquals([0], $log['bindings']);
        })->table('news')->select('uid, COUNT(*) AS total')->group('uid')->having('COUNT(*) > ?', 0)->get();

        $db->debug(function (ConnectionInterface $conn) use ($_this) {
            $log = $conn->queryLog();
            $_this->assertEquals('SELECT uid, COUNT(*) AS total FROM news GROUP BY uid HAVING COUNT(*) > ? AND COUNT(*) < ?', $log['sql']);
            $_this->assertEquals([0, 10], $log['bindings']);
        })->table('news')->select('uid, COUNT(*) AS total')->group('uid')->having('COUNT(*) > ? AND COUNT(*) < ?', 0, 10)->get();
    }

    public function testJoin(): void
    {
        $db = db();
        $_this = $this;

        $db->debug(function (ConnectionInterface $conn) use ($_this) {
            $log = $conn->queryLog();
            $_this->assertEquals('SELECT n.*, u.name FROM news AS n LEFT JOIN users AS u ON n.uid = u.id AND u.balance > ?', $log['sql']);
            $_this->assertEquals([0], $log['bindings']);
        })->table('news AS n')->select('n.*, u.name')->leftJoin('users AS u', 'n.uid = u.id AND u.balance > ?', 0)->get();
    }

}

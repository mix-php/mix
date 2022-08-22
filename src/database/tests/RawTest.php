<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RawTest extends TestCase
{
    public function test(): void
    {
        $db = db();
        $_this = $this;

        $rowsAffected = $db->exec('DELETE FROM users WHERE id = ?', 100000)->rowCount();
        $this->assertEquals(0, $rowsAffected);

        $res = $db->debug(function (\Mix\Database\ConnectionInterface $conn) use ($_this) {
            $log = $conn->queryLog();
            $sql = "SELECT * FROM users WHERE id = ?";
            $_this->assertEquals($log['sql'], $sql);
            $_this->assertEquals($log['bindings'], [1]);
        })->raw('SELECT * FROM users WHERE id = ?', 1)->get();
        $obj = array_pop($res);
        $this->assertIsObject($obj);
        $this->assertObjectHasAttribute('id', $obj);
        $this->assertObjectHasAttribute('name', $obj);
        $this->assertObjectHasAttribute('balance', $obj);
        $this->assertObjectHasAttribute('add_time', $obj);

        $obj = $db->debug(function (\Mix\Database\ConnectionInterface $conn) use ($_this) {
            $log = $conn->queryLog();
            $sql = "SELECT * FROM users WHERE id = ?";
            $_this->assertEquals($log['sql'], $sql);
            $_this->assertEquals($log['bindings'], [1]);
        })->raw('SELECT * FROM users WHERE id = ?', 1)->first();
        $this->assertIsObject($obj);
        $this->assertObjectHasAttribute('id', $obj);
        $this->assertObjectHasAttribute('name', $obj);
        $this->assertObjectHasAttribute('balance', $obj);
        $this->assertObjectHasAttribute('add_time', $obj);
    }
}

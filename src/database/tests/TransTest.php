<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TransTest extends TestCase
{

    // 事务多次执行 bind params 残留的问题
    public function testBindParams(): void
    {
        $db = db();

        // PDOException: SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens
        $tx = $db->beginTransaction();
        try {
            $data = [
                'name' => 'foo8',
                'balance' => 8,
            ];
            $tx->insert('users', $data);
            $data = [
                'name' => 'foo9',
                'add_time' => '2021-09-27 00:00:00',
            ];
            $tx->insert('users', $data);
            $tx->commit();
            $this->assertTrue(true);
        } catch (\Throwable $ex) {
            $tx->rollback();
            $this->assertContains("Field 'balance' doesn't have a default value", $ex->getMessage());
        }
    }

    // 事务异常
    public function testRollback(): void
    {
        $db = db();

        $tx = $db->beginTransaction();
        try {
            $data = [
                'name' => 'foo8',
                'balance' => 8,
            ];
            $tx->insert('users', $data);
            throw new \RuntimeException('ERROR');
            $tx->commit();
        } catch (\Throwable $ex) {
            $tx->rollback();
            $this->assertTrue(true);
        }
    }

    // 自动事务
    public function testAuto(): void
    {
        $_this = $this;
        $db = db();

        $db->transaction(function (Mix\Database\Transaction $tx) use ($_this) {
            $data = [
                'name' => 'foo9',
                'balance' => 9,
            ];
            $tx->insert('users', $data);
            $_this->assertTrue(true);
        });
    }

}

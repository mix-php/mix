<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TransTest extends TestCase
{
    // 没有pool，因为复用连接，lastInsertId, rowCount 值为第一个查询的值
    public function testLastInsertIdRowCount(): void
    {
        $db = db();

        // 没有pool + 非事务
        $data = [
            'name' => 'foo8',
            'balance' => 8,
        ];
        $id1 = $db->insert('users', $data)->lastInsertId();
        $id2 = $db->insert('users', $data)->lastInsertId();
        $this->assertNotEquals($id1, $id2);

        $count1 = $db->table('users')->where('id = ?', $id1)->update('balance', new Mix\Database\Expr('balance + ?', 1))->rowCount();
        $count2 = $db->table('users')->update('balance', new Mix\Database\Expr('balance + ?', 1))->rowCount();
        $this->assertNotEquals($count1, $count2);

        // 没有pool + 事务
        $tx = $db->beginTransaction();
        $data = [
            'name' => 'foo8',
            'balance' => 8,
        ];
        $id1 = $tx->insert('users', $data)->lastInsertId();
        $id2 = $tx->insert('users', $data)->lastInsertId();
        $this->assertNotEquals($id1, $id2);

        $count1 = $tx->table('users')->where('id = ?', $id1)->update('balance', new Mix\Database\Expr('balance + ?', 1))->rowCount();
        $count2 = $tx->table('users')->update('balance', new Mix\Database\Expr('balance + ?', 1))->rowCount();
        $this->assertNotEquals($count1, $count2);
    }

    // 有pool，因为复用连接，lastInsertId, rowCount 值为第一个查询的值
    public function testPoolLastInsertIdRowCount(): void
    {
        // 有pool + 非事务
        swoole_co_run(function () {
            $pool = pool();

            $data = [
                'name' => 'foo8',
                'balance' => 8,
            ];
            $id1 = $pool->insert('users', $data)->lastInsertId();
            $id2 = $pool->insert('users', $data)->lastInsertId();
            $this->assertNotEquals($id1, $id2);

            $count1 = $pool->table('users')->where('id = ?', $id1)->update('balance', new Mix\Database\Expr('balance + ?', 1))->rowCount();
            $count2 = $pool->table('users')->update('balance', new Mix\Database\Expr('balance + ?', 1))->rowCount();
            $this->assertNotEquals($count1, $count2);
        });

        // 有pool + 事务
        swoole_co_run(function () {
            $pool = pool();

            $tx = $pool->beginTransaction();
            $data = [
                'name' => 'foo8',
                'balance' => 8,
            ];
            $id1 = $tx->insert('users', $data)->lastInsertId();
            $id2 = $tx->insert('users', $data)->lastInsertId();
            $this->assertNotEquals($id1, $id2);

            $count1 = $tx->table('users')->where('id = ?', $id1)->update('balance', new Mix\Database\Expr('balance + ?', 1))->rowCount();
            $count2 = $tx->table('users')->update('balance', new Mix\Database\Expr('balance + ?', 1))->rowCount();
            $this->assertNotEquals($count1, $count2);
        });
    }

    // 事务内，因为复用连接，上一个查询的 bind params 残留到下一个查询中执行的问题
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

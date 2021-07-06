<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UpdateTest extends TestCase
{

    public function test(): void
    {
        $db = db();

        $db->table('users')->where('id = ?', 1)->update('name', 'foo1')->rowCount();

        $data = [
            'name' => 'foo2',
            'balance' => 100,
        ];
        $db->table('users')->where('id = ?', 1)->updates($data);

        $data = [
            'balance' => new Mix\Database\Expr('balance + ?', 1),
        ];
        $db->table('users')->where('id = ?', 1)->updates($data);

        $db->table('users')->where('id = ?', 1)->update('balance', new Mix\Database\Expr('balance + ?', 1));

        $rowsAffected = $db->table('users')->where('id = ?', 1)->update('add_time', new Mix\Database\Expr('CURRENT_TIMESTAMP()'))->rowCount();
        $this->assertEquals(1, $rowsAffected);

        $data = [
            'add_time' => new Mix\Database\Expr('CURRENT_TIMESTAMP()'),
        ];
        $rowsAffected = $db->table('users')->where('id = ?', 1)->updates($data)->rowCount();
        $this->assertEquals(0, $rowsAffected);
    }

}

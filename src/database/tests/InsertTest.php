<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InsertTest extends TestCase
{

    public function testInsert(): void
    {
        $db = db();

        $id = $db->insert('users', [
            'name' => 'foo1',
            'balance' => 1,
        ])->lastInsertId();
        $this->assertGreaterThan(0, (int)$id);

        $data = [
            [
                'name' => 'foo2',
                'balance' => 2,
            ],
            [
                'name' => 'foo3',
                'balance' => 3,
            ]
        ];
        $id = $db->batchInsert('users', $data)->lastInsertId();
        $this->assertGreaterThan(0, (int)$id);

        $data = [
            'name' => 'foo',
            'balance' => 0,
            'add_time' => new Mix\Database\Expr('CURRENT_TIMESTAMP()'),
        ];
        $db->insert('users', $data);
    }

}

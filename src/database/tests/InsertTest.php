<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InsertTest extends TestCase
{
    public function test(): void
    {
        $db = db();

        $id = $db->insert('users', [
            'name' => 'foo1',
            'balance' => 1,
        ])->lastInsertId();
        $this->assertGreaterThan(0, (int)$id);

        try {
            $db->insert('users_11111', [
                'name' => 'foo1',
                'balance' => 1,
            ])->lastInsertId();
        } catch (\Throwable $ex) {
            $this->assertContains('Base table or view not found', $ex->getMessage());
        }

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
            'name' => 'foo4',
            'balance' => 4,
            'add_time' => new Mix\Database\Expr('CURRENT_TIMESTAMP()'),
        ];
        $db->insert('users', $data);
    }
}

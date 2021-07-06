<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TransTest extends TestCase
{

    public function test(): void
    {
        $_this = $this;
        $db = db();

        $tx = $db->beginTransaction();
        try {
            $data = [
                'name' => 'foo8',
                'balance' => 8,
            ];
            $tx->insert('users', $data);
            $tx->commit();
            $this->assertTrue(true);
        } catch (\Throwable $ex) {
            $tx->rollback();
            throw $ex;
        }

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

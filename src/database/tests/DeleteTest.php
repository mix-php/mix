<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DeleteTest extends TestCase
{

    public function test(): void
    {
        $db = db();

        $rowsAffected = $db->table('users')->where('id = ?', 100000)->delete()->rowCount();
        $this->assertEquals(0, $rowsAffected);
    }

}

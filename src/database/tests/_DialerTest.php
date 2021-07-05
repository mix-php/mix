<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DialerTest extends TestCase
{

    public function test(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $db     = (new \Mix\Database\Dialer())->dial(DATABASE_DSN, DATABASE_USERNAME, DATABASE_PASSWORD);
            $conn   = $db->raw('select * from `users` limit 1');
            $result = $conn->queryAll();
            $sql    = $conn->getLastSql();
            $_this->assertEquals(count($result), 1);
        };
        run($func);
    }

}

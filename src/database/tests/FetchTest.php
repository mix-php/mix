<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FetchTest extends TestCase
{

    public function testValue(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $conn   = conn();
            $result = $conn->table('users')->value('text');
            $_this->assertContains($result, 'test1');
        };
        run($func);
    }

    public function testValueEx(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $conn = conn();
            try {
                $result = $conn->table('users')->value('text123');
            } catch (\Throwable $exception) {
                $_this->assertEquals(get_class($exception), \PDOException::class);
            }
        };
        run($func);
    }

}

<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BindTest extends TestCase
{

    public function testBindValues(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $conn   = conn();
            $result = $conn->prepare('select * from users where id = ? or id = ?')->bindValues([1, 2])->queryAll();
            $_this->assertTrue(!empty($result));
        };
        run($func);
    }

}

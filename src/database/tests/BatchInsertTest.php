<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BatchInsertTest extends TestCase
{

    // 主要测试 $data 内得数据字段顺序不一
    public function test(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $conn   = conn();
            $result = $conn->batchInsert('users', [
                [
                    'text' => 'test3',
                    'num'  => 3,
                ],
                [
                    'num'  => 4,
                    'text' => 'test4',
                ],
            ])->execute();
            $sql    = $conn->getLastSql();
            $_this->assertContains("('test3', 3), ('test4', 4)", $sql);
        };
        run($func);
    }

}

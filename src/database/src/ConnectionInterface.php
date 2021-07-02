<?php

namespace Mix\Database;

/**
 * Interface ConnectionInterface
 * @package Mix\Database
 */
interface ConnectionInterface
{

    public function inTransaction(): bool;

    public function query(): \PDOStatement;

    /**
     * 返回一行
     * @param int $fetchStyle
     * @return array|object
     */
    public function queryOne(int $fetchStyle = null);

    public function queryAll(int $fetchStyle = null): array;

    public function queryColumn(int $columnNumber = 0): array;

    /**
     * 返回一个标量值
     * @return mixed
     */
    public function queryScalar();

    public function getLastInsertId(): string;

    public function getRowCount(): int;

    public function getLastSql(): string;

    public function getLastLog(): array;

}

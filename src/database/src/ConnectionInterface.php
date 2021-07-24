<?php

namespace Mix\Database;

/**
 * Interface ConnectionInterface
 * @package Mix\Database
 */
interface ConnectionInterface
{

    public function debug(\Closure $func): ConnectionInterface;

    public function raw(string $sql, ...$values): ConnectionInterface;

    public function exec(string $sql, ...$values): ConnectionInterface;

    public function table(string $table): ConnectionInterface;

    public function select(string ...$fields): ConnectionInterface;

    public function join(string $table, string $on, ...$values): ConnectionInterface;

    public function leftJoin(string $table, string $on, ...$values): ConnectionInterface;

    public function rightJoin(string $table, string $on, ...$values): ConnectionInterface;

    public function fullJoin(string $table, string $on, ...$values): ConnectionInterface;

    public function where(string $expr, ...$values): ConnectionInterface;

    public function or(string $expr, ...$values): ConnectionInterface;

    public function order(string $field, string $order): ConnectionInterface;

    public function group(string ...$fields): ConnectionInterface;

    public function having(string $expr, ...$values): ConnectionInterface;

    public function offset(int $length): ConnectionInterface;

    public function limit(int $length): ConnectionInterface;

    public function lockForUpdate(): ConnectionInterface;

    public function sharedLock(): ConnectionInterface;

    /**
     * 返回多行
     * @return array
     */
    public function get(): array;

    /**
     * 返回一行
     * @return array|object|false
     */
    public function first();

    /**
     * 返回单个值
     * @param string $field
     * @return mixed
     * @throws \PDOException
     */
    public function value(string $field);

    public function updates(array $data): ConnectionInterface;

    public function update(string $field, $value): ConnectionInterface;

    public function delete(): ConnectionInterface;

    /**
     * 自动事务
     * @param \Closure $closure
     * @throws \Throwable
     */
    public function transaction(\Closure $closure);

    public function beginTransaction(): Transaction;

    public function statement(): \PDOStatement;

    public function lastInsertId(): string;

    public function rowCount(): int;

    public function queryLog(): array;

}

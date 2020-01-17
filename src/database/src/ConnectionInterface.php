<?php

namespace Mix\Database;

use Mix\Database\Query\Expression;

/**
 * Interface ConnectionInterface
 * @package Mix\Database
 * @author liu,jian <coder.keda@gmail.com>
 */
interface ConnectionInterface
{

    /**
     * 连接
     * @return bool
     */
    public function connect();

    /**
     * 关闭连接
     * @return bool
     */
    public function close();

    /**
     * 准备执行语句
     * @param $sql
     * @return $this
     */
    public function prepare($sql);

    /**
     * 绑定参数
     * @param array $data
     * @return $this
     */
    public function bindParams(array $data);

    /**
     * 返回结果集
     * @return \PDOStatement
     */
    public function query();

    /**
     * 返回一行
     * @param int $fetchStyle
     * @return array|object
     */
    public function queryOne(int $fetchStyle = null);

    /**
     * 返回多行
     * @param int $fetchStyle
     * @return array
     */
    public function queryAll(int $fetchStyle = null);

    /**
     * 返回一列 (默认第一列)
     * @param int $columnNumber
     * @return array
     */
    public function queryColumn(int $columnNumber = 0);

    /**
     * 返回一个标量值
     * @return mixed
     */
    public function queryScalar();

    /**
     * 执行SQL语句
     * @return bool
     */
    public function execute();

    /**
     * 返回最后插入行的ID或序列值
     * @return string
     */
    public function getLastInsertId();

    /**
     * 返回受上一个 SQL 语句影响的行数
     * @return int
     */
    public function getRowCount();

    /**
     * 返回最后的SQL语句
     * @return string
     */
    public function getLastSql();

    /**
     * 获取最后的日志
     * @return array
     */
    public function getLastLog();

    /**
     * 插入
     * @param string $table
     * @param array $data
     * @return $this
     */
    public function insert(string $table, array $data);

    /**
     * 批量插入
     * @param string $table
     * @param array $data
     * @return $this
     */
    public function batchInsert(string $table, array $data);

    /**
     * 更新
     * @param string $table
     * @param array $data
     * @param array $where
     * @return $this
     */
    public function update(string $table, array $data, array $where);

    /**
     * 删除
     * @param string $table
     * @param array $where
     * @return $this
     */
    public function delete(string $table, array $where);

    /**
     * 自动事务
     * @param \Closure $closure
     * @throws \Throwable
     */
    public function transaction(\Closure $closure);

    /**
     * 开始事务
     * @return bool
     */
    public function beginTransaction();

    /**
     * 提交事务
     * @return bool
     */
    public function commit();

    /**
     * 回滚事务
     * @return bool
     */
    public function rollback();

    /**
     * 返回当前PDO连接是否在事务内（在事务内的连接回池会造成下次开启事务产生错误）
     * @return bool
     */
    public function inTransaction();

    /**
     * 返回一个RawQuery对象，对象的值将不经过参数绑定，直接解释为SQL的一部分，适合传递数据库原生函数
     * @param string $value
     * @return Expression
     */
    public static function raw(string $value);

    /**
     * 启动查询生成器
     * @param string $table
     * @return QueryBuilder
     */
    public function table(string $table);

    /**
     * 释放连接
     * @return bool
     */
    public function release();

    /**
     * 丢弃连接
     * @return bool
     */
    public function discard();

}

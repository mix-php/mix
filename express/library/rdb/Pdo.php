<?php

/**
 * Mysql类
 * @author 刘健 <code.liu@qq.com>
 */

namespace sys\rdb;

class Pdo
{

    // pdo
    private static $pdo;

    // 最后sql数据
    private static $lastSqlData;

    // 回滚含有零影响行数的事务
    private static $rollbackZeroAffected;

    // 连接
    public static function connect()
    {
        if (!isset(self::$pdo)) {
            $conf = Config::get('pdo');
            $params = [
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_ERRMODE          => \PDO::ERRMODE_EXCEPTION,
            ];
            self::$pdo = new \PDO(
                $conf['dsn'],
                $conf['username'],
                $conf['password'],
                $params += $conf['attribute']
            );
            self::$rollbackZeroAffected = $conf['transaction']['rollback_zero_affected'];
        }
    }

    // 执行一条 SQL 语句，并返回结果集
    public static function query($sql, $data = [])
    {
        self::connect();
        list($sql, $params, $values) = self::$lastSqlData = self::prepare($sql, $data);
        $statement                   = self::$pdo->prepare($sql);
        foreach ($params as $key => &$value) {
            $statement->bindParam($key, $value);
        }
        foreach ($values as $key => &$value) {
            $statement->bindValue($key + 1, $value);
        }
        $statement->execute();
        return new Statement($statement);
    }

    // 执行一条 SQL 语句，并返回受影响的行数
    public static function execute($sql, $data = [])
    {
        $statement    = self::query($sql, $data);
        $affectedRows = $statement->rowCount();
        $lastInsertId = self::$pdo->lastInsertId();
        if (self::$pdo->inTransaction() && self::$rollbackZeroAffected && $affectedRows == 0) {
            throw new \PDOException('事物内查询的影响行数为零');
        }
        return $lastInsertId ?: $affectedRows;
    }

    // 自动事务
    public static function transaction($func, $debug = true)
    {
        self::beginTransaction();
        try {
            $func();
            // 提交事务
            self::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            self::rollBack();
            // 调试
            if ($debug) {
                throw $e;
            }
            return false;
        }
    }

    // 开始事务
    public static function beginTransaction()
    {
        self::connect();
        self::$pdo->beginTransaction();
    }

    // 提交事务
    public static function commit()
    {
        self::$pdo->commit();
    }

    // 回滚事务
    public static function rollBack()
    {
        self::$pdo->rollBack();
    }

    // 返回最后执行的SQL语句
    public static function getLastSql()
    {
        if (isset(self::$lastSqlData)) {
            list($sql, $params, $values) = self::$lastSqlData;
            $params                      = self::quotes($params);
            $values                      = self::quotes($values);
            foreach ($params as $key => $value) {
                $sql = str_replace(':' . $key, $value, $sql);
            }
            $sql = vsprintf(str_replace('?', '%s', $sql), $values);
            return $sql;
        }
        return '';
    }

    // 给字符串加引号
    private static function quotes($var)
    {
        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::quotes($v);
            }
            return $var;
        }
        return is_numeric($var) ? $var : "'{$var}'";
    }

    // 预处理, 扩展数组参数的支持
    private static function prepare($sql, $data)
    {
        $params = $values = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $tmp = [];
                foreach ($value as $k => $v) {
                    $tmp[]    = '?';
                    $values[] = $v;
                }
                $sql = str_replace(':' . $key, implode(',', $tmp), $sql);
            } else {
                $params[$key] = $value;
            }
        }
        return [$sql, $params, $values];
    }

}

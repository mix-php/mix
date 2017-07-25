<?php

/**
 * Mysql类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\rdb;

use express\base\Object;

class Pdo extends Object
{

    // 数据源格式
    public $dsn = '';
    // 数据库用户名
    public $username = 'root';
    // 数据库密码
    public $password = '';
    // 设置PDO属性
    public $attribute = [];
    // 回滚含有零影响行数的事务
    public $rollbackZeroAffected = false;
    // PDO
    private $pdo;
    // PDOStatement
    private $pdoStatement;
    // sql
    private $sql;
    // values
    private $values = [];
    // 最后sql数据
    private $lastSqlData;
    // 默认属性
    private $defaultAttribute = [
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ];

    // 初始化
    public function init()
    {
        $this->pdo = new \PDO(
            $this->dsn,
            $this->username,
            $this->password,
            $this->attribute + $this->defaultAttribute
        );
    }

    // 创建命令
    public function createCommand($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    // 绑定参数
    public function bindValue($data)
    {
        $this->values += $data;
        return $this;
    }

    // 绑定预处理, 扩展绑定数组参数
    private function bindPrepare($sql, $data)
    {
        $params = $values = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $tmp = [];
                foreach ($value as $k => $v) {
                    $tmp[] = '?';
                    $values[] = $v;
                }
                $key = substr($key, 0, 1) == ':' ? $key : ":{$key}";
                $sql = str_replace($key, implode(',', $tmp), $sql);
            } else {
                $params[$key] = $value;
            }
        }
        return [$sql, $params, $values];
    }

    // 开始绑定参数
    private function bindStart()
    {
        if (empty($this->values)) {
            $this->pdoStatement = $this->pdo->prepare($this->sql);
        } else {
            list($sql, $params, $values) = $this->lastSqlData = $this->bindPrepare($this->sql, $this->values);
            $this->pdoStatement = $this->pdo->prepare($sql);
            foreach ($params as $key => &$value) {
                $this->pdoStatement->bindParam($key, $value);
            }
            foreach ($values as $key => &$value) {
                $this->pdoStatement->bindValue($key + 1, $value);
            }
        }
        $this->values = null;
    }

    // 执行查询，并返回报表类
    public function query()
    {
        $this->bindStart();
        $this->pdoStatement->execute();
        return $this->pdoStatement;
    }

    // 返回多行
    public function queryAll()
    {
        $this->bindStart();
        $this->pdoStatement->execute();
        return $this->pdoStatement->fetchAll();
    }

    // 返回一行
    public function queryOne()
    {
        $this->bindStart();
        $this->pdoStatement->execute();
        return $this->pdoStatement->fetch(isset($this->attribute[\PDO::ATTR_DEFAULT_FETCH_MODE]) ? $this->attribute[\PDO::ATTR_DEFAULT_FETCH_MODE] : $this->defaultAttribute[\PDO::ATTR_DEFAULT_FETCH_MODE]);
    }

    // 返回一列 (第一列)
    public function queryColumn()
    {
        $this->bindStart();
        $this->pdoStatement->execute();
        $column = false;
        while ($row = $this->pdoStatement->fetchColumn()) {
            $column[] = $row;
        }
        return $column;
    }

    // 返回一个标量值
    public function queryScalar()
    {
        $this->bindStart();
        $this->pdoStatement->execute();
        return $this->pdoStatement->fetchColumn();
    }

    // 执行SQL语句，并返InsertId或回受影响的行数
    public function execute()
    {
        $this->bindStart();
        $this->pdoStatement->execute();
        $affectedRows = $this->pdoStatement->rowCount();
        $lastInsertId = $this->pdo->lastInsertId();
        if ($this->pdo->inTransaction() && $this->rollbackZeroAffected && $affectedRows == 0) {
            throw new \PDOException('affected rows in the transaction is zero');
        }
        return ($affectedRows == 1 && $lastInsertId > 0) ? $lastInsertId : $affectedRows;
    }

    // 插入
    public function insert($table, $data)
    {
        $keys = array_keys($data);
        $fields = array_map(function ($key) {
            return ":{$key}";
        }, $keys);
        $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $fields) . ")";
        $this->createCommand($sql);
        $this->bindValue($data);
        return $this;
    }

    // 批量插入
    public function batchInsert($table, $data)
    {
        $keys = array_keys($data[0]);
        $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $keys) . "`) VALUES ";
        $fields = [];
        for ($i = 0; $i < count($keys); $i++) {
            $fields[] = '?';
        }
        $values = [];
        $valuesSql = [];
        foreach ($data as $item) {
            foreach ($item as $value) {
                $values[] = $value;
            }
            $valuesSql[] = "(" . implode(', ', $fields) . ")";
        }
        $sql .= implode(', ', $valuesSql);
        $this->pdoStatement = $this->pdo->prepare($sql);
        foreach ($values as $key => &$value) {
            $this->pdoStatement->bindValue($key + 1, $value);
        }
        $this->lastSqlData = [$sql, [], $values];
        return $this;
    }

    // 更新
    public function update($table, $data, $where)
    {
        $keys = array_keys($data);
        $fieldsSql = array_map(function ($key) {
            return "`{$key}` = :{$key}";
        }, $keys);
        $whereParams = [];
        foreach ($where as $key => $value) {
            $where[$key] = "`{$value[0]}` {$value[1]} :where_{$value[0]}";
            $whereParams["where_{$value[0]}"] = $value[2];
        }
        $sql = "UPDATE `{$table}` SET " . implode(', ', $fieldsSql) . " WHERE " . implode(', ', $where);
        $this->createCommand($sql);
        $this->bindValue($data);
        $this->bindValue($whereParams);
        return $this;
    }

    // 删除
    public function delete($table, $where)
    {
        $whereParams = [];
        foreach ($where as $key => $value) {
            $where[$key] = "`{$value[0]}` {$value[1]} :{$value[0]}";
            $whereParams["{$value[0]}"] = $value[2];
        }
        $sql = "DELETE FROM `{$table}` WHERE " . implode(', ', $where);
        $this->createCommand($sql);
        $this->bindValue($whereParams);
        return $this;
    }

    // 自动事务
    public function transaction($closure)
    {
        $this->beginTransaction();
        try {
            $closure();
            // 提交事务
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollBack();
            throw $e;
        }
    }

    // 开始事务
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    // 提交事务
    public function commit()
    {
        $this->pdo->commit();
    }

    // 回滚事务
    public function rollBack()
    {
        $this->pdo->rollBack();
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

    // 返回最后执行的SQL语句
    public function getLastSql()
    {
        if (isset($this->lastSqlData)) {
            list($sql, $params, $values) = $this->lastSqlData;
            $params = self::quotes($params);
            $values = self::quotes($values);
            foreach ($params as $key => $value) {
                $key = substr($key, 0, 1) == ':' ? $key : ":{$key}";
                $sql = str_replace($key, $value, $sql);
            }
            $sql = vsprintf(str_replace('?', '%s', $sql), $values);
            return $sql;
        }
        return $this->sql;
    }

}

<?php

namespace mix\client;

use mix\base\Component;

/**
 * BasePdo组件
 * @author 刘健 <coder.liu@qq.com>
 */
class BasePdo extends Component
{

    // 数据源格式
    public $dsn = '';
    // 数据库用户名
    public $username = 'root';
    // 数据库密码
    public $password = '';
    // 设置PDO属性
    public $attribute = [];
    // PDO
    protected $_pdo;
    // PDOStatement
    protected $_pdoStatement;
    // sql
    protected $_sql = '';
    // sql缓存
    protected $_sqlCache = [];
    // sql原始数据
    protected $sqlRawData = [];
    // params
    protected $_params = [];
    // values
    protected $_values = [];
    // 默认属性
    protected $_defaultAttribute = [
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ];

    // 连接
    protected function connect()
    {
        $this->_pdo = new \PDO(
            $this->dsn,
            $this->username,
            $this->password,
            $this->attribute += $this->_defaultAttribute
        );
    }

    // 关闭连接
    public function disconnect()
    {
        $this->_pdoStatement = null;
        $this->_pdo          = null;
    }

    // 查询构建
    public function queryBuilder($sqlItem)
    {
        if (isset($sqlItem['if']) && $sqlItem['if'] == false) {
            return $this;
        }
        if (isset($sqlItem['params'])) {
            $this->bindParams($sqlItem['params']);
        }
        $this->_sqlCache[] = array_shift($sqlItem);
        return $this;
    }

    // 创建命令
    public function createCommand($sql = null)
    {
        // 字符串构建
        if (is_string($sql)) {
            $this->_sql = $sql;
        }
        // 数组构建
        if (is_array($sql)) {
            foreach ($sql as $item) {
                $this->queryBuilder($item);
            }
            $this->_sql = implode(' ', $this->_sqlCache);
        }
        if (is_null($sql)) {
            $this->_sql = implode(' ', $this->_sqlCache);
        }
        return $this;
    }

    // 绑定参数
    public function bindParams($data)
    {
        $this->_params += $data;
        return $this;
    }

    // 绑定值
    protected function bindValues($data)
    {
        $this->_values += $data;
        return $this;
    }

    // 扩展数组参数的支持
    protected static function usingArray($sql, $data)
    {
        $params = $values = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $tmp = [];
                foreach ($value as $k => $v) {
                    $tmp[]    = '?';
                    $values[] = $v;
                }
                $key = substr($key, 0, 1) == ':' ? $key : ":{$key}";
                $sql = str_replace($key, implode(', ', $tmp), $sql);
            } else {
                $params[$key] = $value;
            }
        }
        return [$sql, $params, $values];
    }

    // 自动连接
    protected function autoConnect()
    {
        if (!isset($this->_pdo)) {
            $this->connect();
        }
    }

    // 执行前准备
    protected function prepare()
    {
        // 自动连接
        $this->autoConnect();
        // 准备与参数绑定
        if (!empty($this->_params)) {
            list($sql, $params, $values) = $this->sqlRawData = self::usingArray($this->_sql, $this->_params);
            $this->_pdoStatement = $this->_pdo->prepare($sql);
            foreach ($params as $key => &$value) {
                $this->_pdoStatement->bindParam($key, $value);
            }
            foreach ($values as $key => $value) {
                $this->_pdoStatement->bindValue($key + 1, $value);
            }
        } elseif (!empty($this->_values)) {
            $this->_pdoStatement = $this->_pdo->prepare($this->_sql);
            foreach ($this->_values as $key => $value) {
                $this->_pdoStatement->bindValue($key + 1, $value);
            }
            $this->sqlRawData = [$this->_sql, [], $this->_values];
        } else {
            $this->_pdoStatement = $this->_pdo->prepare($this->_sql);
            $this->sqlRawData    = [];
        }
        $this->_sqlCache = [];
        $this->_params   = [];
        $this->_values   = [];
    }

    // 返回多行
    public function queryAll()
    {
        $this->prepare();
        $this->_pdoStatement->execute();
        return $this->_pdoStatement->fetchAll();
    }

    // 返回一行
    public function queryOne()
    {
        $this->prepare();
        $this->_pdoStatement->execute();
        return $this->_pdoStatement->fetch($this->attribute[\PDO::ATTR_DEFAULT_FETCH_MODE]);
    }

    // 返回一列 (第一列)
    public function queryColumn($columnNumber = 0)
    {
        $this->prepare();
        $this->_pdoStatement->execute();
        $column = [];
        while ($row = $this->_pdoStatement->fetchColumn($columnNumber)) {
            $column[] = $row;
        }
        return $column;
    }

    // 返回一个标量值
    public function queryScalar()
    {
        $this->prepare();
        $this->_pdoStatement->execute();
        return $this->_pdoStatement->fetchColumn();
    }

    // 执行SQL语句
    public function execute()
    {
        $this->prepare();
        return $this->_pdoStatement->execute();
    }

    // 返回最后插入行的ID或序列值
    public function getLastInsertId()
    {
        return $this->_pdo->lastInsertId();
    }

    // 返回受上一个 SQL 语句影响的行数
    public function getRowCount()
    {
        return $this->_pdoStatement->rowCount();
    }

    // 插入
    public function insert($table, $data)
    {
        $keys   = array_keys($data);
        $fields = array_map(function ($key) {
            return ":{$key}";
        }, $keys);
        $sql    = "INSERT INTO `{$table}` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $fields) . ")";
        $this->createCommand($sql);
        $this->bindParams($data);
        return $this;
    }

    // 批量插入
    public function batchInsert($table, $data)
    {
        $keys   = array_keys($data[0]);
        $sql    = "INSERT INTO `{$table}` (`" . implode('`, `', $keys) . "`) VALUES ";
        $fields = [];
        for ($i = 0; $i < count($keys); $i++) {
            $fields[] = '?';
        }
        $values    = [];
        $valuesSql = [];
        foreach ($data as $item) {
            foreach ($item as $value) {
                $values[] = $value;
            }
            $valuesSql[] = "(" . implode(', ', $fields) . ")";
        }
        $sql .= implode(', ', $valuesSql);
        $this->createCommand($sql);
        $this->bindValues($values);
        return $this;
    }

    // 更新
    public function update($table, $data, $where)
    {
        $keys        = array_keys($data);
        $fieldsSql   = array_map(function ($key) {
            return "`{$key}` = :{$key}";
        }, $keys);
        $whereParams = [];
        foreach ($where as $key => $value) {
            $where[$key]                      = "`{$value[0]}` {$value[1]} :where_{$value[0]}";
            $whereParams["where_{$value[0]}"] = $value[2];
        }
        $sql = "UPDATE `{$table}` SET " . implode(', ', $fieldsSql) . " WHERE " . implode(' AND ', $where);
        $this->createCommand($sql);
        $this->bindParams($data);
        $this->bindParams($whereParams);
        return $this;
    }

    // 删除
    public function delete($table, $where)
    {
        $whereParams = [];
        foreach ($where as $key => $value) {
            $where[$key]                = "`{$value[0]}` {$value[1]} :{$value[0]}";
            $whereParams["{$value[0]}"] = $value[2];
        }
        $sql = "DELETE FROM `{$table}` WHERE " . implode(' AND ', $where);
        $this->createCommand($sql);
        $this->bindParams($whereParams);
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
        $this->_pdo->beginTransaction();
    }

    // 提交事务
    public function commit()
    {
        $this->_pdo->commit();
    }

    // 回滚事务
    public function rollBack()
    {
        $this->_pdo->rollBack();
    }

    // 给字符串加引号
    protected static function quotes($var)
    {
        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::quotes($v);
            }
            return $var;
        }
        return is_numeric($var) ? $var : "'{$var}'";
    }

    // 返回原生SQL语句
    public function getRawSql()
    {
        if (!empty($this->sqlRawData)) {
            list($sql, $params, $values) = $this->sqlRawData;
            $params = self::quotes($params);
            $values = self::quotes($values);
            foreach ($params as $key => $value) {
                $key = substr($key, 0, 1) == ':' ? $key : ":{$key}";
                $sql = str_replace($key, $value, $sql);
            }
            $sql = vsprintf(str_replace('?', '%s', $sql), $values);
            return $sql;
        }
        return $this->_sql;
    }

}

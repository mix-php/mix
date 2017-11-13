<?php

namespace mix\rdb;

use mix\base\Component;

/**
 * PdoCluster组件
 * @author 刘健 <coder.liu@qq.com>
 */
class PdoCluster extends Pdo
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
    public $rollbackZeroAffectedTransaction = false;
    // PDO
    protected $_pdo;
    // PDOStatement
    protected $_pdoStatement;
    // sql
    protected $_sql;
    // sql缓存
    protected $_sqlCache = [];
    // params
    protected $_params = [];
    // values
    protected $_values = [];
    // 最后sql数据
    protected $_lastSqlData;
    // 默认属性
    protected $_defaultAttribute = [
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ];

    // 连接
    public function connect()
    {
        $this->_pdo = new \PDO(
            $this->dsn,
            $this->username,
            $this->password,
            $this->attribute += $this->_defaultAttribute
        );
    }

    // 关闭连接
    public function close()
    {
        $this->_pdoStatement = null;
        $this->_pdo          = null;
    }

}

<?php

namespace mix\rdb;

use mix\base\Component;

/**
 * PdoCluster组件
 * @author 刘健 <coder.liu@qq.com>
 */
class PdoCluster extends Pdo
{

    // 主服务器组
    protected $masters = [];
    // 从服务器组
    protected $slaves = [];
    // pdo池
    protected $_pdos;

    // 主连接
    protected function connectMaster()
    {
        if (!isset($this->_pdos['master'])) {
            $this->dsn = $this->masters[array_rand($this->masters)];
            parent::connect();
            $this->_pdos['master'] = $this->_pdo;
        } else {
            $this->_pdo = $this->_pdos['master'];
        }
    }

    // 从连接
    protected function connectSlave()
    {
        if (!isset($this->_pdos['slave'])) {
            $this->dsn = $this->slaves[array_rand($this->slaves)];
            parent::connect();
            $this->_pdos['slave'] = $this->_pdo;
        } else {
            $this->_pdo = $this->_pdos['slave'];
        }
    }

    // 检查是否为Select语句
    protected static function isSelect($sql)
    {
        if (stripos('SELECT', $sql) === false) {
            return false;
        }
        return true;
    }

    // 检查是否在一个事务内
    protected function inTransaction()
    {
        // 检查是否有Master连接，且在一个事务内
        if (isset($this->_pdos['master']) && $this->_pdos['master']->inTransaction()) {
            return true;
        }
        return false;
    }

    // 根据SQL类型连接
    public function connect()
    {
        // 主从选择
        if (self::isSelect($this->_sql) && !$this->inTransaction()) {
            $this->connectSlave();
        } else {
            $this->connectMaster();
        }
    }

    // 关闭连接
    public function close()
    {
        parent::close();
        $this->_pdos = null;
    }

    // 执行前准备
    protected function prepare()
    {
        // 根据SQL连接
        $this->connect();
        // 执行前准备
        parent::prepare();
    }

    // 开始事务
    public function beginTransaction()
    {
        $this->connectMaster();
        parent::beginTransaction();
    }

}

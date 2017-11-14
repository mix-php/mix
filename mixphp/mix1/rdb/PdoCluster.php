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
    // 配置主服务器
    protected $masterConfig = [];
    // 从服务器组
    protected $slaves = [];
    // 配置从服务器
    protected $slaveConfig = [];
    // pdo池
    protected $_pdos;

    // 主连接
    protected function connectMaster()
    {
        if (!isset($this->_pdos['master'])) {
            $this->dsn      = $this->masters[array_rand($this->masters)];
            $this->username = $this->masterConfig['username'];
            $this->password = $this->masterConfig['password'];
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
            $this->dsn      = $this->slaves[array_rand($this->slaves)];
            $this->username = $this->slaveConfig['username'];
            $this->password = $this->slaveConfig['password'];
            parent::connect();
            $this->_pdos['slave'] = $this->_pdo;
        } else {
            $this->_pdo = $this->_pdos['slave'];
        }
    }

    // 检查是否为Select语句
    protected static function isSelect($sql)
    {
        if (stripos($sql, 'SELECT') === false) {
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

    // 连接
    public function connect()
    {
        $this->connectMaster();
        $this->connectSlave();
    }

    // 关闭连接
    public function close()
    {
        parent::close();
        $this->_pdos = null;
    }

    // 根据SQL类型自动连接
    protected function autoConnect()
    {
        // 主从选择
        if (self::isSelect($this->_sql) && !$this->inTransaction()) {
            $this->connectSlave();
        } else {
            $this->connectMaster();
        }
    }

    // 执行前准备
    protected function prepare()
    {
        // 根据SQL类型自动连接
        $this->autoConnect();
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

<?php

namespace mix\client;

/**
 * BasePdoMasterSlave组件
 * @author 刘健 <coder.liu@qq.com>
 */
class BasePdoMasterSlave extends BasePdo
{

    // 主服务器组
    public $masters = [];
    // 配置主服务器
    public $masterConfig = [];
    // 从服务器组
    public $slaves = [];
    // 配置从服务器
    public $slaveConfig = [];
    // pdo池
    protected $_pdos;

    // 关闭连接
    public function disconnect()
    {
        parent::disconnect();
        $this->_pdos = null;
    }

    // 主连接
    protected function selectMaster()
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
    protected function selectSlave()
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

    // 根据SQL类型自动连接
    protected function autoConnect()
    {
        // 主从选择
        if (self::isSelect($this->_sql) && !$this->inTransaction()) {
            $this->selectSlave();
        } else {
            $this->selectMaster();
        }
    }

    // 开始事务
    public function beginTransaction()
    {
        $this->selectMaster();
        parent::beginTransaction();
    }

}

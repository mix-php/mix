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
    protected $masters;
    // 从服务器组
    protected $slaves;
    // pdo池
    protected $_pdos;
    // SQL类型
    const SQL_TYPE_READ = 0;
    const SQL_TYPE_WRITE = 1;
    protected $_sqlType;

    // 主连接
    public function masterConnect()
    {
        if (!isset($this->_pdos['master'])) {
            $this->dsn = $this->masters[array_rand($this->masters)];
            parent::connect();
            $this->_pdos['master'] = $this->_pdo;
        }
        return $this->_pdos['master'];
    }

    // 从连接
    public function slaveConnect()
    {
        if (!isset($this->_pdos['slave'])) {
            $this->dsn = $this->slaves[array_rand($this->slaves)];
            parent::connect();
            $this->_pdos['slave'] = $this->_pdo;
        }
        return $this->_pdos['slave'];
    }

    // 连接
    public function connect()
    {
        // 主从选择
        if ($this->_sqlType == self::SQL_TYPE_READ) {
            $this->slaveConnect();
        } else {
            $this->masterConnect();
        }
    }

    // 执行前准备
    protected function prepare()
    {
        // SQL类型判断

        // 执行前准备
        parent::prepare();
    }


}

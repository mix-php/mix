<?php

namespace mix\rdb;

/**
 * BasePdoPersistent组件
 * @author 刘健 <coder.liu@qq.com>
 */
class BasePdoPersistent extends BasePdo
{

    // 连接持续时间
    public $persistentTime = 7200;
    // 连接时间
    protected $_connectTime;

    // 初始化
    public function initialize()
    {
        // 共用连接对象
        $hash               = md5($this->dsn . $this->username . $this->password);
        $this->_pdo         = &\Mix::$container['pdo_' . $hash];
        $this->_connectTime = &\Mix::$container['pdoConnectTime_' . $hash];
        // 连接
        $this->connect();
    }

    // 连接
    public function connect()
    {
        $this->close();
        $this->_connectTime = time();
        parent::connect();
    }

    // 执行前准备
    protected function prepare()
    {
        // 主动重新连接
        if ($this->_connectTime + $this->persistentTime < time()) {
            $this->connect();
        }
        try {
            // 执行前准备
            parent::prepare();
        } catch (\Exception $e) {
            // 长连接超时处理
            $this->connect();
            throw $e;
        }
    }

}

<?php

namespace mix\swoole;

/**
 * Mysql长连接组件
 * @author 刘健 <coder.liu@qq.com>
 */
class PdoPersistent extends \mix\rdb\Pdo
{

    // 连接持续时间
    public $persistentTime = 7200;

    // 连接时间
    protected $_connectTime;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 共用连接对象
        $this->_pdo         = &\Mix::$container['_pdo'];
        $this->_connectTime = &\Mix::$container['_pdoConnectTime'];
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

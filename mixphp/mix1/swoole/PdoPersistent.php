<?php

namespace mix\swoole;

/**
 * Mysql长连接组件
 * @author 刘健 <coder.liu@qq.com>
 */
class PdoPersistent extends \mix\rdb\Pdo
{

    // 重连时间
    public $reconnection = 7200;

    // 连接时间
    protected $_connectTime;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 共用连接对象
        $this->_pdo         = &\Mix::$container['_pdo'];
        $this->_connectTime = &\Mix::$container['_pdoConnectTime'];
        if (is_null($this->_pdo)) {
            // 连接
            $this->connect();
        }
    }

    // 连接
    public function connect()
    {
        if (isset($this->_pdo)) {
            // 置空才会释放旧连接
            $this->_pdoStatement = null;
            $this->_pdo          = null;
        }
        $this->_connectTime = time();
        parent::connect();
    }

    // 开始绑定参数
    protected function bindStart()
    {
        // 主动重新连接
        if ($this->_connectTime + $this->reconnection < time()) {
            $this->connect();
        }
        try {
            // 开始绑定参数
            parent::bindStart();
        } catch (\Exception $e) {
            // 长连接超时处理
            $this->connect();
            throw $e;
        }
    }

}

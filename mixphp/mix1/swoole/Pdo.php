<?php

namespace mix\swoole;

/**
 * Mysql类
 * @author 刘健 <code.liu@qq.com>
 */
class Pdo extends \mix\web\Pdo
{

    // 重连时间
    public $reconnection = 7200;

    // 连接时间
    protected $_connectTime;

    // 初始化
    public function init()
    {
        // 共用连接对象
        $this->_pdo = &\Mix::$container['_pdo'];
        $this->_connectTime = &\Mix::$container['_connectTime'];
        if (is_null($this->_pdo)) {
            // 连接
            $this->connect();
        } else {
            $this->_connectTime = time();
        }
    }

    // 连接
    public function connect()
    {
        isset($this->_pdo) and $this->_pdo = null; // 置空才会释放旧连接
        $this->_connectTime = time();
        parent::connect();
    }

    // 开始绑定参数
    protected function bindStart()
    {
        // 主动重新连接
        if ($this->_connectTime + $this->reconnection < time()) {
            var_dump('init');
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

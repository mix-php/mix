<?php

namespace mix\swoole;

use mix\base\Component;

/**
 * Session组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Session extends Component
{

    // 处理者值
    const HANDLER_REDIS = 'redis';
    // 处理者
    public $saveHandler = self::HANDLER_REDIS;
    // 保存路径
    public $savePath = '';
    // 生存时间
    public $gcMaxLifetime = 7200;
    // session名
    public $name = 'MIXSSID';

    // 处理者
    protected $_handler;
    // SessionID
    protected $_sessionId;
    // 保存路径参数
    protected $_savePath;
    // 保存的key
    protected $_saveKey;

    // 请求开始事件
    public function onRequestStart()
    {
        parent::onRequestStart();
        $this->createHandler();
        $this->loadSessionId();
    }

    // 请求结束事件
    public function onRequestEnd()
    {
        parent::onRequestEnd();
        $this->_handler   = null;
        $this->_sessionId = null;
    }

    // 创建 Handler
    protected function createHandler()
    {
        if (!isset($this->_handler)) {
            // 解析参数
            $savePath = parse_url($this->savePath);
            parse_str($savePath['query'], $query);
            $savePath += $query;
            $this->_savePath = $savePath;
            // Redis Handler
            switch ($this->saveHandler) {
                case self::HANDLER_REDIS:
                    $this->_handler = $this->createRedisHandler();
                    break;
            }
        }
    }

    // 创建 Redis Handler
    protected function createRedisHandler()
    {
        // 创建 Redis Handler
        $redis = new \Redis();
        if (!$redis->connect($this->_savePath['host'], $this->_savePath['port'])) {
            throw new \Exception('Redis连接失败');
        }
        $redis->auth($this->_savePath['auth']);
        $redis->select($this->_savePath['database']);
        return $redis;
    }

    // 载入session_id
    protected function loadSessionId()
    {
        $this->_sessionId = \Mix::app()->request->cookie($this->name);
        if (is_null($this->_sessionId)) {
            $this->_sessionId = $this->createSessionId();
        }
        \Mix::app()->response->setCookie($this->name, $this->_sessionId, time() + $this->gcMaxLifetime);
        $this->_saveKey = $this->_savePath['prefix'] . $this->_sessionId;
    }

    // 创建session_id
    protected function createSessionId()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $name  = '';
        for ($i = 0; $i < 26; $i++) {
            $name .= $chars{mt_rand(0, 61)};
        }
        return $name;
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            $value = $this->_handler->hGetAll($this->_saveKey);
            return $value ?: null;
        }
        $array = $this->_handler->hmGet($this->_saveKey, [$name]);
        return empty($array) ? null : array_shift($array);
    }

    // 赋值
    public function set($name, $value)
    {
        $success = $this->_handler->hMset($this->_saveKey, [$name => $value]);
        $this->_handler->setTimeout($this->_saveKey, $this->gcMaxLifetime);
        return $success ? true : false;
    }

    // 判断是否存在
    public function has($name)
    {
        $exist = $this->_handler->hExists($this->_saveKey, $name);
        return $exist ? true : false;
    }

    // 删除
    public function delete($name)
    {
        $success = $this->_handler->hDel($this->_saveKey, $name);
        return $success ? true : false;
    }

    // 清除session
    public function clear()
    {
        $success = $this->_handler->del($this->_saveKey);
        return $success ? true : false;
    }

}

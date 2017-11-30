<?php

namespace mix\web;

use mix\base\Component;

/**
 * Token组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Token extends Component
{

    // 处理者值
    const HANDLER_REDIS = 'redis';
    // 处理者
    public $saveHandler = self::HANDLER_REDIS;
    // 处理者配置信息
    public $handlerConfig = [
        // 主机
        'host'     => '127.0.0.1',
        // 端口
        'port'     => 6379,
        // 数据库
        'database' => 0,
        // 密码
        'password' => '',
        // Key前缀
        'prefix'   => 'MIXTKID:',
    ];
    // 有效期
    public $expires = 7200;
    // session名
    public $name = 'access_token';
    // 处理者
    protected $_handler;
    // TokenKey
    protected $_tokenKey;
    // TokenID
    protected $_tokenId;
    // Token前缀
    protected $_tokenPrefix;
    // 唯一索引前缀
    protected $_uniqueIndexPrefix;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 创建 Handler
        $this->createHandler();
        // 前缀处理
        $this->_tokenPrefix       = $this->handlerConfig['prefix'] . 'DATA:';
        $this->_uniqueIndexPrefix = $this->handlerConfig['prefix'] . 'UNIQUEINDEX:';
    }

    // 请求开始事件
    public function onRequestStart()
    {
        parent::onRequestStart();
        // 载入TokenID
        $this->loadTokenId();
    }

    // 请求结束事件
    public function onRequestEnd()
    {
        parent::onRequestEnd();
        // 关闭连接
        $this->_handler->disconnect();
    }

    // 创建 Handler
    protected function createHandler()
    {
        switch ($this->saveHandler) {
            case self::HANDLER_REDIS:
                $this->createRedisHandler();
                break;
        }
    }

    // 创建 Redis Handler
    protected function createRedisHandler()
    {
        $class          = $this->handlerConfig['class'];
        $redis          = new $class([
            'host'     => $this->handlerConfig['host'],
            'port'     => $this->handlerConfig['port'],
            'database' => $this->handlerConfig['database'],
            'password' => $this->handlerConfig['password'],
        ]);
        $this->_handler = $redis;
    }

    // 载入TokenID
    protected function loadTokenId()
    {
        $this->_tokenId = \Mix::app()->request->get($this->name) or
        $this->_tokenId = \Mix::app()->request->header($this->name) or
        $this->_tokenId = \Mix::app()->request->post($this->name);
        if (is_null($this->_tokenId)) {
            $this->_tokenId = self::createTokenId();
        }
        $this->_tokenKey = $this->_tokenPrefix . $this->_tokenId;
    }

    // 创建TokenID
    protected static function createTokenId()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $name  = '';
        for ($i = 0; $i < 32; $i++) {
            $name .= $chars{mt_rand(0, 61)};
        }
        return $name;
    }

    // 设置唯一索引
    public function setUniqueIndex($uniqueId)
    {
        $uniqueKey = $this->_uniqueIndexPrefix . $uniqueId;
        // 删除旧token数据
        $beforeTokenId = $this->_handler->get($uniqueKey);
        if (!empty($beforeTokenId)) {
            $beforeTokenkey = $this->_tokenPrefix . $beforeTokenId;
            $this->_handler->del($beforeTokenkey);
        }
        // 更新唯一索引
        $this->_handler->setex($uniqueKey, $this->expires, $this->_tokenId);
    }

    // 赋值
    public function set($name, $value)
    {
        $success = $this->_handler->hMset($this->_tokenKey, [$name => serialize($value)]);
        $this->_handler->setTimeout($this->_tokenKey, $this->expires);
        return $success ? true : false;
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            $array = $this->_handler->hGetAll($this->_tokenKey);
            foreach ($array as $key => $item) {
                $array[$key] = unserialize($item);
            }
            return $array ?: [];
        }
        $reslut = $this->_handler->hmGet($this->_tokenKey, [$name]);
        $value  = array_shift($reslut);
        return $value === false ? null : unserialize($value);
    }

    // 判断是否存在
    public function has($name)
    {
        $exist = $this->_handler->hExists($this->_tokenKey, $name);
        return $exist ? true : false;
    }

    // 删除
    public function delete($name)
    {
        $success = $this->_handler->hDel($this->_tokenKey, $name);
        return $success ? true : false;
    }

    // 清除session
    public function clear()
    {
        $success = $this->_handler->del($this->_tokenKey);
        return $success ? true : false;
    }

    // 获取TokenId
    public function getTokenId()
    {
        return $this->_tokenId;
    }

}

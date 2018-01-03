<?php

namespace mix\web;

use mix\base\Component;

/**
 * Token组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Token extends Component
{

    // 保存处理者
    public $saveHandler;
    // 保存的Key前缀
    public $saveKeyPrefix;
    // 有效期
    public $expires = 7200;
    // session名
    public $name = 'access_token';
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
        // 前缀处理
        $this->_tokenPrefix       = $this->saveKeyPrefix . 'DATA:';
        $this->_uniqueIndexPrefix = $this->saveKeyPrefix . 'UNIQUEINDEX:';
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
        $this->saveHandler->disconnect();
    }

    // 载入TokenID
    public function loadTokenId()
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
        $beforeTokenId = $this->saveHandler->get($uniqueKey);
        if (!empty($beforeTokenId)) {
            $beforeTokenkey = $this->_tokenPrefix . $beforeTokenId;
            $this->saveHandler->del($beforeTokenkey);
        }
        // 更新唯一索引
        $this->saveHandler->setex($uniqueKey, $this->expires, $this->_tokenId);
    }

    // 赋值
    public function set($name, $value)
    {
        $success = $this->saveHandler->hMset($this->_tokenKey, [$name => serialize($value)]);
        $this->saveHandler->setTimeout($this->_tokenKey, $this->expires);
        return $success ? true : false;
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            $array = $this->saveHandler->hGetAll($this->_tokenKey);
            foreach ($array as $key => $item) {
                $array[$key] = unserialize($item);
            }
            return $array ?: [];
        }
        $reslut = $this->saveHandler->hmGet($this->_tokenKey, [$name]);
        $value  = array_shift($reslut);
        return $value === false ? null : unserialize($value);
    }

    // 判断是否存在
    public function has($name)
    {
        $exist = $this->saveHandler->hExists($this->_tokenKey, $name);
        return $exist ? true : false;
    }

    // 删除
    public function delete($name)
    {
        $success = $this->saveHandler->hDel($this->_tokenKey, $name);
        return $success ? true : false;
    }

    // 清除session
    public function clear()
    {
        $success = $this->saveHandler->del($this->_tokenKey);
        return $success ? true : false;
    }

    // 获取TokenId
    public function getTokenId()
    {
        return $this->_tokenId;
    }

}

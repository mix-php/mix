<?php

namespace mix\swoole;

use mix\base\Component;

/**
 * WebSocketToken组件
 * @author 刘健 <coder.liu@qq.com>
 */
class WebSocketToken extends Component
{

    // 保存处理者
    public $saveHandler;
    // 保存的Key前缀
    public $saveKeyPrefix;
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

    // 设置TokenID
    protected function setTokenId($tokenId)
    {
        // 赋值
        $this->_tokenId  = $tokenId;
        $this->_tokenKey = $this->_tokenPrefix . $this->_tokenId;
        // 关闭连接
        $this->saveHandler->disconnect();
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

    // 获取TokenId
    public function getTokenId()
    {
        return $this->_tokenId;
    }

}

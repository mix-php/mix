<?php

namespace mix\websocket;

use mix\base\Component;

/**
 * TokenReader组件
 * @author 刘健 <coder.liu@qq.com>
 */
class TokenReader extends Component
{

    // 保存处理者
    public $saveHandler;
    // 保存的Key前缀
    public $saveKeyPrefix;
    // session名
    public $name = 'access_token';
    // TokenKey
    protected $_tokenKey;
    // TokenID
    protected $_tokenId;
    // Token前缀
    protected $_tokenPrefix;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 前缀处理
        $this->_tokenPrefix = $this->saveKeyPrefix . 'DATA:';
    }

    // 载入TokenID
    public function loadTokenId($request)
    {
        // 关闭
        $this->close();
        // 载入TokenID
        $this->_tokenId = $request->get($this->name) or
        $this->_tokenId = $request->header($this->name);
        $this->_tokenKey = $this->_tokenPrefix . $this->_tokenId;
        // 返回
        return $this;
    }

    // 关闭
    public function close()
    {
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

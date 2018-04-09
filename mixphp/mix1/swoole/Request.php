<?php

namespace mix\swoole;

use mix\web\BaseRequest;

/**
 * Request组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Request extends BaseRequest
{

    // 请求对象
    protected $_requester;

    // 设置请求对象
    public function setRequester($requester)
    {
        $this->_requester = $requester;
        // 重置数据
        $this->setRoute([]);
        $this->_get    = isset($requester->get) ? $requester->get : [];
        $this->_post   = isset($requester->post) ? $requester->post : [];
        $this->_files  = isset($requester->files) ? $requester->files : [];
        $this->_cookie = isset($requester->cookie) ? $requester->cookie : [];
        $this->_server = isset($requester->server) ? $requester->server : [];
        $this->_header = isset($requester->header) ? $requester->header : [];
    }

    // 返回原始的HTTP包体
    public function getRawBody()
    {
        return $this->_requester->rawContent();
    }

}

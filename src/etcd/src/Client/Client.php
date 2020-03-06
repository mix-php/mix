<?php

namespace Mix\Etcd\Client;

use Mix\Etcd\Exception\NotFoundException;

/**
 * Class Client
 * @package Mix\Etcd\Client
 */
class Client extends \Etcd\Client
{

    /**
     * Watch prefix
     * @param string $prefix
     * @param \Closure $func
     * @return Watcher
     */
    public function watchKeysWithPrefix(string $prefix, \Closure $func)
    {
        return new Watcher($this->server, $prefix, $func);
    }

    /**
     * 重写该方法，让 lease 失效时修改为抛出异常
     *
     * keeps the lease alive by streaming keep alive requests
     * from the client\nto the server and streaming keep alive responses
     * from the server to the client.
     *
     * @param int64 $id ID is the lease ID for the lease to keep alive.
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function keepAlive($id)
    {
        $params = [
            'ID' => $id,
        ];

        $body = $this->request(self::URI_KEEPALIVE, $params);

        if (!isset($body['result'])) {
            return $body;
        }

        if (!isset($body['result']['ID']) || !isset($body['result']['TTL'])) {
            throw new NotFoundException('Invalid lease id');
        }

        // response "result" field, etcd bug?
        return [
            'ID'  => $body['result']['ID'],
            'TTL' => $body['result']['TTL'],
        ];
    }

}

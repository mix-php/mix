<?php

namespace Mix\Etcd\Client;

use Mix\Micro\Exception\NotFoundException;

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
        return new Watcher($this->server, $this->token, $prefix, $func);
    }

    /**
     * 重写该方法，统一登录与非登录的返回数据格式
     * Gets the key or a range of keys
     *
     * @param string $key
     * @param array $options
     *         string range_end
     *         int    limit
     *         int    revision
     *         int    sort_order
     *         int    sort_target
     *         bool   serializable
     *         bool   keys_only
     *         bool   count_only
     *         int64  min_mod_revision
     *         int64  max_mod_revision
     *         int64  min_create_revision
     *         int64  max_create_revision
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function get($key, array $options = [])
    {
        $params  = [
            'key' => $key,
        ];
        $params  = $this->encode($params);
        $options = $this->encode($options);
        $body    = $this->request(self::URI_RANGE, $params, $options);
        $body    = $this->decodeBodyForFields(
            $body,
            'kvs',
            ['key', 'value',]
        );

        if (isset($body['kvs'])) {
            return $this->convertFields($body['kvs']);
        }

        return [];
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

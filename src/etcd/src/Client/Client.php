<?php

namespace Mix\Etcd\Client;

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

}

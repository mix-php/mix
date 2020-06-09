<?php

namespace Mix\Micro\Server;

/**
 * Interface ServerInterface
 * @package Mix\Micro\Server
 */
interface ServerInterface
{

    /**
     * 获取全部 service 名称
     * @return string[][] [name => [class,...]]
     */
    public function services();

    /**
     * Host
     * @return string
     */
    public function host();

    /**
     * Port
     * @return int
     */
    public function port();

    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown();

}

<?php

/**
 * Interface ApplicationInterface
 * @author LIUJIAN <coder.keda@gmail.com>
 *
 * 系统组件 <不可改名>
 * @property \Mix\Log\Logger $log
 * @property \Mix\Http\Route $route
 * @property \Mix\Http\Message\Request|\Mix\Http\Message\Compatible\Request $request
 * @property \Mix\Http\Message\Response|\Mix\Http\Message\Compatible\Response $response
 * @property \Mix\Console\Error|\Mix\Http\Error|\Mix\WebSocket\Error $error
 * @property \Mix\WebSocket\Registry $registry
 * @property \Mix\WebSocket\WebSocketConnection $ws
 * @property \Mix\WebSocket\Frame $frame
 *
 * 自定义组件
 * @property \Mix\Auth\Authorization $auth
 * @property \Mix\Http\Session\HttpSession $session
 * @property \Mix\Tcp\Session\TcpSession $tcpSession
 * @property \Mix\Database\PDOConnection|Mix\Database\MasterSlave\PDOConnection $pdo
 * @property \Mix\Redis\RedisConnection $redis
 * @property \Mix\Database\Pool\ConnectionPool $pdoPool
 * @property \Mix\Redis\Pool\ConnectionPool $redisPool
 */
interface ApplicationInterface
{
}

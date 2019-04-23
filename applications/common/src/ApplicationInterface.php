<?php

/**
 * Interface ApplicationInterface
 * @author liu,jian <coder.keda@gmail.com>
 *
 * 系统组件 <不可改名>
 * @property \Mix\Log\Logger $log
 * @property \Mix\Http\Route $route
 * @property \Mix\Http\Message\Request\HttpRequest|\Mix\Http\Message\Request\Compatible\HttpRequest $request
 * @property \Mix\Http\Message\Response\HttpResponse|\Mix\Http\Message\Response\Compatible\HttpResponse $response
 * @property \Mix\Console\Error|\Mix\Http\Error|\Mix\WebSocket\Error $error
 * @property \Mix\WebSocket\Registry|\Mix\Tcp\Registry|\Mix\Udp\Registry $registry
 * @property \Mix\WebSocket\WebSocketConnection $ws
 * @property \Mix\Tcp\TcpConnection $tcp
 * @property \Mix\Udp\UdpSender $udp
 *
 * 自定义组件
 * @property \Mix\Auth\Authorization $auth
 * @property \Mix\Http\Session\HttpSession $session
 * @property \Mix\Tcp\Session\TcpSession $tcpSession
 * @property \Mix\Database\PDOConnection|Mix\Database\MasterSlave\PDOConnection $db
 * @property \Mix\Redis\RedisConnection $redis
 * @property \Mix\Database\Pool\ConnectionPool $dbPool
 * @property \Mix\Redis\Pool\ConnectionPool $redisPool
 * @property \Mix\Cache\Cache $cache
 */
interface ApplicationInterface
{
}

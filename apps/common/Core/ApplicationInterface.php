<?php

namespace Apps\Common\Core;

/**
 * App类Interface
 * @author 刘健 <coder.liu@qq.com>
 * 
 * @property \Mix\Log\Logger $log
 * @property \Mix\Console\Input $input
 * @property \Mix\Console\Output $output
 * @property \Mix\Http\Route $route
 * @property \Mix\Http\Request|\Mix\Http\Compatible\Request $request
 * @property \Mix\Http\Response|\Mix\Http\Compatible\Response $response
 * @property \Mix\Http\Error|\Mix\Console\Error $error
 * @property \Mix\Http\Token $token
 * @property \Mix\Http\Session $session
 * @property \Mix\Http\Cookie $cookie
 * @property \Mix\Database\PDOConnection $pdo
 * @property \Mix\Redis\RedisConnection $redis
 * @property \Mix\WebSocket\TokenReader $tokenReader
 * @property \Mix\WebSocket\SessionReader $sessionReader
 * @property \Mix\WebSocket\MessageHandler $messageHandler
 * @property \Mix\Pool\ConnectionPool $connectionPool
 */
interface ApplicationInterface
{
}

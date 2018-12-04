<?php

namespace Apps\Common\Core;

/**
 * App接口
 * @author 刘健 <coder.liu@qq.com>
 *
 * 系统组件
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
 * @property \Mix\WebSocket\TokenReader $tokenReader
 * @property \Mix\WebSocket\SessionReader $sessionReader
 * @property \Mix\WebSocket\MessageHandler $messageHandler
 * 
 * 用户自定义组件
 * @property \Mix\Redis\Coroutine\RedisPool $redisPool
 * @property \Mix\Database\PDOConnection $pdo
 * @property \Mix\Redis\RedisConnection $redis
 */
interface ApplicationInterface
{
}

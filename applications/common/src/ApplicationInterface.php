<?php

/**
 * Interface ApplicationInterface
 * @author LIUJIAN <coder.keda@gmail.com>
 *
 * 系统组件
 * @property \Mix\Log\Logger $log
 * @property \Mix\Http\Route $route
 * @property \Mix\Http\Request|\Mix\Http\Compatible\Request $request
 * @property \Mix\Http\Response|\Mix\Http\Compatible\Response $response
 * @property \Mix\Http\Error|\Mix\Console\Error $error
 *
 * 自定义组件
 * @property \Mix\Auth\Authorization $auth
 * @property \Mix\Http\Session\HttpSession $session
 * @property \Mix\Database\PDOConnection|Mix\Database\MasterSlave\PDOConnection $pdo
 * @property \Mix\Redis\RedisConnection $redis
 * @property \Mix\Database\Pool\ConnectionPool $pdoPool
 * @property \Mix\Redis\Pool\ConnectionPool $redisPool
 */
interface ApplicationInterface
{
}


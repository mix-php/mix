<?php

namespace Mix\Database;

use Mix\Bean\BeanInjector;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Dialer
 * @package Mix\Database
 */
class Dialer
{

    /**
     * 数据源格式
     * @var string
     */
    public $dsn = '';

    /**
     * 数据库用户名
     * @var string
     */
    public $username = 'root';

    /*
     * 数据库密码
     */
    public $password = '';

    /**
     * 驱动连接选项
     * @var array
     */
    public $options = [];

    /**
     * 最多可空闲连接数
     * @var int
     */
    public $maxIdle = 5;

    /**
     * 最大连接数
     * @var int
     */
    public $maxActive = 5;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * AbstractConnection constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Dial
     * @return Database
     */
    public function dial(string $dsn, string $username, string $password): Database
    {
        $db = new Database([
            'dsn'        => $dsn,
            'username'   => $username,
            'password'   => $password,
            'options'    => $this->options,
            'maxIdle'    => $this->maxIdle,
            'maxActive'  => $this->maxActive,
            'dispatcher' => $this->dispatcher,
        ]);
        $db->init();
        return $db;
    }

}

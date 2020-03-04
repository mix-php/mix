<?php

namespace Mix\Database\Pool;

use Mix\Bean\BeanInjector;
use Mix\Database\Connection;
use Mix\Pool\DialerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Dialer
 * @package Mix\Database\Pool
 */
class Dialer implements DialerInterface
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
    public $attributes = [];

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $eventDispatcher;

    /**
     * Dialer constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config)
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Dial
     * @return Connection
     */
    public function dial()
    {
        return new Connection([
            'dsn'             => $this->dsn,
            'username'        => $this->username,
            'password'        => $this->password,
            'attributes'      => $this->attributes,
            'eventDispatcher' => $this->eventDispatcher,
        ]);
    }

}

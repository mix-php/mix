<?php

namespace Mix\Database\Pool;

use Mix\Bean\BeanInjector;
use Mix\Database\Driver;
use Mix\Pool\DialerInterface;

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
    public $options = [];

    /**
     * Dialer constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Dial
     * @return Driver
     * @throws \PDOException
     */
    public function dial()
    {
        return new Driver(
            $this->dsn,
            $this->username,
            $this->password,
            $this->options
        );
    }

}

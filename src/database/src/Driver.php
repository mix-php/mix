<?php

namespace Mix\Database;

use Mix\ObjectPool\ObjectTrait;

/**
 * Class Driver
 * @package Mix\Database
 */
class Driver
{

    use ObjectTrait;

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
     * @var \PDO
     */
    protected $pdo;

    /**
     * 默认驱动连接选项
     * @var array
     */
    protected $defaultOptions = [
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ];

    /**
     * Driver constructor.
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $options
     * @throws \PDOException
     */
    public function __construct(string $dsn, string $username, string $password, array $options)
    {
        $this->dsn      = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options  = $options;
        $this->connect();
    }

    /**
     * Get instance
     * @return \PDO
     */
    public function instance()
    {
        return $this->pdo;
    }

    /**
     * Get options
     * @return array
     */
    public function options()
    {
        return $this->options + $this->defaultOptions;
    }

    /**
     * Connect
     * @throws \PDOException
     */
    public function connect()
    {
        $this->pdo = new \PDO(
            $this->dsn,
            $this->username,
            $this->password,
            $this->options()
        );
    }

    /**
     * Close
     */
    public function close()
    {
        $this->pdo = null;
    }

}

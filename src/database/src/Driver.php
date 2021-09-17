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
     * @var string
     */
    protected $dsn = '';

    /**
     * @var string
     */
    protected $username = 'root';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * 默认驱动连接选项
     * @var array
     */
    protected $defaultOptions = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        \PDO::ATTR_TIMEOUT => 10,
    ];

    /**
     * Driver constructor.
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $options
     * @throws \PDOException
     */
    public function __construct(string $dsn, string $username, string $password, array $options = [])
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $options;
        $this->connect();
    }

    /**
     * Get instance
     * @return \PDO
     */
    public function instance(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Get options
     * @return array
     */
    public function options(): array
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

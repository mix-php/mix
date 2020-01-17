<?php

namespace Mix\Server;

/**
 * Class ConnectionManager
 * @package Mix\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
class ConnectionManager
{

    /**
     * @var Connection[]
     */
    protected $connections = [];

    /**
     * 新增连接
     * @param Connection $connection
     */
    public function add(Connection $connection)
    {
        $id                     = spl_object_hash($connection);
        $this->connections[$id] = $connection;
    }

    /**
     * 移除连接
     * 这里不可关闭连接，因为这个方法是在关闭连接中调用的
     * @param Connection $connection
     */
    public function remove(Connection $connection)
    {
        $id = spl_object_hash($connection);
        if (!isset($this->connections[$id])) {
            return;
        }
        unset($this->connections[$id]);
    }

    /**
     * 计数
     * @return int
     */
    public function count()
    {
        return count($this->connections);
    }

    /**
     * 关闭全部连接
     * @throws \Swoole\Exception
     */
    public function closeAll()
    {
        foreach ($this->connections as $connection) {
            $connection->close();
            $this->remove($connection);
        }
    }

    /**
     * 获取全部连接
     * @return Connection[]
     */
    public function getConnections()
    {
        return array_values($this->connections);
    }

}

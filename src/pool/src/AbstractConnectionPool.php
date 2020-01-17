<?php

namespace Mix\Pool;

use Mix\Bean\BeanInjector;
use Mix\Pool\Event\ConnectionDiscardedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine\Channel;

/**
 * Class AbstractConnectionPool
 * @package Mix\Pool
 * @author liu,jian <coder.keda@gmail.com>
 */
abstract class AbstractConnectionPool
{

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
     * 拨号器
     * @var DialerInterface
     */
    public $dialer;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $eventDispatcher;

    /**
     * 连接队列
     * @var Channel
     */
    protected $_queue;

    /**
     * 活跃连接集合
     * @var array
     */
    protected $_actives = [];

    /**
     * AbstractConnectionPool constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
        // 创建连接队列
        $this->_queue = new Channel($this->maxIdle);
    }

    /**
     * 创建连接
     * @return object
     */
    protected function createConnection()
    {
        // 连接创建时会挂起当前协程，导致 actives 未增加，因此需先 actives++ 连接创建成功后 actives--
        $closure             = function () {
            $connection       = $this->dialer->dial();
            $connection->pool = $this;
            return $connection;
        };
        $id                  = spl_object_hash($closure);
        $this->_actives[$id] = '';
        try {
            $connection = call_user_func($closure);
        } finally {
            unset($this->_actives[$id]);
        }
        return $connection;
    }

    /**
     * 获取连接
     * @return object
     */
    public function getConnection()
    {
        if ($this->getIdleNumber() > 0 || $this->getTotalNumber() >= $this->maxActive) {
            // 队列有连接，从队列取
            // 达到最大连接数，从队列取
            $connection = $this->pop();
        } else {
            // 创建连接
            $connection = $this->createConnection();
        }
        // 登记, 队列中出来的也需要登记，因为有可能是 discard 中创建的新连接
        $id                  = spl_object_hash($connection);
        $this->_actives[$id] = ''; // 不可保存外部连接的引用，否则导致外部连接不析构
        // 返回
        return $connection;
    }

    /**
     * 释放连接
     * @param $connection
     * @return bool
     */
    public function release($connection)
    {
        $id = spl_object_hash($connection);
        // 判断是否已释放
        if (!isset($this->_actives[$id])) {
            return false;
        }
        // 移除登记
        unset($this->_actives[$id]); // 注意：必须是先减 actives，否则会 maxActive - maxIdle <= 1 时会阻塞
        // 入列
        return $this->push($connection);
    }

    /**
     * 丢弃连接
     * @param $connection
     * @return bool
     */
    public function discard($connection)
    {
        $id = spl_object_hash($connection);
        // 判断是否已丢弃
        if (!isset($this->_actives[$id])) {
            return false;
        }
        // 移除登记
        unset($this->_actives[$id]); // 注意：必须是先减 actives，否则会 maxActive - maxIdle <= 1 时会阻塞
        // 入列一个新连接替代丢弃的连接
        $result = $this->push($this->createConnection());
        // 触发事件
        $this->eventDispatcher and $this->eventDispatcher->dispatch(new ConnectionDiscardedEvent($connection));
        // 返回
        return $result;
    }

    /**
     * 获取连接池的统计信息
     * @return array
     */
    public function getStats()
    {
        return [
            'total'  => $this->getTotalNumber(),
            'idle'   => $this->getIdleNumber(),
            'active' => $this->getActiveNumber(),
        ];
    }

    /**
     * 放入连接
     * @param $connection
     * @return bool
     */
    protected function push($connection)
    {
        if ($this->getIdleNumber() < $this->maxIdle) {
            return $this->_queue->push($connection);
        }
        return false;
    }

    /**
     * 弹出连接
     * @return mixed
     */
    protected function pop()
    {
        return $this->_queue->pop();
    }

    /**
     * 获取队列中的连接数
     * @return int
     */
    protected function getIdleNumber()
    {
        $count = $this->_queue->stats()['queue_num'];
        return $count < 0 ? 0 : $count;
    }

    /**
     * 获取活跃的连接数
     * @return int
     */
    protected function getActiveNumber()
    {
        return count($this->_actives);
    }

    /**
     * 获取当前总连接数
     * @return int
     */
    protected function getTotalNumber()
    {
        return $this->getIdleNumber() + $this->getActiveNumber();
    }

}

<?php

namespace Mix\Redis;

/**
 * Class Connection
 * @package Mix\Redis
 * @author liu,jian <coder.keda@gmail.com>
 */
class Connection extends AbstractConnection
{

    use ReferenceTrait;

    /**
     * @var string
     */
    protected $lastCommand = '';

    /**
     * 执行命令
     * @param $name
     * @param array $arguments
     * @return mixed
     * @throws \RedisException
     * @throws \Throwable
     */
    public function __call($name, $arguments = [])
    {
        $this->lastCommand = $name;
        try {
            // 执行父类命令
            return parent::__call($name, $arguments);
        } catch (\Throwable $ex) {
            if (static::isDisconnectException($ex) && !in_array(strtolower($this->lastCommand), ['multi', 'exec'])) {
                // 断开连接异常处理
                $this->reconnect();
                // 重新执行命令
                return $this->__call($name, $arguments);
            } else {
                // 丢弃连接
                $this->driver->__discard();
                // 抛出其他异常
                throw $ex;
            }
        }
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        if (in_array(strtolower($this->lastCommand), ['multi', 'exec'])) {
            $this->driver->__discard();
            return;
        }
        $this->driver->__return();
    }

}

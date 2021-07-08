<?php

namespace Mix\Redis;

/**
 * Class Connection
 * @package Mix\Redis
 */
class Connection extends AbstractConnection
{

    /**
     * @return bool
     */
    protected function inTransaction(): bool
    {
        if ($this instanceof Multi) {
            return true;
        }
        return false;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \RedisException
     */
    public function __call($name, $arguments = [])
    {
        try {
            // 执行父类命令
            return parent::__call($name, $arguments);
        } catch (\Throwable $ex) {
            if (static::isDisconnectException($ex) && !$this->inTransaction()) {
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

    public function __destruct()
    {
        if (!$this->driver || $this->driver instanceof EmptyDriver) {
            return;
        }

        if ($this->inTransaction()) {
            $this->driver->__discard();
            $this->driver = new EmptyDriver();
            return;
        }
        $this->driver->__return();
        $this->driver = new EmptyDriver();
    }

}

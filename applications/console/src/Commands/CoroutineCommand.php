<?php

namespace Console\Commands;

use Mix\Concurrent\Coroutine\Channel;
use Mix\Database\Pool\ConnectionPool;

/**
 * Class CoroutineCommand
 * @package Console\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class CoroutineCommand
{

    /**
     * 主函数
     */
    public function main()
    {
        xgo(function () {
            $time = time();
            $chan = new Channel();
            for ($i = 0; $i < 2; $i++) {
                xgo([$this, 'foo'], $chan);
            }
            for ($i = 0; $i < 2; $i++) {
                $result = $chan->pop();
            }
            println('Total time: ' . (time() - $time));
        });
    }

    /**
     * 查询数据
     * @param Channel $chan
     */
    public function foo(Channel $chan)
    {
        /** @var ConnectionPool $dbPool */
        $dbPool = context()->get('dbPool');
        $db     = $dbPool->getConnection();
        $result = $db->createCommand('select sleep(5)')->queryAll();
        $db->release(); // 不手动释放的连接不会归还连接池，会在析构时丢弃
        $chan->push($result);
    }

}
